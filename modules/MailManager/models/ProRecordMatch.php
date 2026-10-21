<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once 'data/CRMEntity.php';
/**
 * Finding CRM records for a conversation — participant emails, link
 * suggestions and the certain-match lookups (exact email, company domain,
 * domain of an organization's contacts, company name)
 */
class MailManager_ProRecordMatch_Model
{
    /**
     * Modules searched for email matches
     */
    public const SUGGESTION_MODULES = ['Contacts', 'Leads', 'Accounts'];

    /* A shared mail domain proves nothing on these providers */
    public const FREEMAIL_DOMAINS = [
        'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.de', 'hotmail.com', 'outlook.com',
        'live.com', 'icloud.com', 'me.com', 'aol.com', 'protonmail.com', 'proton.me',
        'gmx.de', 'gmx.net', 'gmx.at', 'web.de', 't-online.de', 'mail.com',
        'seznam.cz', 'email.cz', 'centrum.cz', 'volny.cz', 'atlas.cz',
        'centrum.sk', 'azet.sk', 'zoznam.sk', 'post.sk', 'atlas.sk', 'pobox.sk',
        'yandex.com', 'yandex.ru', 'mail.ru',
    ];

    /** @var array|null last senderHasRecordIn() match — ['module', 'crmid'] */
    private static $lastSenderMatch;

    /** @var array|null lowercased address => true, see isInternalEmail() */
    private static $internalEmails;

    /**
     * An address of our own side — a CRM user or a connected mailbox. A
     * colleague in the conversation (a forward, a copy) is not who the mail
     * is about: no record is suggested, linked or created for such an address
     *
     * @param string $email
     * @return bool
     */
    public static function isInternalEmail($email)
    {
        if (self::$internalEmails === null) {
            self::$internalEmails = [];
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                'SELECT email1 AS email FROM vtiger_users WHERE deleted = 0
                 UNION SELECT email2 FROM vtiger_users WHERE deleted = 0
                 UNION SELECT secondaryemail FROM vtiger_users WHERE deleted = 0
                 UNION SELECT email FROM df_mailmanager_mailbox',
                []
            );
            $count = $adb->num_rows($result);
            for ($i = 0; $i < $count; $i++) {
                $address = strtolower(trim((string)$adb->query_result($result, $i, 'email')));
                if ($address !== '') {
                    self::$internalEmails[$address] = true;
                }
            }
        }

