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
 * Thread <-> CRM record links (df_mailmanager_rel) — linking,
 * unlinking and the linked-record cards. A newly linked case record gets
 * the conversation's mail (EmailFiler, CrmLog)
 */
class MailManager_ProRelate_Model
{
    /**
     * Case records, the most specific first — linking one files the
     * conversation's mail on it. A won opportunity turns into a project, so
     * the project outranks the opportunity it came from. They also end the
     * Create menu and take the comment log; a project task is no case record
     * here, but linking it files the mail too (isFiledOnLink())
     */
    public const CASE_MODULES = ['HelpDesk', 'Project', 'Potentials'];

    /**
     * Records that drive the conversation — the link in its head, the status
     * chip, the owner, the tabs and the detailed card of the CRM panel — in
     * ranking order: the last step of the work first (a task made from the
     * mail before the project it belongs to). Where the mail is FILED is a
     * different list (CASE_MODULES / EmailFiler)
     */
    public const DRIVING_MODULES = ['ProjectTask', 'HelpDesk', 'Project', 'Potentials', 'Leads'];

    /** The Defalto calendar module (replaces vtiger Calendar / Events, N5) */
    public const APPOINTMENT_MODULE = MailManager_Appointment_Helper::MODULE;

    /** Project statuses that close the conversation's work (core picklist values) */
    public const PROJECT_DONE = ['completed', 'delivered', 'archived'];

    /** Project task statuses that close the conversation's work (core picklist values) */
    public const TASK_DONE = ['Completed', 'Canceled'];

    /** @var bool|null see hasProjects() */
    private static $hasProjects;

    /**
     * @return bool the tables of the Project modules are there — they are
     *              optional core packages, an installation may lack them
     */
    public static function hasProjects()
    {
        if (self::$hasProjects === null) {
            self::$hasProjects = Vtiger_Utils::CheckTable('vtiger_project')
                && Vtiger_Utils::CheckTable('vtiger_projecttask');
        }

        return self::$hasProjects;
    }

    /** Field of a linked record that names its organization */
    private const ACCOUNT_FIELDS = [
        'HelpDesk' => 'parent_id',
        'Project' => 'account_id',
        'Potentials' => 'related_to',
        'Contacts' => 'account_id',
    ];

    /** Field of a linked case record that names its person */
    private const CONTACT_FIELDS = [
        'HelpDesk' => 'contact_id',
        'Project' => 'contact_id',
        'Potentials' => 'contact_id',
    ];

