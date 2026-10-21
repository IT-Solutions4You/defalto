<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/**
 * Values offered when a CRM record (or a comment) is created from a mail —
 * the Create New options, their quick-create prefill and field labels
 */
class MailManager_ProCreatePrefill_Model
{
    /**
     * QuickCreate prefill parameters per module, derived from the last inbound
     * message of the thread (approved create-from-mail flow — the user always
     * reviews the values in the QuickCreate modal before saving)
     *
     * @param array $thread thread row
     * @param string $ownEmail mailbox address
     * @param array $links currently linked records (getLinks output)
     * @return array module => ['label' => translated single label, 'params' => field values]
     */
    public static function getCreatePrefills(array $thread, $ownEmail, array $links = [])
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email, from_name, reply_to, subject, body_plain FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$thread['threadid']]
        );
        $message = $adb->num_rows($result)
            ? $adb->raw_query_result_rowdata($result, 0)
            : ['from_email' => '', 'from_name' => '', 'reply_to' => '', 'subject' => '', 'body_plain' => ''];

        // a web form mails from our own address — the person is in Reply-To
        $email = MailManager_ProRecordMatch_Model::getEffectiveSender($message);
        // a conversation we started (or mail from our own address) has no person to create a
        // record of; a linked Contact / Organization still allows the case records below
        $hasPerson = $email !== '' && strcasecmp($email, (string)$ownEmail) !== 0;
        if (!$hasPerson && !$links) {
            return [];
        }
        if ($hasPerson && $email !== strtolower(trim((string)$message['from_email']))) {
            // the display name is that of our own system, not the person's
            $message['from_name'] = '';
            $message['from_email'] = $email;
        }

        $nameParts = preg_split('/\s+/', trim((string)$message['from_name'])) ?: [];
        $lastName = $nameParts ? array_pop($nameParts) : '';
        $firstName = implode(' ', $nameParts);
        if ($lastName === '') {
            $lastName = (string)strstr($email, '@', true);
        }
        // Company guess = registrable domain label: newsletter@info.mountfield.sk
        // → "Mountfield" (subdomains and the TLD are dropped, co.uk style handled)
        $domain = (string)substr((string)strstr($email, '@'), 1);
        $labels = array_values(array_filter(explode('.', $domain)));
        $labelCount = count($labels);
        if ($labelCount >= 3 && in_array($labels[$labelCount - 2], ['co', 'com', 'org', 'net', 'gov', 'edu', 'ac'], true)) {
            $domainLabel = $labels[$labelCount - 3];
        } elseif ($labelCount >= 2) {
            $domainLabel = $labels[$labelCount - 2];
        } else {
            $domainLabel = $domain;
        }
        $domainLabel = ucfirst($domainLabel);
        // A freemail domain names the provider, not the company ("Gmail") —
        // no guess then; the AI-extracted signature company still applies below
        if (in_array(strtolower($domain), MailManager_ProRecordMatch_Model::FREEMAIL_DOMAINS, true)) {
            $companyGuess = '';
        } else {
            $companyGuess = $domainLabel ?: trim((string)$message['from_name']);
        }

        // AI-extracted sender/company details (signature) beat the guesses
        // derived from the address — the getter returns null for blanks so
        // array_filter drops them from the prefill
        $payload = json_decode((string)($thread['triage_payload'] ?? ''), true) ?: [];
        $entities = is_array($payload['entities'] ?? null) ? $payload['entities'] : [];
        $entity = function ($key) use ($entities) {
            $value = trim((string)($entities[$key] ?? ''));

            return $value !== '' ? $value : null;
        };
        if ($entity('company') !== null) {
            $companyGuess = $entity('company');
        }
        $firstName = $entity('firstname') ?? $firstName;
        $lastName = $entity('lastname') ?? $lastName;

        $linkedContactId = 0;
        $linkedAccountId = 0;
        $linkedPotentialId = 0;
        $linkedModules = [];
        foreach ($links as $link) {
            $linkedModules[$link['module']] = true;
            if (!$linkedPotentialId && $link['module'] === 'Potentials') {
                $linkedPotentialId = (int)$link['crmid'];
            }
            if (!$linkedContactId && $link['module'] === 'Contacts') {
                $linkedContactId = (int)$link['crmid'];
            }
            if (!$linkedAccountId && $link['module'] === 'Accounts') {
                $linkedAccountId = (int)$link['crmid'];
            }
        }

        // the menu follows this order — who the mail is with first
        // (Organization, Contact), then the Lead
        $definitions = [
            'Accounts' => [
                'accountname' => $companyGuess ?: $lastName,
                'email1' => $email,
                'phone' => $entity('phone'),
                'website' => $entity('website'),
                'bill_street' => $entity('street'),
                'bill_city' => $entity('city'),
                'bill_code' => $entity('zip'),
                'bill_country' => $entity('country'),
            ],
            // A linked Organization prefills the contact's account reference
            'Contacts' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
                'phone' => $entity('phone'),
                'title' => $entity('position'),
                'account_id' => $linkedAccountId ?: null,
            ],
            'Leads' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'company' => $companyGuess,
                'email' => $email,
                'phone' => $entity('phone'),
                'designation' => $entity('position'),
                'website' => $entity('website'),
            ],
        ];

        // A ticket makes sense only for a recognized customer — offered once
        // a Contact or Account is linked, with the relation prefilled
        // Case description = the FIRST inbound message (the original request);
        // the newest one is often just a follow-up/notification. Older cached
        // messages may still carry HTML/CSS remnants in body_plain
        $firstResult = $adb->pquery(
            "SELECT body_plain, body_html FROM df_mailmanager_message
             WHERE threadid = ? AND is_draft = 0 ORDER BY direction = 'in' DESC, sent_at ASC LIMIT 1",
            [$thread['threadid']]
        );
        $firstMessage = $adb->num_rows($firstResult)
            ? $adb->raw_query_result_rowdata($firstResult, 0)
            : ['body_plain' => (string)$message['body_plain'], 'body_html' => ''];
        $descriptionText = mb_substr(
            MailManager_ProMailText_Model::messageText($firstMessage['body_plain'], $firstMessage['body_html']),
            0,
            1000
        );

        if (!$hasPerson) {
            $definitions = [];
        }

        if ($linkedContactId || $linkedAccountId) {
            $definitions['HelpDesk'] = [
                'ticket_title' => (string)$thread['subject'],
                'description' => $descriptionText,
                'contact_id' => $linkedContactId ?: null,
                'parent_id' => $linkedAccountId ?: null,
            ];
        }

        // An opportunity needs a recognized organization
        if ($linkedAccountId) {
            $definitions['Potentials'] = [
                'potentialname' => (string)$thread['subject'],
                'related_to' => $linkedAccountId,
                'contact_id' => $linkedContactId ?: null,
                'description' => $descriptionText,
            ];
        }

        // A project needs its customer too — the organization; made from a
        // conversation with a linked opportunity it points back to it (the
        // won opportunity turns into the project)
        if ($linkedAccountId && vtlib_isModuleActive('Project')) {
            $definitions['Project'] = [
                'projectname' => (string)$thread['subject'],
                'account_id' => $linkedAccountId,
                'contact_id' => $linkedContactId ?: null,
                'description' => $descriptionText,
            ];
            $potentialField = $linkedPotentialId ? self::getProjectPotentialField() : '';
            if ($potentialField !== '') {
                $definitions['Project'][$potentialField] = $linkedPotentialId;
            }
        }

        // Project tasks — offered for a linked organization that has projects;
        // the user picks the target project first (sub-step in the dropdown).
        // The task needs BOTH project modules active
        $projects = [];
        if ($linkedAccountId && vtlib_isModuleActive('Project') && vtlib_isModuleActive('ProjectTask')) {
            $projects = self::getAccountProjects($linkedAccountId);
            if ($projects) {
                $definitions['ProjectTask'] = [
                    'projecttaskname' => (string)$thread['subject'],
                    'description' => $descriptionText,
                ];
            }
        }

        // A linked case record (Ticket / Project / Opportunity) ends the create
        // flow here — further related records are created inside the CRM, not
        // from the mail. One exception: the project a linked opportunity turns
        // into — with nothing but the opportunity linked, the project is offered
        $linkedCases = array_values(array_intersect(
            MailManager_ProRelate_Model::CASE_MODULES,
            array_keys($linkedModules)
        ));
        if ($linkedCases === ['Potentials'] && isset($definitions['Project'])) {
            $definitions = ['Project' => $definitions['Project']];
        } elseif ($linkedCases) {
            return [];
        }

        // Offer only what the conversation does not have yet — a linked record
        // of the same type removes the option; a known customer (linked Contact
        // or Organization) also removes the Lead option
        foreach (array_keys($linkedModules) as $linkedModule) {
            unset($definitions[$linkedModule]);
        }
        if (isset($linkedModules['Contacts']) || isset($linkedModules['Accounts'])) {
            unset($definitions['Leads']);
        }

        $prefills = [];
        foreach ($definitions as $module => $params) {
            // A disabled module (Module Manager) must not be offered even
            // when the profile still carries the permission
            if (!vtlib_isModuleActive($module) || !Users_Privileges_Model::isPermitted($module, 'CreateView')) {
                continue;
            }
            $params = array_filter($params, function ($value) {
                return $value !== '' && $value !== null;
            });
            $prefills[$module] = [
                'label' => vtranslate('SINGLE_' . $module, $module),
                'icon' => Vtiger_Module_Model::getInstance($module)->getModuleIcon(),
                'params' => $params,
                // field labels let the QC form show extra editable rows for
                // prefill fields absent from the QC layout
                'labels' => self::getFieldLabels($module, $params),
            ];
            if ($module === 'ProjectTask') {
                $prefills[$module]['projects'] = $projects;
            }
        }

        return $prefills;
    }

    /**
     * @return string name of the Project field that points to an opportunity
     *                (installations add their own — the core has none), '' when there is none
     */
    private static function getProjectPotentialField()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT f.fieldname FROM vtiger_field f
             INNER JOIN vtiger_fieldmodulerel r ON r.fieldid = f.fieldid AND r.relmodule = 'Potentials'
             WHERE f.tabid = ? AND f.presence IN (0,2) ORDER BY f.fieldid LIMIT 1",
            [getTabid('Project')]
        );

        return $adb->num_rows($result) ? (string)$adb->query_result($result, 0, 'fieldname') : '';
    }

    /**
     * Projects of an organization — targets for the create-task sub-step
     *
     * @param int $accountId
     * @return array of ['crmid', 'label'] newest first
     */
    private static function getAccountProjects($accountId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT p.projectid, ce.label FROM vtiger_project p
             INNER JOIN vtiger_crmentity ce ON ce.crmid = p.projectid AND ce.deleted = 0
             WHERE p.account_id = ? ORDER BY p.projectid DESC LIMIT 50',
            [$accountId]
        );

        $projects = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $projects[] = ['crmid' => (int)$row['projectid'], 'label' => (string)$row['label']];
        }

        return $projects;
    }

    /**
     * A task of the conversation's project made from the mail the user is
     * reading — its subject and its own text (no quoted history)
     *
     * @param array $thread thread row
     * @param int $projectId the linked project
     * @return array|null ['params', 'labels'], null when tasks cannot be created
     */
    public static function getTaskPrefill(array $thread, $projectId)
    {
        if (!$projectId || !vtlib_isModuleActive('ProjectTask')
            || !Users_Privileges_Model::isPermitted('ProjectTask', 'CreateView')) {
            return null;
        }
        $params = array_filter([
            'projecttaskname' => (string)$thread['subject'],
            'projectid' => (int)$projectId,
            'description' => mb_substr(self::getCommentPrefill((int)$thread['threadid']), 0, 4000),
        ], function ($value) {
            return $value !== '' && $value !== null;
        });

        return ['params' => $params, 'labels' => self::getFieldLabels('ProjectTask', $params)];
    }

    /**
     * Translated labels of the module's fields — for the extra editable rows
     * injected into the QuickCreate form
     *
     * @param string $module
     * @param array $params the prefill, fieldname => value
     * @return array fieldname => ['label' => translated, 'textarea' => bool,
     *               'richtext' => bool, 'reference' => bool, 'display' => name
     *               of the referenced record] (missing fields skipped)
     */
    public static function getFieldLabels($module, array $params)
    {
        $labels = [];
        $moduleModel = Vtiger_Module_Model::getInstance($module);
        foreach ($params as $fieldName => $value) {
            $field = $moduleModel->getField($fieldName);
            if (!$field) {
                continue;
            }
            // a relation holds a record id — the form shows whom it points
            // to, the id is nothing a user can read or edit
            $isReference = $field->getFieldDataType() === 'reference';
            $labels[$fieldName] = [
                'label' => vtranslate($field->get('label'), $module),
                // text-area fields keep their line breaks only in a real
                // <textarea> — a single-line input silently drops them
                'textarea' => $field->getFieldDataType() === 'text',
                'richtext' => self::isRichTextField($module, $fieldName),
                'reference' => $isReference,
                'display' => $isReference && (int)$value > 0
                    ? decode_html((string)Vtiger_Functions::getCRMRecordLabel((int)$value))
                    : '',
            ];
        }

        return $labels;
    }

    /** @var string[] fields whose rich-text setting the create form follows */
    private const RICH_TEXT_FIELDS = ['description'];

    /**
     * Is the field edited as rich text (HTML) in this installation? The core
     * knows text areas only — it is the Descriptions extension that turns
     * chosen ones into an editor and shows their content as HTML. The create
     * form follows its settings: such a field gets the editor here too, so a
     * record made from a mail reads the same as one made in the CRM
     *
     * @param string $module
     * @param string $fieldName
     * @return bool false without the extension
     */
    public static function isRichTextField($module, $fieldName)
    {
        if (!in_array($fieldName, self::RICH_TEXT_FIELDS, true) || !vtlib_isModuleActive('ITS4YouDescriptions')
            || !class_exists('ITS4YouDescriptions_AllowedModules_Model')
            || !class_exists('ITS4YouDescriptions_AllowedFields_Model')) {
            return false;
        }

        try {
            return ITS4YouDescriptions_AllowedModules_Model::isAllowed($module)
                && ITS4YouDescriptions_AllowedFields_Model::getInstance($module)->isAllowed($fieldName);
        } catch (Throwable $e) {
            // another extension's settings — never a reason to fail the create
            return false;
        }
    }

    /**
     * Text for the "log this mail as a comment" action — the new part of the
     * newest inbound message (what the user is reading), without the quoted
     * history the record already has from the earlier messages
     *
     * @param int $threadId
     * @return string
     */
    public static function getCommentPrefill($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT messageid FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' AND is_draft = 0 ORDER BY sent_at DESC LIMIT 1",
            [$threadId]
        );
        if (!$adb->num_rows($result)) {
            // outbound-only thread — the newest message still worth logging
            $result = $adb->pquery(
                'SELECT messageid FROM df_mailmanager_message
                 WHERE threadid = ? AND is_draft = 0 ORDER BY sent_at DESC LIMIT 1',
                [$threadId]
            );
        }
        if (!$adb->num_rows($result)) {
            return '';
        }

        return trim(MailManager_ProReplyText_Model::forMessage((int)$adb->query_result($result, 0, 'messageid')));
    }
}