        return isset(self::$internalEmails[strtolower(trim((string)$email))]);
    }

    /**
     * Who really wrote a received message. A web form (or another of our own
     * systems) mails from OUR address and names the person in Reply-To —
     * that person is the sender then. Mail from outside keeps its From: a
     * Reply-To there is only where the answer goes (a list, a no-reply box)
     *
     * @param array $message message row (from_email, reply_to)
     * @return string lowercased address
     */
    public static function getEffectiveSender(array $message)
    {
        $from = strtolower(trim((string)($message['from_email'] ?? '')));
        $replyTo = strtolower(trim((string)($message['reply_to'] ?? '')));

        return $replyTo !== '' && $replyTo !== $from && ($from === '' || self::isInternalEmail($from))
            ? $replyTo
            : $from;
    }

    /**
     * Who the conversation is with — the sender of the newest received
     * message that is not one of us
     *
     * @param int $threadId
     * @return string lowercased address, '' when nobody outside wrote yet
     */
    public static function getLatestSender($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email, reply_to FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 20",
            [(int)$threadId]
        );
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $email = self::getEffectiveSender($adb->raw_query_result_rowdata($result, $i));
            if ($email !== '' && !self::isInternalEmail($email)) {
                return $email;
            }
        }

        return '';
    }

    /**
     * Whose records a thread suggests — our own side (the mailbox, CRM users,
     * other connected mailboxes) excluded:
     * - a conversation somebody outside wrote into: the SENDERS of the
     *   received mail only. Who else the mail went to says nothing about
     *   what it is about — a co-recipient with many records in the CRM
     *   flooded the suggestions while the customer was not among them;
     * - a conversation only we wrote into (nobody answered yet): the
     *   recipients of what we sent
     *
     * @param int $threadId
     * @param string $ownEmail
     * @return string[] lowercased, max 10
     */
    public static function getThreadEmails($threadId, $ownEmail)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT direction, from_email, reply_to, to_addrs, cc_addrs
             FROM df_mailmanager_message WHERE threadid = ?',
            [$threadId]
        );

        $external = function (array $emails) use ($ownEmail) {
            return array_values(array_unique(array_filter($emails, function ($email) use ($ownEmail) {
                return $email !== '' && strcasecmp($email, $ownEmail) !== 0 && !self::isInternalEmail($email);
            })));
        };

        $senders = [];
        $recipients = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            if ($row['direction'] === 'in') {
                $senders[] = self::getEffectiveSender($row);
                continue;
            }
            foreach (['to_addrs', 'cc_addrs'] as $column) {
                foreach (json_decode((string)$row[$column], true) ?: [] as $address) {
                    $recipients[] = strtolower((string)($address['email'] ?? ''));
                }
            }
        }

        $senders = $external($senders);

        return array_slice($senders ?: $external($recipients), 0, 10);
    }

    /**
     * Record suggestions of the CRM panel: matches by address, by name, the ticket named
     * in the subject and the project of a won opportunity, without dismissed ones.
     *
     * @param array $thread  thread row
     * @param array $mailbox mailbox row
     * @param array $links   linked records
     *
     * @return array
     */
    public static function getThreadSuggestions(array $thread, array $mailbox, array $links): array
    {
        $threadId = (int)$thread['threadid'];
        $emails = self::getThreadEmails($threadId, $mailbox['email']);
        $suggestions = self::getSuggestions($emails, array_column($links, 'crmid'));
        $extraSuggestions = [
            self::getNameSuggestion($thread, $links),
            // the person of the sender's name, writing from an address the CRM does not know
            self::getSenderNameSuggestion($thread, $links),
        ];

        foreach ($extraSuggestions as $extraSuggestion) {
            if ($extraSuggestion && !in_array($extraSuggestion['crmid'], array_column($suggestions, 'crmid'), true)) {
                $suggestions[] = $extraSuggestion;
            }
        }

        // the ticket named in the subject – linking it brings its organization and person along
        $ticketSuggestion = self::getTicketSuggestion($thread, $links);

        if ($ticketSuggestion) {
            $suggestions[] = $ticketSuggestion;
        }

        // the won opportunity's project – the next record of this conversation
        $suggestions = array_merge($suggestions, self::getProjectSuggestions($links));
        // open tasks of the linked project – the piece of work the mail is about
        $suggestions = array_merge($suggestions, self::getTaskSuggestions($links));
        // suggestions the user dismissed for this conversation stay away
        $dismissedIds = MailManager_ProRelate_Model::getDismissedIds($threadId);

        return MailManager_ProRelate_Model::sortPartyFirst(
            array_values(array_filter($suggestions, function ($suggestion) use ($dismissedIds) {
                return !in_array((int)$suggestion['crmid'], $dismissedIds, true);
            }))
        );
    }

    /**
     * CRM records whose email fields match the participants
     *
     * @param string[] $emails
     * @param int[] $excludeCrmIds already linked records
     * @return array of ['crmid', 'label', 'module']
     */
    public static function getSuggestions(array $emails, array $excludeCrmIds)
    {
        if (!$emails) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $suggestions = [];

        foreach (self::SUGGESTION_MODULES as $module) {
            $tabId = getTabid($module);
            $fieldResult = $adb->pquery(
                'SELECT columnname, tablename FROM vtiger_field WHERE tabid = ? AND uitype = 13 AND presence IN (0,2)',
                [$tabId]
            );
            $fieldCount = $adb->num_rows($fieldResult);
            if (!$fieldCount) {
                continue;
            }

            $focus = CRMEntity::getInstance($module);
            $baseTable = $focus->table_name;
            $baseIndex = $focus->table_index;

            $joins = '';
            $joined = [$baseTable => true];
            $conditions = [];
            $params = [];
            for ($i = 0; $i < $fieldCount; $i++) {
                $table = $adb->query_result($fieldResult, $i, 'tablename');
                $column = $adb->query_result($fieldResult, $i, 'columnname');
                if (empty($joined[$table])) {
                    $tableIndex = $focus->tab_name_index[$table] ?? $baseIndex;
                    $joins .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $tableIndex . ' = ' . $baseTable . '.' . $baseIndex;
                    $joined[$table] = true;
                }
                $conditions[] = $table . '.' . $column . ' IN (' . generateQuestionMarks($emails) . ')';
                $params = array_merge($params, $emails);
            }

            $query = 'SELECT ce.crmid, ce.label FROM vtiger_crmentity ce
                      INNER JOIN ' . $baseTable . ' ON ' . $baseTable . '.' . $baseIndex . ' = ce.crmid' . $joins . '
                      WHERE ce.deleted = 0 AND (' . implode(' OR ', $conditions) . ') LIMIT 6';
            $result = $adb->pquery($query, $params);

            $count = $adb->num_rows($result);
            for ($i = 0; $i < $count; $i++) {
                $crmid = (int)$adb->query_result($result, $i, 'crmid');
                if (in_array($crmid, $excludeCrmIds, true)) {
                    continue;
                }
                if (!Users_Privileges_Model::isPermitted($module, 'DetailView', $crmid)) {
                    continue;
                }
                $suggestions[] = [
                    'crmid' => $crmid,
                    'label' => decode_html((string)$adb->query_result($result, $i, 'label')),
                    'module' => $module,
                    'icon' => Vtiger_Module_Model::getInstance($module)->getModuleIcon(),
                    'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$crmid, $module),
                ];
            }
        }

        return $suggestions;
    }

    /**
     * The ticket whose number stands in the subject (helpdesk notifications,
     * replies to them: "TT002733 - …") — a link suggestion while no ticket is
     * linked. Never linked by itself: anyone can type a ticket number into a
     * subject, and the link files the mail on that ticket
     *
     * @param array $thread thread row (subject)
     * @param array $links current links (Relate::getLinks)
     * @return array|null suggestion row like getSuggestions() + 'reason' => 'ticket_no'
     */
    public static function getTicketSuggestion(array $thread, array $links)
    {
        if (in_array('HelpDesk', array_column($links, 'module'), true) || !vtlib_isModuleActive('HelpDesk')) {
            return null;
        }
        $adb = PearDatabase::getInstance();
        $prefixResult = $adb->pquery(
            "SELECT prefix FROM vtiger_modentity_num WHERE semodule = 'HelpDesk' AND active = 1",
            []
        );
        $prefix = $adb->num_rows($prefixResult) ? trim((string)$adb->query_result($prefixResult, 0, 'prefix')) : '';
        // the number as a whole word: "TT002733", not a part of "ATT0027331"
        $pattern = '/(?<![A-Za-z0-9])' . preg_quote($prefix, '/') . '\d+(?!\d)/i';
        if ($prefix === '' || !preg_match($pattern, (string)$thread['subject'], $match)) {
            return null;
        }

        $ticket = self::uniqueMatch(
            'SELECT ce.crmid, ce.label FROM vtiger_troubletickets tt
             INNER JOIN vtiger_crmentity ce ON ce.crmid = tt.ticketid AND ce.deleted = 0
             WHERE tt.ticket_no = ? LIMIT 2',
            [$match[0]]
        );
        if (!$ticket || !Users_Privileges_Model::isPermitted('HelpDesk', 'DetailView', $ticket['crmid'])) {
            return null;
        }

        return [
            'crmid' => (int)$ticket['crmid'],
            'label' => decode_html((string)$ticket['label']),
            'module' => 'HelpDesk',
            'icon' => Vtiger_Module_Model::getInstance('HelpDesk')->getModuleIcon(),
            'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$ticket['crmid'], 'HelpDesk'),
            'reason' => 'ticket_no',
        ];
    }

    /**
     * Projects made from an opportunity linked to the conversation — offered
     * as link suggestions while no project is linked. A project points to its
     * opportunity through a reference field (any field of the Project module
     * that accepts Potentials; installations add their own)
     *
     * @param array $links current links (Relate::getLinks)
     * @return array suggestion rows like getSuggestions() + 'reason' => 'potential'
     */
    public static function getProjectSuggestions(array $links)
    {
        $linkedModules = array_column($links, 'module');
        $potentialIds = [];
        foreach ($links as $link) {
            if ($link['module'] === 'Potentials') {
                $potentialIds[] = (int)$link['crmid'];
            }
        }
        if (!$potentialIds || in_array('Project', $linkedModules, true) || !vtlib_isModuleActive('Project')
            || !MailManager_ProRelate_Model::hasProjects()) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $fieldResult = $adb->pquery(
            "SELECT f.columnname, f.tablename FROM vtiger_field f
             INNER JOIN vtiger_fieldmodulerel r ON r.fieldid = f.fieldid AND r.relmodule = 'Potentials'
             WHERE f.tabid = ? AND f.presence IN (0,2)",
            [getTabid('Project')]
        );
        $focus = CRMEntity::getInstance('Project');
        $joins = '';
        $joined = [$focus->table_name => true];
        $conditions = [];
        $params = [];
        $fieldCount = $adb->num_rows($fieldResult);
        for ($i = 0; $i < $fieldCount; $i++) {
            $table = $adb->query_result($fieldResult, $i, 'tablename');
            // only the module's own tables (base + custom fields)
            if (!isset($focus->tab_name_index[$table])) {
                continue;
            }
            if (empty($joined[$table])) {
                $joins .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $focus->tab_name_index[$table]
                    . ' = ' . $focus->table_name . '.' . $focus->table_index;
                $joined[$table] = true;
            }
            $conditions[] = $table . '.' . $adb->query_result($fieldResult, $i, 'columnname')
                . ' IN (' . generateQuestionMarks($potentialIds) . ')';
            $params = array_merge($params, $potentialIds);
        }
        if (!$conditions) {
            return [];
        }

        $result = $adb->pquery(
            'SELECT ce.crmid, ce.label FROM vtiger_crmentity ce
             INNER JOIN ' . $focus->table_name . ' ON ' . $focus->table_name . '.' . $focus->table_index . ' = ce.crmid'
            . $joins . '
             WHERE ce.deleted = 0 AND (' . implode(' OR ', $conditions) . ') ORDER BY ce.crmid DESC LIMIT 5',
            $params
        );

        $suggestions = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $crmid = (int)$adb->query_result($result, $i, 'crmid');
            if (!Users_Privileges_Model::isPermitted('Project', 'DetailView', $crmid)) {
                continue;
            }
            $suggestions[] = [
                'crmid' => $crmid,
                'label' => decode_html((string)$adb->query_result($result, $i, 'label')),
                'module' => 'Project',
                'icon' => Vtiger_Module_Model::getInstance('Project')->getModuleIcon(),
                'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$crmid, 'Project'),
                'reason' => 'potential',
            ];
        }

        return $suggestions;
    }

    /**
     * Open tasks of the project linked to the conversation — offered as link
     * suggestions while no task is linked, so the mail can go to the piece of
     * work it is about (finished and cancelled tasks are left out)
     *
     * @param array $links current links (Relate::getLinks)
     * @return array suggestion rows like getSuggestions() + 'reason' => 'project'
     */
    public static function getTaskSuggestions(array $links)
    {
        $linkedModules = array_column($links, 'module');
        $projectIds = [];

        foreach ($links as $link) {
            if ($link['module'] === 'Project') {
                $projectIds[] = (int)$link['crmid'];
            }
        }

        if (!$projectIds || in_array('ProjectTask', $linkedModules, true) || !vtlib_isModuleActive('ProjectTask')
            || !MailManager_ProRelate_Model::hasProjects()
            || !Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView')) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $closed = ['Completed', 'Canceled'];
        $result = $adb->pquery(
            'SELECT ce.crmid, ce.label FROM vtiger_projecttask t
             INNER JOIN vtiger_crmentity ce ON ce.crmid = t.projecttaskid AND ce.deleted = 0
             WHERE t.projectid IN (' . generateQuestionMarks($projectIds) . ')
               AND COALESCE(t.projecttaskstatus, \'\') NOT IN (' . generateQuestionMarks($closed) . ')
             ORDER BY ce.modifiedtime DESC LIMIT 5',
            array_merge($projectIds, $closed)
        );

        $suggestions = [];
        $icon = Vtiger_Module_Model::getInstance('ProjectTask')->getModuleIcon();

        while ($row = $adb->fetchByAssoc($result)) {
            $crmid = (int)$row['crmid'];

            if (!Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView', $crmid)) {
                continue;
            }

            $suggestions[] = [
                'crmid' => $crmid,
                'label' => decode_html((string)$row['label']),
                'module' => 'ProjectTask',
                'icon' => $icon,
                'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$crmid, 'ProjectTask'),
                'reason' => 'project',
            ];
        }

        return $suggestions;
    }

    /**
     * Organization named in the sender's signature (AI-extracted company) as
     * a link suggestion — never linked automatically, the sender writes the
     * signature himself. Offered only while no organization is linked
     *
     * @param array $thread thread row (triage_payload)
     * @param array $links current links (Relate::getLinks)
     * @return array|null suggestion row like getSuggestions() + 'reason' => 'name'
     */
    public static function getNameSuggestion(array $thread, array $links)
    {
        if (in_array('Accounts', array_column($links, 'module'), true)) {
            return null;
        }
        $payload = json_decode((string)($thread['triage_payload'] ?? ''), true) ?: [];
        $company = (string)($payload['entities']['company'] ?? '');
        // older payloads may still carry our own company read from a quote
        $match = self::isOwnCompany($company) ? null : self::findAccountByName($company);
        if (!$match
            || MailManager_ProRelate_Model::isUnlinked((int)$thread['threadid'], $match['crmid'])
            || !Users_Privileges_Model::isPermitted('Accounts', 'DetailView', $match['crmid'])) {
            return null;
        }

        return [
            'crmid' => (int)$match['crmid'],
            'label' => decode_html((string)$match['label']),
            'module' => 'Accounts',
            'icon' => Vtiger_Module_Model::getInstance('Accounts')->getModuleIcon(),
            'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$match['crmid'], 'Accounts'),
            'reason' => 'name',
        ];
    }

    /**
     * The contact of the same NAME as the sender, as a link suggestion — for
     * a person who writes from another address than the CRM knows (a second
     * company, a private mailbox). Never linked automatically: a display name
     * is whatever the sender typed. Offered only while no contact is linked,
     * the address itself matches no contact and exactly one contact carries
     * the name
     *
     * @param array $thread thread row
     * @param array $links current links (Relate::getLinks)
     * @return array|null suggestion row like getSuggestions() + 'reason' => 'sender_name'
     */
    public static function getSenderNameSuggestion(array $thread, array $links)
    {
        if (in_array('Contacts', array_column($links, 'module'), true)) {
            return null;
        }
        $adb = PearDatabase::getInstance();
        $email = self::getLatestSender((int)$thread['threadid']);
        if ($email === '' || self::findSenderRecord($email)) {
            return null;
        }
        $result = $adb->pquery(
            "SELECT from_name FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' AND from_email = ? ORDER BY sent_at DESC LIMIT 1",
            [(int)$thread['threadid'], $email]
        );
        $name = $adb->num_rows($result) ? self::normalizePersonName($adb->query_result($result, 0, 'from_name')) : '';
        $words = $name === '' ? [] : explode(' ', $name);
        // one word (a first name, a nickname) or an address is no name to match by
        if (count($words) < 2 || strpos($name, '@') !== false) {
            return null;
        }

        // the last name is one of the name's endings ("Jozef Novák" -> "Novák",
        // a contact made from a display name may hold more of it there)
        $endings = [];
        for ($i = 1; $i < count($words); $i++) {
            $endings[] = implode(' ', array_slice($words, $i));
        }
        $candidates = $adb->pquery(
            'SELECT ce.crmid, ce.label, c.firstname, c.lastname FROM vtiger_contactdetails c
             INNER JOIN vtiger_crmentity ce ON ce.crmid = c.contactid AND ce.deleted = 0
             WHERE LOWER(c.lastname) IN (' . generateQuestionMarks($endings) . ') LIMIT 50',
            $endings
        );
        $matches = [];
        $count = $adb->num_rows($candidates);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($candidates, $i);
            if (self::normalizePersonName($row['firstname'] . ' ' . $row['lastname']) === $name) {
                $matches[] = $row;
            }
        }
        if (count($matches) !== 1) {
            return null;
        }

        $crmid = (int)$matches[0]['crmid'];
        if (MailManager_ProRelate_Model::isUnlinked((int)$thread['threadid'], $crmid)
            || !Users_Privileges_Model::isPermitted('Contacts', 'DetailView', $crmid)) {
            return null;
        }

        return [
            'crmid' => $crmid,
            'label' => decode_html((string)$matches[0]['label']),
            'module' => 'Contacts',
            'icon' => Vtiger_Module_Model::getInstance('Contacts')->getModuleIcon(),
            'summary' => MailManager_ProRelate_Model::getSummaryLines((int)$crmid, 'Contacts'),
            'reason' => 'sender_name',
        ];
    }

    /**
     * @param string $name a person's name as stored or as a mail shows it
     * @return string lowercased, html-decoded, single spaces — for comparing
     */
    private static function normalizePersonName($name)
    {
        $name = decode_html((string)$name);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', str_replace(['"', "'"], '', $name))), 'UTF-8');
    }

    /**
     * Record of the module carrying exactly this email — null when none or
     * when the email is ambiguous (more than one record)
     *
     * @param string $module Contacts|Accounts
     * @param string $email
     * @return array|null ['crmid', 'label']
     */
    public static function findUniqueByEmail($module, $email)
    {
        $adb = PearDatabase::getInstance();
        $fieldResult = $adb->pquery(
            'SELECT columnname, tablename FROM vtiger_field WHERE tabid = ? AND uitype = 13 AND presence IN (0,2)',
            [getTabid($module)]
        );
        $fieldCount = $adb->num_rows($fieldResult);
        if (!$fieldCount) {
            return null;
        }

        $focus = CRMEntity::getInstance($module);
        $joins = '';
        $joined = [$focus->table_name => true];
        $conditions = [];
        $params = [];
        for ($i = 0; $i < $fieldCount; $i++) {
            $table = $adb->query_result($fieldResult, $i, 'tablename');
            $column = $adb->query_result($fieldResult, $i, 'columnname');
            if (empty($joined[$table])) {
                $tableIndex = $focus->tab_name_index[$table] ?? $focus->table_index;
                $joins .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $tableIndex . ' = ' . $focus->table_name . '.' . $focus->table_index;
                $joined[$table] = true;
            }
            $conditions[] = $table . '.' . $column . ' = ?';
            $params[] = $email;
        }

        return self::uniqueMatch(
            'SELECT DISTINCT ce.crmid, ce.label FROM vtiger_crmentity ce
             INNER JOIN ' . $focus->table_name . ' ON ' . $focus->table_name . '.' . $focus->table_index . ' = ce.crmid' . $joins . '
             WHERE ce.deleted = 0 AND (' . implode(' OR ', $conditions) . ') LIMIT 2',
            $params
        );
    }

    /**
     * Organization whose mail or web domain equals the sender's domain
     *
     * @param string $email sender address
     * @return array|null ['crmid', 'label']
     */
    public static function findAccountByDomain($email)
    {
        $domain = self::businessDomain($email);
        if ($domain === '') {
            return null;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT ce.crmid, ce.label, a.email1, a.email2, a.website FROM vtiger_crmentity ce
             INNER JOIN vtiger_account a ON a.accountid = ce.crmid
             WHERE ce.deleted = 0 AND (a.email1 LIKE ? OR a.email2 LIKE ? OR a.website LIKE ?)",
            ['%@' . $domain, '%@' . $domain, '%' . $domain . '%']
        );

        $matches = [];
        $suffix = '@' . $domain;
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count && count($matches) < 2; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $mailMatch = substr(strtolower((string)$row['email1']), -strlen($suffix)) === $suffix
                || substr(strtolower((string)$row['email2']), -strlen($suffix)) === $suffix;
            // The LIKE on website over-matches (i.cz ~ wiki.cz) — verify the
            // real host: equal to the domain or a subdomain of it
            $website = strtolower(trim((string)$row['website']));
            $host = $website !== ''
                ? (string)(parse_url((strpos($website, '//') === false ? 'http://' : '') . $website, PHP_URL_HOST) ?: '')
                : '';
            $host = preg_replace('/^www\./', '', $host);
            if ($mailMatch
                || $host === $domain
                || ($host !== '' && substr($host, -strlen('.' . $domain)) === '.' . $domain)) {
                $matches[$row['crmid']] = ['crmid' => (int)$row['crmid'], 'label' => (string)$row['label']];
            }
        }

        return count($matches) === 1 ? reset($matches) : null;
    }

    /**
     * Organization whose CONTACTS use the sender's mail domain — the company
     * is recognized through its people (unique account required)
     *
     * @param string $email sender address
     * @return array|null ['crmid', 'label']
     */
    public static function findAccountByContactsDomain($email)
    {
        $domain = self::businessDomain($email);
        if ($domain === '') {
            return null;
        }
        $adb = PearDatabase::getInstance();

        return self::uniqueMatch(
            "SELECT DISTINCT ace.crmid, ace.label FROM vtiger_contactdetails c
             INNER JOIN vtiger_crmentity ce ON ce.crmid = c.contactid AND ce.deleted = 0
             INNER JOIN vtiger_crmentity ace ON ace.crmid = c.account_id AND ace.deleted = 0
             WHERE c.account_id > 0 AND (c.email LIKE ? OR c.secondaryemail LIKE ?) LIMIT 2",
            ['%@' . $domain, '%@' . $domain]
        );
    }

    /**
     * Organization matching the AI-extracted company name (normalized:
     * diacritics, case and legal forms like a.s. / s.r.o. / GmbH ignored)
     *
     * @param string $company extracted company name
     * @return array|null ['crmid', 'label']
     */
    public static function findAccountByName($company)
    {
        $normalized = self::normalizeCompanyName($company);
        if ($normalized === '' || mb_strlen($normalized) < 3) {
            return null;
        }
        $adb = PearDatabase::getInstance();
        // Prefilter by the first significant word (ci collation matches
        // case/diacritics) — the exact check runs on the normalized names
        $firstWord = explode(' ', $normalized)[0];
        $result = $adb->pquery(
            'SELECT ce.crmid, ce.label FROM vtiger_crmentity ce
             INNER JOIN vtiger_account a ON a.accountid = ce.crmid
             WHERE ce.deleted = 0 AND ce.label LIKE ?',
            ['%' . $firstWord . '%']
        );

        $matches = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count && count($matches) < 2; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            if (self::normalizeCompanyName((string)$row['label']) === $normalized) {
                $matches[$row['crmid']] = ['crmid' => (int)$row['crmid'], 'label' => (string)$row['label']];
            }
        }

        return count($matches) === 1 ? reset($matches) : null;
    }

    /**
     * Is the name our own company (vtiger Company Details)? A reply quotes
     * our signature — a name taken from there is never the sender's company
     *
     * @param string $company
     * @return bool
     */
    public static function isOwnCompany($company)
    {
        $normalized = self::normalizeCompanyName($company);
        $own = self::normalizeCompanyName(
            decode_html((string)Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname'))
        );

        return $normalized !== '' && $normalized === $own;
    }

    /**
     * @param string $query LIMIT 2 query returning crmid + label
     * @param array $params
     * @return array|null the single match, null when none or ambiguous
     */
    private static function uniqueMatch($query, array $params)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($query, $params);
        if ($adb->num_rows($result) !== 1) {
            return null;
        }
        $row = $adb->raw_query_result_rowdata($result, 0);

        // labels are stored html-encoded ("A &amp; B") — decoded for display and name matching
        return ['crmid' => (int)$row['crmid'], 'label' => decode_html((string)$row['label'])];
    }

    /**
     * The organization the sender's contact belongs to — the most certain
     * organization signal, and the only one for freemail senders
     *
     * @param string $email sender address
     * @return array|null ['crmid', 'label'] of the organization
     */
    public static function findAccountOfContact($email)
    {
        $contact = self::findUniqueByEmail('Contacts', $email);
        if (!$contact) {
            return null;
        }

        return self::uniqueMatch(
            'SELECT ce.crmid, ce.label FROM vtiger_contactdetails cd
             INNER JOIN vtiger_crmentity ce ON ce.crmid = cd.account_id AND ce.deleted = 0
             WHERE cd.contactid = ?',
            [$contact['crmid']]
        );
    }

    /**
     * Sender's mail domain when it identifies a company — '' for freemail
     *
     * @param string $email
     * @return string
     */
    private static function businessDomain($email)
    {
        $domain = strtolower((string)substr((string)strstr($email, '@'), 1));

        return ($domain === '' || in_array($domain, self::FREEMAIL_DOMAINS, true)) ? '' : $domain;
    }

    /**
     * Company name comparison key — lowercase ASCII without legal forms
     *
     * @param string $name
     * @return string
     */
    private static function normalizeCompanyName($name)
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($name));
        $name = strtolower($ascii !== false ? $ascii : trim($name));
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name);
        $name = preg_replace(
            '/\b(a s|s r o|spol s r o|k s|v o s|sro|gmbh|ag|se|ltd|llc|inc|corp|plc|bv|nv|oy|ab|sa)\b/',
            ' ',
            ' ' . $name . ' '
        );

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    /**
     * Existence check of a CRM record with the sender's email — deliberately
     * without a permission filter (runs from cron, the result only steers the
     * suggestion, no record data is exposed)
     *
     * @param string $email
     * @return array|null ['module', 'crmid']
     */
    public static function findSenderRecord($email)
    {
        foreach (MailManager_ProRecordMatch_Model::SUGGESTION_MODULES as $module) {
            if (self::senderHasRecordIn($email, $module)) {
                return self::$lastSenderMatch;
            }
        }

        return null;
    }

    /**
     * Does a record of the given module carry the sender's email?
     *
     * @param string $email
     * @param string $module
     * @return bool
     */
    public static function senderHasRecordIn($email, $module)
    {
        if ($email === '') {
            return false;
        }
        $adb = PearDatabase::getInstance();

        $fieldResult = $adb->pquery(
            'SELECT columnname, tablename FROM vtiger_field WHERE tabid = ? AND uitype = 13 AND presence IN (0,2)',
            [getTabid($module)]
        );
        $fieldCount = $adb->num_rows($fieldResult);
        if (!$fieldCount) {
            return false;
        }

        $focus = CRMEntity::getInstance($module);
        $baseTable = $focus->table_name;
        $baseIndex = $focus->table_index;

        $joins = '';
        $joined = [$baseTable => true];
        $conditions = [];
        $params = [];
        for ($i = 0; $i < $fieldCount; $i++) {
            $table = $adb->query_result($fieldResult, $i, 'tablename');
            $column = $adb->query_result($fieldResult, $i, 'columnname');
            if (empty($joined[$table])) {
                $tableIndex = $focus->tab_name_index[$table] ?? $baseIndex;
                $joins .= ' INNER JOIN ' . $table . ' ON ' . $table . '.' . $tableIndex . ' = ' . $baseTable . '.' . $baseIndex;
                $joined[$table] = true;
            }
            $conditions[] = $table . '.' . $column . ' = ?';
            $params[] = $email;
        }

        $result = $adb->pquery(
            'SELECT ce.crmid FROM vtiger_crmentity ce
             INNER JOIN ' . $baseTable . ' ON ' . $baseTable . '.' . $baseIndex . ' = ce.crmid' . $joins . '
             WHERE ce.deleted = 0 AND (' . implode(' OR ', $conditions) . ') LIMIT 1',
            $params
        );
        if ($adb->num_rows($result)) {
            self::$lastSenderMatch = ['module' => $module, 'crmid' => (int)$adb->query_result($result, 0, 'crmid')];

            return true;
        }

        return false;
    }
}