    /**
     * A case record brings its organization and its person along — linking a
     * ticket links the customer it is for, so the panel always reads
     * organization, people, case record instead of offering the same
     * organization once more as a suggestion. A party the user unlinked from
     * the conversation stays away
     *
     * @param int $threadId
     * @param int $crmid the case record
     * @param string $module
     * @param int $userId
     * @return void
     */
    private static function linkParties($threadId, $crmid, $module, $userId)
    {
        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $module);
        } catch (Exception $e) {
            return;
        }
        $fields = array_unique(array_filter([
            self::ACCOUNT_FIELDS[$module] ?? '',
            self::CONTACT_FIELDS[$module] ?? '',
        ]));
        foreach ($fields as $fieldName) {
            $partyId = (int)$recordModel->get($fieldName);
            if ($partyId && in_array(getSalesEntityType($partyId), self::PARTY_MODULES, true)
                && !self::isUnlinked($threadId, $partyId)) {
                // not a curating link — the case record already says what the mail is about
                self::link($threadId, $partyId, $userId, false, MailManager_ProHistory_Model::SOURCE_AUTO);
            }
        }
    }

    /**
     * The organization of the conversation — the linked one, otherwise the
     * organization of a linked record (a ticket created in the CRM brings its
     * customer along without the organization being linked itself)
     *
     * @param array $links rows of getLinks()
     * @return array|null ['crmid', 'label']
     */
    public static function getLinkedAccount(array $links)
    {
        foreach ($links as $link) {
            if ($link['module'] === 'Accounts') {
                return ['crmid' => (int)$link['crmid'], 'label' => $link['label']];
            }
        }
        foreach (self::ACCOUNT_FIELDS as $module => $fieldName) {
            foreach ($links as $link) {
                if ($link['module'] !== $module) {
                    continue;
                }
                try {
                    $accountId = (int)Vtiger_Record_Model::getInstanceById($link['crmid'], $module)->get($fieldName);
                } catch (Exception $e) {
                    continue;
                }
                // the reference must really be an organization the user may see
                if ($accountId && getSalesEntityType($accountId) === 'Accounts'
                    && Users_Privileges_Model::isPermitted('Accounts', 'DetailView', $accountId)) {
                    return [
                        'crmid' => $accountId,
                        'label' => decode_html((string)Vtiger_Functions::getCRMRecordLabel($accountId)),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Constant values as an SQL list for IN () / FIELD () — for the module's
     * own constants only, never for request data
     *
     * @param string[] $values
     * @return string 'A', 'B', 'C'
     */
    public static function quoteList(array $values)
    {
        return "'" . implode("', '", array_map('addslashes', $values)) . "'";
    }

    /** Who the mail is with — every other linked record is what it is about */
    public const PARTY_MODULES = ['Accounts', 'Contacts'];

    /** Modules of the "link an existing record" picker, in menu order */
    public const PICKER_MODULES = ['Accounts', 'Contacts', 'Leads', 'Potentials', 'HelpDesk', 'Project'];

    /**
     * One order everywhere in the panel — the organization, then its people,
     * then the rest as it came
     *
     * @param array $rows rows carrying 'module'
     * @return array
     */
    public static function sortPartyFirst(array $rows)
    {
        $rank = function ($row) {
            $position = array_search($row['module'] ?? '', self::PARTY_MODULES, true);

            return $position === false ? count(self::PARTY_MODULES) : $position;
        };
        // the position breaks ties — usort keeps equal rows in order only from PHP 8
        $positions = array_flip(array_keys($rows));
        uksort($rows, function ($a, $b) use ($rows, $rank, $positions) {
            return [$rank($rows[$a]), $positions[$a]] <=> [$rank($rows[$b]), $positions[$b]];
        });

        return array_values($rows);
    }

    /**
     * Modules the user may pick an existing record from (core record popup).
     * 'filtered' = the popup can list the records of the linked organization
     * only — the organization has a related list of that module
     *
     * Offered is only what the conversation does not have yet: a linked
     * organization / person is not picked a second time, a linked case record
     * names its organization and person itself, and a Lead makes no sense
     * once anything is linked (the same rules as the create menu)
     *
     * @param int $accountId the linked organization, 0 when none
     * @param string[] $linkedModules modules of the records linked already
     * @param int $projectId the linked project (only its tasks are listed), 0 when none
     * @return array module => ['label', 'icon', 'filtered', and for records of
     *               a parent of their own 'parent_module', 'parent_id']
     */
    public static function getPickerModules($accountId, array $linkedModules = [], $projectId = 0)
    {
        $accountModel = $accountId ? Vtiger_Module_Model::getInstance('Accounts') : null;
        $modules = [];
        foreach (self::PICKER_MODULES as $module) {
            if (in_array($module, self::PARTY_MODULES, true)
                && (in_array($module, $linkedModules, true) || array_intersect(self::CASE_MODULES, $linkedModules))) {
                continue;
            }
            if ($module === 'Leads' && $linkedModules) {
                continue;
            }
            if (!vtlib_isModuleActive($module) || !Users_Privileges_Model::isPermitted($module, 'DetailView')) {
                continue;
            }
            $moduleModel = Vtiger_Module_Model::getInstance($module);
            $modules[$module] = [
                'label' => vtranslate($module, $module),
                'icon' => $moduleModel->getModuleIcon(),
                'filtered' => $accountModel && $module !== 'Accounts'
                    && (bool)Vtiger_Relation_Model::getInstance($accountModel, $moduleModel),
            ];
        }

        // A project task — the last step of the chain (a task made in the
        // CRM, or one whose link to the mail got lost). With a project linked
        // the popup lists that project's tasks only; without one all of them
        // (tasks have no related list under an organization to filter by)
        if (!in_array('ProjectTask', $linkedModules, true) && vtlib_isModuleActive('ProjectTask')
            && Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView')) {
            $modules['ProjectTask'] = [
                'label' => vtranslate('ProjectTask', 'ProjectTask'),
                'icon' => Vtiger_Module_Model::getInstance('ProjectTask')->getModuleIcon(),
                'filtered' => false,
                'parent_module' => $projectId ? 'Project' : '',
                'parent_id' => (int)$projectId,
            ];
        }

        return $modules;
    }

    /**
     * @param int $threadId
     * @return bool the thread is linked to a (non-deleted) ticket
     */
    public static function hasTicketLink($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ? AND ce.setype = 'HelpDesk' LIMIT 1",
            [$threadId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * @param int $threadId
     * @param bool $visibleOnly only records the current user may view — for
     *                          display; the chain logic needs every link
     * @param bool $withDetails the CRM panel shows the records (summary,
     *                          key fields); the logic only needs who is linked
     * @return array of ['crmid', 'label', 'module', 'icon', 'summary', 'owner',
     *                  'details' => [['label', 'value']]] linked records
     */
    public static function getLinks($threadId, $visibleOnly = false, $withDetails = false)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT r.relid, r.crmid, r.setype, ce.label, ce.assigned_user_id FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ?
             ORDER BY FIELD(r.setype, \'Contacts\', \'Accounts\') DESC, r.createdtime',
            [$threadId]
        );

        // the organization first, then its people, then the rest as linked
        $rows = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            // In a shared mailbox a record may be linked by someone (or the
            // cron) the current user cannot see — its card must not leak
            if ($visibleOnly && !Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                continue;
            }
            $rows[] = $row;
        }

        // Only the END record — the last step of the work (the newest task,
        // otherwise the project of a won opportunity, not the opportunity) —
        // shows its key and summary fields; every other record is one line
        $endIndex = $withDetails ? self::findEndRecordIndex($rows) : null;
        $links = [];
        foreach ($rows as $index => $row) {
            $details = $index === $endIndex ? self::buildRecordDetails((int)$row['crmid'], $row['setype']) : [];
            $links[] = [
                'crmid' => (int)$row['crmid'],
                // stored html-encoded by vtiger; the template escapes it once more
                'label' => decode_html($row['label']),
                'module' => $row['setype'],
                'icon' => Vtiger_Module_Model::getInstance($row['setype'])->getModuleIcon(),
                'summary' => $details || !$withDetails
                    ? []
                    : self::getSummaryLines((int)$row['crmid'], $row['setype']),
                'owner' => trim((string)getOwnerName((int)$row['assigned_user_id'])),
                // a group shows another icon than a person
                'owner_group' => 'Group' === Vtiger_Owner_UIType::getOwnerType((int)$row['assigned_user_id']),
                'details' => $details,
            ];
        }
        // the detailed card closes the list — the one-line cards stay together above it
        if ($endIndex !== null) {
            $links[] = array_splice($links, $endIndex, 1)[0];
        }

        return $links;
    }

    /**
     * The record that drives the conversation among its links — the highest
     * ranked module (DRIVING_MODULES), the newest link within it
     *
     * @param array $rows link rows carrying 'setype' and 'relid'
     * @return int|null index into $rows, null when no driving record is linked
     */
    private static function findEndRecordIndex(array $rows)
    {
        $endIndex = null;
        $endRank = null;
        foreach ($rows as $index => $row) {
            $rank = array_search($row['setype'], self::DRIVING_MODULES, true);
            if ($rank === false) {
                continue;
            }
            if ($endIndex === null || $rank < $endRank
                || ($rank === $endRank && (int)$row['relid'] > (int)$rows[$endIndex]['relid'])) {
                $endIndex = $index;
                $endRank = $rank;
            }
        }

        return $endIndex;
    }

    /**
     * Short record subline (e.g. "Firma s.r.o. · +421 903 111 222") — for the
     * AI prompts, which take a record in one line
     *
     * @param int $crmid
     * @param string $module
     * @return string plain text, max 3 values
     */
    public static function buildRecordSummary($crmid, $module)
    {
        return implode(' · ', self::getSummaryLines((int)$crmid, (string)$module));
    }

    /**
     * The values a record card lists under its name, one per line: the
     * record's process state, then the header fields (Layout Editor) — a date
     * with the name of its field
     *
     * @param int $crmid
     * @param string $module
     * @return string[] plain text, max 3 values
     */
    public static function getSummaryLines(int $crmid, string $module): array
    {
        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $module);
        } catch (Exception $e) {
            return [];
        }

        // The record's own process state leads the summary — the conversation
        // status (Open/In Progress/Done) is a separate mailbox workflow
        $statusFields = [
            'ProjectTask' => 'projecttaskstatus',
            'HelpDesk' => 'ticketstatus',
            'Project' => 'projectstatus',
            'Potentials' => 'sales_stage',
            'Leads' => 'leadstatus',
        ];
        $parts = [];
        if (isset($statusFields[$module])) {
            $status = self::getPlainDisplayValue($recordModel, $statusFields[$module]);
            if ($status !== '') {
                $parts[] = $status;
            }
        }

        foreach (Vtiger_Module_Model::getInstance($module)->getHeaderViewFieldsList() as $fieldName => $fieldModel) {
            $value = self::getPlainDisplayValue($recordModel, $fieldName);
            // a bare date says nothing (start? deadline?) — it gets the field's name
            if ($value !== '' && in_array($fieldModel->getFieldDataType(), ['date', 'datetime'], true)) {
                $value = vtranslate($fieldModel->get('label'), $module) . ': ' . $value;
            }
            if ($value !== '' && !in_array($value, $parts, true)) {
                $parts[] = $value;
            }
            if (count($parts) >= 3) {
                break;
            }
        }

        return $parts;
    }

    /**
     * Marks the cards the assistant linked by itself on a certain match — the
     * card says so, the reason shows on hover
     *
     * @param array $links rows of getLinks()
     * @param array $thread thread row (triage_payload)
     * @return array the links with 'autolinked' (reason key, '' = linked by hand)
     */
    public static function markAutoLinked(array $links, array $thread): array
    {
        $payload = json_decode((string)($thread['triage_payload'] ?? ''), true) ?: [];
        $reasons = array_column(is_array($payload['autolink'] ?? null) ? $payload['autolink'] : [], 'reason', 'crmid');

        foreach ($links as $index => $link) {
            $links[$index]['autolinked'] = (string)($reasons[$link['crmid']] ?? '');
        }

        return $links;
    }

    /**
     * A field as plain text — the display value comes html-encoded (a related
     * record's name as a link, "š" as an entity) and the templates escape it
     * once more
     *
     * @param Vtiger_Record_Model $recordModel
     * @param string $fieldName
     * @return string
     */
    private static function getPlainDisplayValue(Vtiger_Record_Model $recordModel, $fieldName)
    {
        return trim(html_entity_decode(
            strip_tags((string)$recordModel->getDisplayValue($fieldName)),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        ));
    }

    /**
     * The record at a glance — its key fields (record header) and summary
     * fields as the admin picked them in the Layout Editor, plus the
     * assigned user; filled values only, formatted for the current user;
     * long texts and fields the user may not see are left out
     *
     * @param int $crmid
     * @param string $module
     * @return array of ['label', 'value'] plain text
     */
    public static function buildRecordDetails($crmid, $module)
    {
        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $module);
        } catch (Exception $e) {
            return [];
        }

        $moduleModel = Vtiger_Module_Model::getInstance($module);
        // the name is the card title already
        $skip = $moduleModel->getNameFields();
        $fields = $moduleModel->getHeaderViewFieldsList() + $moduleModel->getSummaryViewFieldsList();
        // who works on it is always there — last, unless the admin placed it
        $ownerField = $moduleModel->getField('assigned_user_id');
        if (!isset($fields['assigned_user_id']) && $ownerField && $ownerField->isViewable()) {
            $fields['assigned_user_id'] = $ownerField;
        }
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $details = [];
        foreach ($fields as $fieldName => $fieldModel) {
            // long texts (description…) belong to the record, not to a glance
            if (in_array($fieldName, $skip, true) || $fieldModel->getFieldDataType() === 'text') {
                continue;
            }
            $value = self::getPlainDisplayValue($recordModel, $fieldName);
            if ($value === '') {
                continue;
            }
            // a converted amount (uitype 71) is in the user's currency — with its symbol
            if ((int)$fieldModel->get('uitype') === 71) {
                $value = CurrencyField::appendCurrencySymbol(
                    $value,
                    html_entity_decode((string)$currentUser->get('currency_symbol'), ENT_QUOTES | ENT_HTML5, 'UTF-8')
                );
            }
            $details[] = ['label' => vtranslate($fieldModel->get('label'), $module), 'value' => $value];
        }

        return $details;
    }

    /**
     * The contact of an appointment scheduled from the conversation. Both references
     * must be records linked to the thread; a contact-only conversation sends no
     * target (a contact belongs in contact_id).
     *
     * @param int $threadId
     * @param int $targetId the parent record of the appointment, 0 = none
     *
     * @return int|null the first linked contact (0 = none), null when the references are not linked
     */
    public static function getAppointmentContactId(int $threadId, int $targetId): ?int
    {
        $contactId = 0;
        $targetLinked = false;

        foreach (self::getLinks($threadId) as $link) {
            if ($targetId && (int)$link['crmid'] === $targetId) {
                $targetLinked = true;
            }

            if (!$contactId && 'Contacts' === $link['module']) {
                $contactId = (int)$link['crmid'];
            }
        }

        if (($targetId && !$targetLinked) || (!$targetId && !$contactId)) {
            return null;
        }

        return $contactId;
    }

    /**
     * Relates an appointment to its parent and contact and shows it among the
     * conversation's records — it does not curate the thread (no label, no AI
     * suggestion resolution), it is a by-product of the mail.
     *
     * @param int $threadId
     * @param int $eventId   the appointment
     * @param int $targetId  the parent record, 0 = none
     * @param int $contactId of getAppointmentContactId()
     * @param int $userId
     *
     * @return void
     * @throws Exception when the appointment cannot be saved
     */
    public static function saveAppointmentLink(int $threadId, int $eventId, int $targetId, int $contactId, int $userId): void
    {
        MailManager_Appointment_Helper::relate($eventId, $targetId, $contactId);
        self::link($threadId, $eventId, $userId, false);
    }

    /**
     * Links a record created from the conversation and, when linked, fills the relation
     * fields the create prefill intended – QuickCreate layouts often lack them.
     *
     * @param int   $threadId
     * @param int   $crmid      the record (usually freshly created)
     * @param int   $userId
     * @param bool  $curates    see link(); false for by-products (project task)
     * @param array $references create prefill values sent by the client
     *
     * @return bool linked
     */
    public static function linkWithReferences(int $threadId, int $crmid, int $userId, bool $curates, array $references): bool
    {
        $success = self::link($threadId, $crmid, $userId, $curates);

        if ($success) {
            // QuickCreate layouts often lack the relation fields — fill the
            // intended references (contact/organization) after creation
            self::saveRecordReferences($crmid, $references);
        }

        return $success;
    }

    /**
     * Sets the relation fields the create-from-mail prefill intended when the
     * QuickCreate form did not carry them. Relation references ONLY — the
     * plain data fields (signature address/phone…) are injected into the QC
     * form as editable rows and the core Save persists the user's version
     *
     * The values come from the client — the record must be editable by the
     * user and every reference must be an existing record of a module the
     * field accepts, visible to the user
     *
     * @param int   $crmid   freshly created record
     * @param array $prefill create prefill values
     *
     * @return void
     */
    public static function saveRecordReferences(int $crmid, array $prefill): void
    {
        $relationFields = ['account_id', 'contact_id', 'parent_id', 'related_to', 'projectid'];
        $wanted = array_intersect_key($prefill, array_flip($relationFields));
        $module = getSalesEntityType($crmid);

        if (!$wanted || !$module || !Users_Privileges_Model::isPermitted($module, 'EditView', $crmid)) {
            return;
        }

        try {
            $record = Vtiger_Record_Model::getInstanceById($crmid, $module);
            $changed = false;

            foreach ($wanted as $field => $value) {
                $fieldModel = $record->getModule()->getField($field);
                $referenceId = (int)$value;

                if ($referenceId <= 0 || !$fieldModel || !empty($record->get($field))
                    || !self::isAcceptableReference($fieldModel, $referenceId)) {
                    continue;
                }

                $record->set($field, $referenceId);
                $changed = true;
            }

            if ($changed) {
                $record->set('mode', 'edit');
                $record->save();
            }
        } catch (Exception $e) {
            // enrichment is best-effort — the record and the link already exist
        }
    }

    /**
     * @param Vtiger_Field_Model $fieldModel  relation field being filled
     * @param int                $referenceId record the client wants to reference
     *
     * @return bool the record exists, fits the field and the user may see it
     */
    private static function isAcceptableReference($fieldModel, int $referenceId): bool
    {
        if (!isRecordExists($referenceId)) {
            return false;
        }

        $referenceModule = getSalesEntityType($referenceId);

        return in_array($referenceModule, $fieldModel->getReferenceList(), true)
            && Users_Privileges_Model::isPermitted($referenceModule, 'DetailView', $referenceId);
    }

    /**
     * Links a thread to a CRM record
     *
     * @param int $threadId
     * @param int $crmid
     * @param int $userId
     * @param bool $curates true when the link states what the mail is about
     *                      (labels the thread, resolves the AI suggestion);
     *                      false for by-products like a scheduled event
     * @param string $source who links it — MailManager_ProHistory_Model::SOURCE_*
     * @return bool
     */
    public static function link(
        $threadId,
        $crmid,
        $userId,
        $curates = true,
        $source = MailManager_ProHistory_Model::SOURCE_USER
    ) {
        $adb = PearDatabase::getInstance();

        $record = $adb->pquery('SELECT setype, label FROM vtiger_crmentity WHERE crmid = ? AND deleted = 0', [$crmid]);
        if (!$adb->num_rows($record)) {
            return false;
        }
        $module = $adb->query_result($record, 0, 'setype');
        if (!Users_Privileges_Model::isPermitted($module, 'DetailView', $crmid)) {
            return false;
        }

        $existing = $adb->pquery(
            'SELECT relid FROM df_mailmanager_rel WHERE threadid = ? AND crmid = ?',
            [$threadId, $crmid]
        );
        $isNewLink = !$adb->num_rows($existing);
        if ($isNewLink) {
            $adb->pquery(
                'INSERT INTO df_mailmanager_rel (threadid, crmid, setype, linked_by, source, createdtime)
                 VALUES (?,?,?,?,?,?)',
                [$threadId, $crmid, $module, $userId, $source, date('Y-m-d H:i:s')]
            );
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_LINK,
                $source,
                $module,
                $adb->query_result($record, 0, 'label'),
                $crmid
            );
        } elseif ($source === MailManager_ProHistory_Model::SOURCE_USER) {
            // the user confirms a link the sync made — it is his now
            $adb->pquery(
                'UPDATE df_mailmanager_rel SET source = ? WHERE threadid = ? AND crmid = ?',
                [$source, $threadId, $crmid]
            );
        }
        // an explicit link lifts an earlier unlink (auto-link checks it first)
        $adb->pquery('DELETE FROM df_mailmanager_unlink WHERE threadid = ? AND crmid = ?', [$threadId, $crmid]);

        if ($curates) {
            self::applyLabelFromModule($threadId, $module, $source);

            // A linked record resolves a pending AI triage suggestion
            $adb->pquery(
                "UPDATE df_mailmanager_thread SET triage_state = 'accepted' WHERE threadid = ? AND triage_state = 'suggested'",
                [$threadId]
            );
        }

        // Shared contract with Basic (N15): a new link of a case record, or of
        // any record the USER links, files the conversation's messages on it
        // as ITS4YouEmails records with mail_message_key — Basic then shows the
        // mail as linked. An automatic link happens on new mail, which the sync
        // files by itself; opening an old conversation must not file old mail.
        // No comment here — the comment log covers only mail arriving later
        if ($isNewLink && self::isFiledOnLink($module, $source)) {
            MailManager_ProEmailFiler_Model::fileThreadOnRecord($threadId, $crmid);
        }

        if ($isNewLink && in_array($module, self::CASE_MODULES, true)) {
            self::linkParties($threadId, $crmid, $module, $userId);
        }

        return true;
    }

    /**
     * Whether linking a record files the conversation's messages on it: a
     * case record and a project task always, every other e-mail target only
     * when the user links it.
     *
     * @param string $module module of the linked record
     * @param string $source who links it — MailManager_ProHistory_Model::SOURCE_*
     *
     * @return bool
     */
    public static function isFiledOnLink(string $module, string $source): bool
    {
        if (!in_array($module, MailManager_ProEmailFiler_Model::TARGET_MODULES, true)) {
            return false;
        }

        // a task linked to the conversation takes its mail (and its project shows it too)
        return in_array($module, self::CASE_MODULES, true) || 'ProjectTask' === $module
            || MailManager_ProHistory_Model::SOURCE_USER === $source;
    }

    /**
     * Manual classification — linking a record labels the thread by intent:
     * a Ticket means Support, an Opportunity/Lead means Sales (mapping via
     * the configurable label suggested_module). Only when no label is set yet.
     *
     * @param int $threadId
     * @param string $module linked record module
     * @param string $source who linked it — MailManager_ProHistory_Model::SOURCE_*
     * @return void
     */
    private static function applyLabelFromModule($threadId, $module, $source)
    {
        // An organization or a person says nothing about the intent — they
        // get linked to every kind of mail (auto-link included)
        if (in_array($module, ['Accounts', 'Contacts'], true)) {
            return;
        }
        // Opportunities and Leads share the Sales label, whichever of the
        // two it is configured with; the exact module wins
        $aliasMap = ['Potentials' => 'Leads', 'Leads' => 'Potentials'];
        $alias = $aliasMap[$module] ?? $module;

        $adb = PearDatabase::getInstance();
        $mailbox = $adb->pquery('SELECT mailboxid FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);
        if (!$adb->num_rows($mailbox)) {
            return;
        }
        // only a label of this mailbox — never another user's own label
        list($scopeSql, $scopeParams) = Settings_MailManager_Label_Model::mailboxScopeSql(
            'l',
            (int)$adb->query_result($mailbox, 0, 'mailboxid')
        );
        $result = $adb->pquery(
            'SELECT l.labelid FROM df_mailmanager_label l
             WHERE l.suggested_module IN (?, ?) AND l.active = 1 AND ' . $scopeSql . '
             ORDER BY l.suggested_module = ? DESC, l.owner_userid IS NOT NULL, l.sequence LIMIT 1',
            array_merge([$module, $alias], $scopeParams, [$module])
        );
        if (!$adb->num_rows($result)) {
            return;
        }

        $labelId = (int)$adb->query_result($result, 0, 'labelid');
        $update = $adb->pquery(
            'UPDATE df_mailmanager_thread SET labelid = ? WHERE threadid = ? AND labelid IS NULL',
            [$labelId, $threadId]
        );
        if ($adb->getAffectedRowCount($update)) {
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_LABEL,
                $source,
                null,
                MailManager_ProHistory_Model::getLabelName($labelId)
            );
        }
    }

    /**
     * Removes a link and remembers the decision — the auto-link would
     * otherwise link the same record again on the next panel open
     *
     * @param int $threadId
     * @param int $crmid
     * @return void
     */
    public static function unlink($threadId, $crmid)
    {
        $adb = PearDatabase::getInstance();
        $linked = $adb->pquery(
            'SELECT r.setype, e.label FROM df_mailmanager_rel r
             LEFT JOIN vtiger_crmentity e ON e.crmid = r.crmid
             WHERE r.threadid = ? AND r.crmid = ?',
            [$threadId, $crmid]
        );
        if ($adb->num_rows($linked)) {
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_UNLINK,
                MailManager_ProHistory_Model::SOURCE_USER,
                $adb->query_result($linked, 0, 'setype'),
                $adb->query_result($linked, 0, 'label'),
                $crmid
            );
        }
        $adb->pquery('DELETE FROM df_mailmanager_rel WHERE threadid = ? AND crmid = ?', [$threadId, $crmid]);
        $adb->pquery(
            'INSERT IGNORE INTO df_mailmanager_unlink (threadid, crmid, createdtime) VALUES (?,?,?)',
            [$threadId, $crmid, date('Y-m-d H:i:s')]
        );
    }

    /**
     * A suggestion the user does not want for this conversation — never
     * offered (or auto-linked) again; linking the record by hand lifts it
     *
     * @param int $threadId
     * @param int $crmid
     * @return void
     */
    public static function dismissSuggestion($threadId, $crmid)
    {
        PearDatabase::getInstance()->pquery(
            'INSERT INTO df_mailmanager_unlink (threadid, crmid, createdtime, dismissed) VALUES (?,?,?,1)
             ON DUPLICATE KEY UPDATE dismissed = 1',
            [$threadId, $crmid, date('Y-m-d H:i:s')]
        );
    }

    /**
     * @param int $threadId
     * @return int[] records dismissed as a suggestion for the conversation
     */
    public static function getDismissedIds($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT crmid FROM df_mailmanager_unlink WHERE threadid = ? AND dismissed = 1',
            [$threadId]
        );
        $ids = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $ids[] = (int)$adb->query_result($result, $i, 'crmid');
        }

        return $ids;
    }

    /**
     * @param int $threadId
     * @param int $crmid
     * @return bool the user unlinked this record from the conversation
     */
    public static function isUnlinked($threadId, $crmid)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_unlink WHERE threadid = ? AND crmid = ?',
            [$threadId, $crmid]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * The thread's linked not-yet-closed ticket (newest link wins) — the
     * target of the close-ticket suggestion. Public: the CloseTicket action
     * re-derives the target server-side instead of trusting the client
     *
     * @param int $threadId
     * @return array|null ['crmid', 'label']
     */
    public static function findOpenLinkedTicket($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT r.crmid, ce.label FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             INNER JOIN vtiger_troubletickets tt ON tt.ticketid = r.crmid
             WHERE r.threadid = ? AND tt.ticketstatus != 'Closed'
             ORDER BY r.relid DESC LIMIT 1",
            [$threadId]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }
}
