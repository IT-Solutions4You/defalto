<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_Install_Model extends Core_Install_Model
{
    /**
     * Table whose existence marks an installation that started Mail Manager Pro once.
     */
    public const PRO_MARKER_TABLE = 'df_mailmanager_config';

    /**
     * Config row written as the last step of initializePro(); its presence means the
     * first Pro start completed (tables, labels and cron).
     */
    public const PRO_INITIALIZED_SETTING = 'pro_initialized_at';

    /**
     * MySQL named lock that serializes concurrent first Pro starts.
     */
    public const PRO_INITIALIZE_LOCK = 'df_mailmanager_initialize';

    /**
     * Pro background synchronization. Kept apart from the inherited $registerCron so the
     * Basic lifecycle never registers it; see updateProCron().
     *
     * @var array
     */
    public array $registerProCron = [
        ['MailManagerProSync', 'modules/MailManager/cron/ProSync.php', 900, 'MailManager', 0, 'Mail Manager Pro background synchronization'],
        ['MailManagerProScheduled', 'modules/MailManager/cron/ProScheduled.php', 60, 'MailManager', 0, 'Mail Manager Pro scheduled sending'],
    ];

    /**
     * Settings menu item of Mail Manager Pro (Settings > Extensions, as in the original
     * extension); registered with the Pro schema only, so Basic installations do not get it.
     *
     * @var array
     */
    public array $registerProSettingsLinks = [
        ['MailManager', 'index.php?module=MailManager&parent=Settings&view=Index', 'LBL_EXTENSIONS'],
    ];

    /**
     * Global header script of Mail Manager Pro (browser notifications of new mail);
     * registered with the Pro schema only, so Basic pages do not load it.
     *
     * @var array
     */
    public array $registerProCustomLinks = [
        ['MailManager', 'HEADERSCRIPT', 'MailManager_ProNotifier_Js', 'layouts/$LAYOUT$/modules/MailManager/resources/ProNotifier.js'],
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return [
            'vtiger_mailmanager_mailrel',
            'vtiger_mailmanager_mailrecord',
            'vtiger_mailmanager_mailattachments',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_mailmanager_mailrel', null)
            ->createTable('mailuid', 'varchar(999) default NULL')
            ->createColumn('crmid', 'int(11) default NULL')
            ->createColumn('emailid', 'int(11) default NULL')
        ;

        $this->getTable('vtiger_mailmanager_mailrecord', null)
            ->createTable('userid', 'int(11) default NULL')
            ->createColumn('mfrom','varchar(255) default NULL')
            ->createColumn('mto','varchar(255) default NULL')
            ->createColumn('mcc','varchar(500) default NULL')
            ->createColumn('mbcc','varchar(500) default NULL')
            ->createColumn('mdate','varchar(20) default NULL')
            ->createColumn('msubject','varchar(500) default NULL')
            ->createColumn('mbody','text')
            ->createColumn('mcharset','varchar(10) default NULL')
            ->createColumn('misbodyhtml','int(1) default NULL')
            ->createColumn('mplainmessage','int(1) default NULL')
            ->createColumn('mhtmlmessage','int(1) default NULL')
            ->createColumn('muniqueid','varchar(500) default NULL')
            ->createColumn('mbodyparsed','int(1) default NULL')
            ->createColumn('muid','int(11) default NULL')
            ->createColumn('lastsavedtime','int(11) default NULL')
            ->createColumn('folder','varchar(250)')
            ->createColumn('mfolder','VARCHAR(250)')
            ->createKey('KEY IF NOT EXISTS `userid_lastsavedtime_idx` (`userid`,`lastsavedtime`)')
            ->createKey('KEY IF NOT EXISTS `userid_muid_idx` (`userid`,`muid`)')
        ;

        $this->getTable('vtiger_mailmanager_mailattachments', null)
            ->createTable('userid', 'int(11) default NULL')
            ->createColumn('muid','int(11) default NULL')
            ->createColumn('aname','varchar(100) default NULL')
            ->createColumn('lastsavedtime','int(11) default NULL')
            ->createColumn('attachid','int(19) NOT NULL')
            ->createColumn('path','varchar(200) NOT NULL')
            ->createColumn('cid','varchar(100) default NULL')
            ->createColumn('mfolder','varchar(250) default NULL')
            ->createKey('KEY IF NOT EXISTS `userid_muid_idx` (`userid`,`muid`)')
        ;

        $this->getTable('vtiger_mailscanner_entitymethod', 'mailscanner_entitymethod_id')
            ->createTable()
            ->createColumn('module_name', 'varchar(100)')
            ->createColumn('method_name', 'varchar(100)')
            ->createColumn('function_path', 'varchar(400)')
            ->createColumn('function_name', 'varchar(100)')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`mailscanner_entitymethod_id`)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `mailscanner_entitymethod_idx` (`mailscanner_entitymethod_id`)');

        $this->getTable('vtiger_mailscanner_actions', 'actionid')
            ->createTable()
            ->createColumn('scannerid', 'int(11) DEFAULT NULL')
            ->createColumn('actiontype', 'varchar(100) DEFAULT NULL')
            ->createColumn('module', 'varchar(100) DEFAULT NULL')
            ->createColumn('lookup', 'varchar(100) DEFAULT NULL')
            ->createColumn('sequence', 'int(11) DEFAULT NULL')
            ->createkey('PRIMARY KEY IF NOT EXISTS (`actionid`)');

        $this->getTable('vtiger_mailscanner', 'scannerid')
            ->createTable()
            ->createColumn('scannername', 'varchar(30) DEFAULT NULL')
            ->createColumn('server', 'varchar(100) DEFAULT NULL')
            ->createColumn('protocol', 'varchar(10) DEFAULT NULL')
            ->createColumn('username', 'varchar(255) DEFAULT NULL')
            ->createColumn('password', 'varchar(255) DEFAULT NULL')
            ->createColumn('ssltype', 'varchar(10) DEFAULT NULL')
            ->createColumn('sslmethod', 'varchar(30) DEFAULT NULL')
            ->createColumn('connecturl', 'varchar(255) DEFAULT NULL')
            ->createColumn('searchfor', 'varchar(10) DEFAULT NULL')
            ->createColumn('markas', 'varchar(10) DEFAULT NULL')
            ->createColumn('isvalid', 'int(1) DEFAULT NULL')
            ->createColumn('scanfrom', 'varchar(10) DEFAULT \'ALL\'')
            ->createColumn('time_zone', 'varchar(10) DEFAULT NULL')
            ->createColumn('client_id', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_secret', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_token', 'text DEFAULT NULL')
            ->createColumn('client_access_token', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`scannerid`)')
            ;

        $this->getTable('vtiger_mailscanner_folders', 'folderid')
            ->createTable()
            ->createColumn('scannerid','int(19) DEFAULT NULL')
            ->createColumn('foldername','varchar(255) DEFAULT NULL')
            ->createColumn('lastscan','varchar(30) DEFAULT NULL')
            ->createColumn('rescan','int(1) DEFAULT NULL')
            ->createColumn('enabled','int(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`folderid`)')
            ->createKey('KEY IF NOT EXISTS `folderid_idx` (`folderid`)')
            ;

        $this->getTable('vtiger_mailscanner_ids', 'scannerid')
            ->createTable('scannerid', 'int(19) DEFAULT NULL')
            ->createColumn('messageid','varchar(512) DEFAULT NULL')
            ->createColumn('crmid','int(19) DEFAULT NULL')
            ->createColumn('refids','text DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `scanner_message_ids_idx` (`scannerid`,`messageid`)')
            ->createKey('KEY IF NOT EXISTS `messageids_crmid_idx` (`crmid`)')
        ;

        $this->getTable('vtiger_mailscanner_ruleactions', 'ruleid')
            ->createTable('ruleid', 'int(19) DEFAULT NULL')
            ->createColumn('actionid', 'int(19) DEFAULT NULL')
        ;

        $this->getTable('vtiger_mailscanner_rules', 'ruleid')
            ->createTable()
            ->createColumn('scannerid','int(11) DEFAULT NULL')
            ->createColumn('fromaddress','varchar(255) DEFAULT NULL')
            ->createColumn('toaddress','varchar(255) DEFAULT NULL')
            ->createColumn('subjectop','varchar(20) DEFAULT NULL')
            ->createColumn('subject','varchar(255) DEFAULT NULL')
            ->createColumn('bodyop','varchar(20) DEFAULT NULL')
            ->createColumn('body','varchar(255) DEFAULT NULL')
            ->createColumn('matchusing','varchar(5) DEFAULT NULL')
            ->createColumn('sequence','int(11) DEFAULT NULL')
            ->createColumn('assigned_to','int(10) DEFAULT NULL')
            ->createColumn('cc','varchar(255) DEFAULT NULL')
            ->createColumn('bcc','varchar(255) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`ruleid`)')
        ;

        if ($this->isProSchemaInstalled()) {
            $this->installProTables();
        }
    }

    /**
     * Returns true when the Pro schema exists, i.e. Mail Manager Pro was started at least
     * once on this installation. Basic installations never get Pro tables.
     *
     * @return bool
     */
    public function isProSchemaInstalled(): bool
    {
        return (bool)Vtiger_Utils::CheckTable(self::PRO_MARKER_TABLE);
    }

    /**
     * Returns the names of all Pro tables.
     *
     * @return string[]
     */
    public function getProTables(): array
    {
        return array_keys($this->getProTableDefinitions());
    }

    /**
     * Creates or updates every Pro table. Idempotent: it runs on the first Pro start and
     * on every later system update of an installation that already has the Pro schema.
     * The marker table is created last so an interrupted run is completed next time.
     *
     * @return void
     * @throws Exception
     */
    public function installProTables(): void
    {
        $definitions = $this->getProTableDefinitions();
        $marker = [self::PRO_MARKER_TABLE => $definitions[self::PRO_MARKER_TABLE]];
        unset($definitions[self::PRO_MARKER_TABLE]);

        foreach ($definitions + $marker as $tableName => $definition) {
            $this->installProTable($tableName, $definition);
        }

        $this->updateLabelColors();
        // the default agents from the former single provider / model (nothing changes)
        MailManager_ProAiAgent_Model::ensureDefaults();
        $this->updateProSettingsLinks();
        $this->updateProCustomLinks();
    }

    /**
     * Registers (or removes) the Pro header script links; idempotent.
     *
     * @param bool $register
     *
     * @return void
     */
    public function updateProCustomLinks(bool $register = true): void
    {
        foreach ($this->registerProCustomLinks as [$moduleName, $type, $label, $url]) {
            $module = Vtiger_Module::getInstance($moduleName);

            if (!$module) {
                continue;
            }

            $module->deleteLink($type, $label);

            if ($register) {
                $module->addLink($type, $label, str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url));
            }
        }
        $this->registerMissingProCron();
    }

    /**
     * Registers the Pro cron tasks an installation does not have yet (a task added in a
     * later version); existing tasks keep their state and the administrator's frequency.
     *
     * @return void
     */
    public function registerMissingProCron(): void
    {
        foreach ($this->registerProCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = array_pad($cronInfo, 6, null);

            if (!Vtiger_Cron::getInstance($name)) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

    /**
     * Registers (or removes) the Pro Settings menu item; idempotent.
     *
     * @param bool $register
     *
     * @return void
     * @throws Exception
     */
    public function updateProSettingsLinks(bool $register = true): void
    {
        foreach ($this->registerProSettingsLinks as [$name, $link, $block]) {
            if ($register) {
                Settings_Vtiger_MenuItem_Model::createItem($name, $link, Settings_Vtiger_Menu_Model::createMenu($block));
            } else {
                Settings_Vtiger_MenuItem_Model::deleteItem($name);
            }
        }
    }

    /**
     * Moves every label color to the closest Defalto palette color
     * (Settings_MailManager_Label_Model::PALETTE); idempotent.
     *
     * @return void
     */
    public function updateLabelColors(): void
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT labelid, color FROM df_mailmanager_label', []);

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            $color = Settings_MailManager_Label_Model::getPaletteColor($row['color']);

            if ($color !== strtolower((string)$row['color'])) {
                $db->pquery('UPDATE df_mailmanager_label SET color = ? WHERE labelid = ?', [$color, (int)$row['labelid']]);
            }
        }
    }

    /**
     * Creates one Pro table from its definition: an auto-increment ID table when 'id' is
     * set, otherwise a table started with its first column; then columns and keys.
     *
     * @param string $tableName
     * @param array  $definition ['id' => ?string, 'columns' => [name => type], 'keys' => string[]]
     *
     * @return void
     * @throws Exception
     */
    public function installProTable(string $tableName, array $definition): void
    {
        $columns = $definition['columns'];
        $tableId = $definition['id'] ?? null;

        if ($tableId) {
            $table = $this->getTable($tableName, $tableId)->createTable();
        } else {
            $firstColumn = array_key_first($columns);
            $table = $this->getTable($tableName, null)->createTable($firstColumn, $columns[$firstColumn]);
        }

        foreach ($columns as $column => $type) {
            $table->createColumn($column, $type);
        }

        foreach ($definition['keys'] ?? [] as $key) {
            $table->createKey($key);
        }
    }

    /**
     * Registers (or removes) the Pro cron task.
     *
     * @param bool $register false removes the task
     *
     * @return void
     */
    public function updateProCron(bool $register = true): void
    {
        foreach ($this->registerProCron as $cronInfo) {
            [$name, $handler, $frequency, $module, $sequence, $description] = array_pad($cronInfo, 6, null);

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }

    /**
     * Returns true when the first Pro start completed (completion row in the config table).
     *
     * @return bool
     * @throws Exception
     */
    public function isProInitialized(): bool
    {
        return $this->isProSchemaInstalled()
            && null !== (new MailManager_ProConfig_Model())->getValue(self::PRO_INITIALIZED_SETTING);
    }

    /**
     * Prepares Mail Manager Pro on its first start: Pro tables, default labels, the Basic
     * accounts taken over as personal mailboxes and the background cron, then records completion. Idempotent and serialized by a named
     * lock, so parallel first requests do not run it twice; an interrupted run is
     * repeated on the next Pro request because completion is recorded last.
     *
     * @return void
     * @throws Exception
     */
    public function initializePro(): void
    {
        $db = PearDatabase::getInstance();
        $lock = $db->pquery('SELECT GET_LOCK(?, 30) AS locked', [self::PRO_INITIALIZE_LOCK]);

        if (1 !== (int)$db->query_result($lock, 0, 'locked')) {
            throw new Exception('Mail Manager Pro initialization is already running, please try again.');
        }

        try {
            if (!$this->isProInitialized()) {
                $this->installProTables();
                $this->saveDefaultProLabels();
                (new MailManager_ProMailbox_Model())->linkBasicAccounts();
                $this->updateProCron();
                (new MailManager_ProConfig_Model())->saveValue(self::PRO_INITIALIZED_SETTING, date('Y-m-d H:i:s'));
            }
        } finally {
            $db->pquery('SELECT RELEASE_LOCK(?)', [self::PRO_INITIALIZE_LOCK]);
        }
    }

    /**
     * Seeds the global default labels, only into an empty label table so labels the
     * administrator renamed or deleted never come back.
     *
     * @return void
     */
    public function saveDefaultProLabels(): void
    {
        $db = PearDatabase::getInstance();
        $existing = $db->pquery('SELECT 1 FROM df_mailmanager_label LIMIT 1', []);

        if ($db->num_rows($existing)) {
            return;
        }

        foreach ($this->getDefaultProLabels() as [$name, $color, $description, $suggestedModule, $sequence]) {
            $workspace = str_starts_with($suggestedModule, 'action:') ? 0 : 1;
            $db->pquery(
                'INSERT INTO df_mailmanager_label (name, color, ai_description, suggested_module, active, sequence, workspace) VALUES (?,?,?,?,1,?,?)',
                [$name, $color, $description, $suggestedModule, $sequence, $workspace]
            );
        }
    }

    /**
     * Returns the default global labels: name, colour, description for the AI triage,
     * suggested module or action, sequence. Cleanup labels only suggest their action.
     *
     * @return array
     */
    public function getDefaultProLabels(): array
    {
        return [
            ['Support', '#5e81f4', 'Problem reports, error descriptions, requests for help with our products.', 'HelpDesk', 1],
            ['Sales', '#5ce573', 'Price inquiries, demo requests, interest in products or services.', 'Potentials', 2],
            ['Spam', '#8181a5', 'Unsolicited offers, cold outreach, phishing.', 'action:delete', 3],
            ['Newsletter', '#f4cf5e', 'Marketing newsletters and promotional mailings.', 'action:spam', 4],
        ];
    }

    /**
     * Returns the complete Pro schema (ported from ITS4YouMailManager 4.69 as
     * df_mailmanager_* tables). A new column is added here; installProTables() applies it.
     *
     * @return array table name => ['id' => ?string, 'columns' => [name => type], 'keys' => string[]]
     */
    public function getProTableDefinitions(): array
    {
        return [
            'df_mailmanager_oauthapp' => [
                'id' => 'oauthappid',
                'columns' => [
                    'provider' => 'VARCHAR(20) NOT NULL',
                    'client_id' => 'VARCHAR(255) NOT NULL',
                    'client_secret' => 'TEXT NOT NULL',
                    'tenant_id' => 'VARCHAR(100) DEFAULT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'modifiedtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['UNIQUE KEY uq_provider (provider)'],
            ],
            'df_mailmanager_mailbox' => [
                'id' => 'mailboxid',
                'columns' => [
                    'name' => 'VARCHAR(100) NOT NULL',
                    'email' => 'VARCHAR(150) NOT NULL',
                    'provider' => 'VARCHAR(20) NOT NULL',
                    'mailbox_type' => "VARCHAR(10) NOT NULL DEFAULT 'personal'",
                    'owner_userid' => 'INT NOT NULL',
                    'account_id' => 'INT DEFAULT NULL',
                    'status' => "VARCHAR(20) NOT NULL DEFAULT 'active'",
                    'is_default' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'oauthappid' => 'INT DEFAULT NULL',
                    'oauth_tokens' => 'TEXT DEFAULT NULL',
                    'imap_server' => 'VARCHAR(150) DEFAULT NULL',
                    'imap_port' => 'INT UNSIGNED DEFAULT NULL',
                    'imap_encryption' => 'VARCHAR(10) DEFAULT NULL',
                    'imap_username' => 'VARCHAR(150) DEFAULT NULL',
                    'imap_password' => 'TEXT DEFAULT NULL',
                    'imap_cert_fingerprint' => 'VARCHAR(64) DEFAULT NULL',
                    'folder_delimiter' => 'VARCHAR(5) DEFAULT NULL',
                    'smtp_mode' => "VARCHAR(10) NOT NULL DEFAULT 'crm'",
                    'smtp_server' => 'VARCHAR(150) DEFAULT NULL',
                    'smtp_port' => 'INT UNSIGNED DEFAULT NULL',
                    'smtp_encryption' => 'VARCHAR(10) DEFAULT NULL',
                    'smtp_username' => 'VARCHAR(150) DEFAULT NULL',
                    'smtp_password' => 'TEXT DEFAULT NULL',
                    'sync_from' => 'DATE DEFAULT NULL',
                    'last_sync_at' => 'DATETIME DEFAULT NULL',
                    'last_error' => 'TEXT DEFAULT NULL',
                    'failed_attempts' => 'INT NOT NULL DEFAULT 0',
                    'next_retry_at' => 'DATETIME DEFAULT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'modifiedtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_owner (owner_userid)', 'UNIQUE KEY uq_account (account_id)'],
            ],
            // mail the user scheduled to go out later (I12): the SAVED ITS4YouEmails record
            // holds the content and the files, payload the rest of the send (threading, flags)
            'df_mailmanager_scheduled' => [
                'id' => 'scheduledid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'threadid' => 'INT DEFAULT NULL',
                    'userid' => 'INT NOT NULL',
                    'crm_emailid' => 'INT NOT NULL',
                    'scheduled_at' => 'DATETIME NOT NULL',
                    'status' => "VARCHAR(10) NOT NULL DEFAULT 'waiting'",
                    'claimed_at' => 'DATETIME DEFAULT NULL',
                    'payload' => 'MEDIUMTEXT DEFAULT NULL',
                    'error' => 'TEXT DEFAULT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_due (status, scheduled_at)', 'KEY idx_user (userid, status)'],
            ],
            'df_mailmanager_mailbox_access' => [
                'id' => 'accessid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'member_id' => 'INT NOT NULL',
                    'member_type' => "VARCHAR(6) NOT NULL DEFAULT 'user'",
                    'valid_until' => 'DATE DEFAULT NULL',
                ],
                'keys' => ['UNIQUE KEY uq_mailbox_member_type (mailboxid, member_type, member_id)'],
            ],
            'df_mailmanager_folder' => [
                'id' => 'folderid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'remote_id' => 'VARCHAR(255) NOT NULL',
                    'name' => 'VARCHAR(150) NOT NULL',
                    'folder_type' => "VARCHAR(15) NOT NULL DEFAULT 'custom'",
                    'sync_enabled' => 'TINYINT(1) NOT NULL DEFAULT 1',
                    'uidvalidity' => 'BIGINT DEFAULT NULL',
                    'last_uid' => 'BIGINT DEFAULT NULL',
                    'delta_cursor' => 'TEXT DEFAULT NULL',
                    'flags_modseq' => 'BIGINT DEFAULT NULL',
                    'state_signature' => 'VARCHAR(64) DEFAULT NULL',
                    'history_complete' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'total_count' => 'INT NOT NULL DEFAULT 0',
                    'unread_count' => 'INT NOT NULL DEFAULT 0',
                    'unread_base' => 'INT NOT NULL DEFAULT 0',
                ],
                'keys' => ['UNIQUE KEY uq_mailbox_remote (mailboxid, remote_id)'],
            ],
            'df_mailmanager_thread' => [
                'id' => 'threadid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'thread_key' => 'VARCHAR(255) NOT NULL',
                    'subject' => 'VARCHAR(500) DEFAULT NULL',
                    'snippet' => 'VARCHAR(255) DEFAULT NULL',
                    'message_count' => 'INT NOT NULL DEFAULT 0',
                    'unread_count' => 'INT NOT NULL DEFAULT 0',
                    'has_attachments' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'last_message_at' => 'DATETIME DEFAULT NULL',
                    'assigned_userid' => 'INT DEFAULT NULL',
                    'assign_status' => "VARCHAR(15) NOT NULL DEFAULT 'open'",
                    'labelid' => 'INT DEFAULT NULL',
                    'priority' => 'VARCHAR(10) DEFAULT NULL',
                    'ai_summary' => 'TEXT DEFAULT NULL',
                    'ai_summary_msgid' => 'INT DEFAULT NULL',
                    // the records' history the summary was made with (I20) — another reader or a new comment = a new summary
                    'ai_summary_ctx' => 'CHAR(40) DEFAULT NULL',
                    'triage_state' => "VARCHAR(15) NOT NULL DEFAULT 'none'",
                    'triage_payload' => 'TEXT DEFAULT NULL',
                    'triage_claimed_at' => 'DATETIME DEFAULT NULL',
                    'triage_attempts' => 'TINYINT NOT NULL DEFAULT 0',
                    'autocomment_msgid' => 'INT DEFAULT NULL',
                    'ai_topics' => 'VARCHAR(255) DEFAULT NULL',
                    'customer_language' => 'VARCHAR(5) DEFAULT NULL',
                    // what the sender wants, in a few words (the triage, request radar I15)
                    'request_type' => 'VARCHAR(80) DEFAULT NULL',
                    // mail of the conversation tried to instruct the AI (I16): 0 no, 1 flagged, 2 marked safe
                    'ai_suspicious' => 'TINYINT NOT NULL DEFAULT 0',
                    'ai_suspicious_at' => 'DATETIME DEFAULT NULL',
                    // the AI line of the Workspace digest, for this newest message and reader language (I19)
                    'ai_digest' => 'VARCHAR(300) DEFAULT NULL',
                    'ai_digest_msgid' => 'INT DEFAULT NULL',
                    'ai_digest_lang' => 'VARCHAR(5) DEFAULT NULL',
                ],
                'keys' => [
                    'UNIQUE KEY uq_mailbox_threadkey (mailboxid, thread_key)',
                    'KEY idx_request_type (request_type)',
                    'KEY idx_mailbox_lastmsg (mailboxid, last_message_at)',
                    'KEY idx_assignment (assigned_userid, assign_status)',
                ],
            ],
            'df_mailmanager_message' => [
                'id' => 'messageid',
                'columns' => [
                    'threadid' => 'INT NOT NULL',
                    'mailboxid' => 'INT NOT NULL',
                    'folderid' => 'INT NOT NULL',
                    'remote_uid' => 'BIGINT DEFAULT NULL',
                    'remote_id' => 'VARCHAR(255) DEFAULT NULL',
                    'header_message_id' => 'VARCHAR(255) DEFAULT NULL',
                    'header_in_reply_to' => 'VARCHAR(255) DEFAULT NULL',
                    'header_references' => 'TEXT DEFAULT NULL',
                    'reply_to' => 'VARCHAR(150) DEFAULT NULL',
                    'direction' => "VARCHAR(3) NOT NULL DEFAULT 'in'",
                    'is_draft' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'is_read' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'from_email' => 'VARCHAR(150) DEFAULT NULL',
                    'from_name' => 'VARCHAR(150) DEFAULT NULL',
                    'to_addrs' => 'TEXT DEFAULT NULL',
                    'cc_addrs' => 'TEXT DEFAULT NULL',
                    'bcc_addrs' => 'TEXT DEFAULT NULL',
                    'subject' => 'VARCHAR(500) DEFAULT NULL',
                    'body_html' => 'MEDIUMTEXT DEFAULT NULL',
                    'body_plain' => 'MEDIUMTEXT DEFAULT NULL',
                    'snippet' => 'VARCHAR(255) DEFAULT NULL',
                    'sent_at' => 'DATETIME DEFAULT NULL',
                    'size_bytes' => 'INT NOT NULL DEFAULT 0',
                    'has_attachments' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'source' => "VARCHAR(10) NOT NULL DEFAULT 'sync'",
                    'crm_emailid' => 'INT DEFAULT NULL',
                    'mail_message_key' => 'VARCHAR(100) DEFAULT NULL',
                    'draft_context' => 'TEXT DEFAULT NULL',
                    'moved_at' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => [
                    'UNIQUE KEY uq_folder_uid (folderid, remote_uid)',
                    'KEY idx_message_id (header_message_id)',
                    'KEY idx_thread_sent (threadid, sent_at)',
                    'KEY idx_mailbox_unread (mailboxid, is_read, folderid)',
                    'KEY idx_message_key (mail_message_key)',
                ],
            ],
            'df_mailmanager_attachment' => [
                'id' => 'attachmentid',
                'columns' => [
                    'messageid' => 'INT NOT NULL',
                    'remote_part' => 'VARCHAR(100) NOT NULL',
                    'filename' => 'VARCHAR(255) DEFAULT NULL',
                    'mime_type' => 'VARCHAR(100) DEFAULT NULL',
                    'size_bytes' => 'INT NOT NULL DEFAULT 0',
                    'is_inline' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'content_id' => 'VARCHAR(150) DEFAULT NULL',
                    'documentid' => 'INT DEFAULT NULL',
                ],
                'keys' => ['KEY idx_message (messageid)'],
            ],
            'df_mailmanager_label' => [
                'id' => 'labelid',
                'columns' => [
                    'name' => 'VARCHAR(50) NOT NULL',
                    'color' => "CHAR(7) NOT NULL DEFAULT '#eff3fe'",
                    'ai_description' => 'TEXT DEFAULT NULL',
                    'suggested_module' => 'VARCHAR(30) DEFAULT NULL',
                    'active' => 'TINYINT(1) NOT NULL DEFAULT 1',
                    'workspace' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'auto_action' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'sequence' => 'INT NOT NULL DEFAULT 0',
                    'owner_userid' => 'INT DEFAULT NULL',
                ],
                'keys' => ['KEY idx_owner (owner_userid)'],
            ],
            'df_mailmanager_rel' => [
                'id' => 'relid',
                'columns' => [
                    'threadid' => 'INT NOT NULL',
                    'crmid' => 'INT NOT NULL',
                    'setype' => 'VARCHAR(30) NOT NULL',
                    'linked_by' => 'INT NOT NULL DEFAULT 0',
                    'source' => "VARCHAR(10) NOT NULL DEFAULT 'user'",
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['UNIQUE KEY uq_thread_crm (threadid, crmid)', 'KEY idx_crmid (crmid)'],
            ],
            'df_mailmanager_topics' => [
                'columns' => [
                    'crmid' => 'INT NOT NULL',
                    'topics' => 'VARCHAR(255) NOT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['PRIMARY KEY (crmid)'],
            ],
            'df_mailmanager_assistant' => [
                'id' => 'assistantid',
                'columns' => [
                    'threadid' => 'INT NOT NULL',
                    'userid' => 'INT NOT NULL',
                    'role' => 'VARCHAR(10) NOT NULL',
                    'content' => 'MEDIUMTEXT',
                    'actions' => 'TEXT',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_thread_user (threadid, userid)'],
            ],
            'df_mailmanager_translation' => [
                'columns' => [
                    'messageid' => 'INT NOT NULL',
                    'language' => 'VARCHAR(10) NOT NULL',
                    'body' => 'MEDIUMTEXT',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['PRIMARY KEY (messageid, language)'],
            ],
            'df_mailmanager_knowledge' => [
                'id' => 'knowledgeid',
                'columns' => [
                    'source_type' => 'VARCHAR(10) NOT NULL',
                    'source_id' => 'INT NOT NULL',
                    'scope' => "VARCHAR(10) NOT NULL DEFAULT 'general'",
                    'owner_userid' => 'INT NOT NULL DEFAULT 0',
                    'source_title' => 'VARCHAR(255) DEFAULT NULL',
                    'title' => 'VARCHAR(255) DEFAULT NULL',
                    'chunk_no' => 'SMALLINT NOT NULL DEFAULT 0',
                    'content' => 'TEXT',
                    'topics' => 'VARCHAR(255) DEFAULT NULL',
                    'source_hash' => 'CHAR(32) DEFAULT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_source (source_type, source_id)', 'KEY idx_scope (scope, owner_userid)'],
            ],
            'df_mailmanager_unlink' => [
                'columns' => [
                    'threadid' => 'INT NOT NULL',
                    'crmid' => 'INT NOT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'dismissed' => 'TINYINT(1) NOT NULL DEFAULT 0',
                ],
                'keys' => ['PRIMARY KEY (threadid, crmid)'],
            ],
            'df_mailmanager_labeluser' => [
                'columns' => [
                    'labelid' => 'INT NOT NULL',
                    'userid' => 'INT NOT NULL',
                    'ai_description' => 'TEXT',
                    'suggested_module' => 'VARCHAR(30) DEFAULT NULL',
                    'auto_action' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'ai_enabled' => 'TINYINT(1) NOT NULL DEFAULT 1',
                    'modifiedtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['PRIMARY KEY (labelid, userid)'],
            ],
            // the AI summary of the older comments of a record (I20) — written again only when a
            // comment, the description or the solution changes (source_hash)
            'df_mailmanager_record_summary' => [
                'id' => 'summaryid',
                'columns' => [
                    'crmid' => 'INT NOT NULL',
                    'language' => 'VARCHAR(5) NOT NULL',
                    'source_hash' => 'CHAR(40) NOT NULL',
                    'last_commentid' => 'INT NOT NULL DEFAULT 0',
                    'summary' => 'MEDIUMTEXT',
                    'modifiedtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['UNIQUE KEY uq_record_hash (crmid, language, source_hash)'],
            ],
            'df_mailmanager_note' => [
                'id' => 'noteid',
                'columns' => [
                    'threadid' => 'INT NOT NULL',
                    'crmid' => 'INT NOT NULL',
                    'commentid' => 'INT NOT NULL DEFAULT 0',
                    'userid' => 'INT NOT NULL',
                    'body' => 'MEDIUMTEXT',
                    'is_private' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_note_thread (threadid)'],
            ],
            'df_mailmanager_spamrule' => [
                'id' => 'ruleid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'from_email' => 'VARCHAR(150) NOT NULL',
                    'hits' => 'INT NOT NULL DEFAULT 1',
                    'moved_count' => 'INT NOT NULL DEFAULT 0',
                    'active' => 'TINYINT(1) NOT NULL DEFAULT 1',
                    'trusted' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'last_hit_at' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['UNIQUE KEY uq_mailbox_sender (mailboxid, from_email)'],
            ],
            'df_mailmanager_labelrule' => [
                'id' => 'ruleid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'labelid' => 'INT NOT NULL',
                    'rule_type' => 'VARCHAR(10) NOT NULL',
                    'from_email' => "VARCHAR(150) NOT NULL DEFAULT ''",
                    'domain' => "VARCHAR(150) NOT NULL DEFAULT ''",
                    'subject_key' => "VARCHAR(191) NOT NULL DEFAULT ''",
                    'applied_count' => 'INT NOT NULL DEFAULT 0',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'last_applied_at' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => [
                    'UNIQUE KEY uq_rule (mailboxid, rule_type, from_email, domain, subject_key)',
                    'KEY idx_label (labelid)',
                ],
            ],
            'df_mailmanager_history' => [
                'id' => 'historyid',
                'columns' => [
                    'mailboxid' => 'INT NOT NULL',
                    'threadid' => 'INT DEFAULT NULL',
                    'action' => 'VARCHAR(10) NOT NULL',
                    'source' => 'VARCHAR(10) NOT NULL',
                    'userid' => 'INT DEFAULT NULL',
                    'from_email' => 'VARCHAR(150) DEFAULT NULL',
                    'subject' => 'VARCHAR(500) DEFAULT NULL',
                    'from_value' => 'VARCHAR(255) DEFAULT NULL',
                    'to_value' => 'VARCHAR(255) DEFAULT NULL',
                    'crmid' => 'INT DEFAULT NULL',
                    'mail_date' => 'DATETIME DEFAULT NULL',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => ['KEY idx_mailbox (mailboxid, historyid)', 'KEY idx_thread (threadid)'],
            ],
            'df_mailmanager_ailog' => [
                'id' => 'logid',
                'columns' => [
                    'userid' => 'INT NOT NULL',
                    'threadid' => 'INT DEFAULT NULL',
                    'feature' => 'VARCHAR(20) NOT NULL',
                    'model' => 'VARCHAR(50) DEFAULT NULL',
                    'input_tokens' => 'INT NOT NULL DEFAULT 0',
                    'output_tokens' => 'INT NOT NULL DEFAULT 0',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    // the AI agent that made the call (I13)
                    'agentid' => 'INT DEFAULT NULL',
                    // the provider that answered and whether it was the backup (I17)
                    'provider' => 'VARCHAR(20) DEFAULT NULL',
                    'is_backup' => 'TINYINT(1) NOT NULL DEFAULT 0',
                ],
                'keys' => ['KEY idx_user_time (userid, createdtime)'],
            ],
            // AI agents the admin sets up (I13): a provider, a model and how to call it;
            // the tasks point to an agent (df_mailmanager_config ai_task_<task>)
            'df_mailmanager_aiagent' => [
                'id' => 'agentid',
                'columns' => [
                    'name' => 'VARCHAR(100) NOT NULL',
                    'tier' => "VARCHAR(10) NOT NULL DEFAULT 'fast'",
                    'active' => 'TINYINT(1) NOT NULL DEFAULT 1',
                    'provider' => 'VARCHAR(20) NOT NULL',
                    'model' => 'VARCHAR(100) DEFAULT NULL',
                    'effort' => 'VARCHAR(10) DEFAULT NULL',
                    'instructions' => 'TEXT DEFAULT NULL',
                    'max_input_tokens' => 'INT NOT NULL DEFAULT 100000',
                    // the backup when the provider fails (I17): '' = the global one, 'none' = none
                    'backup_provider' => "VARCHAR(20) NOT NULL DEFAULT ''",
                    'backup_model' => 'VARCHAR(100) DEFAULT NULL',
                    'sequence' => 'INT NOT NULL DEFAULT 0',
                    'createdtime' => 'DATETIME DEFAULT NULL',
                    'modifiedtime' => 'DATETIME DEFAULT NULL',
                ],
                'keys' => [],
            ],
            // outages of an AI provider (I17): when, why, how many calls the backup made
            'df_mailmanager_aioutage' => [
                'id' => 'outageid',
                'columns' => [
                    'provider' => 'VARCHAR(20) NOT NULL',
                    'backup_provider' => 'VARCHAR(20) DEFAULT NULL',
                    'started_at' => 'DATETIME NOT NULL',
                    'ended_at' => 'DATETIME DEFAULT NULL',
                    'reason' => 'VARCHAR(20) DEFAULT NULL',
                    'last_error' => 'VARCHAR(255) DEFAULT NULL',
                    'failures' => 'INT NOT NULL DEFAULT 0',
                    'backup_calls' => 'INT NOT NULL DEFAULT 0',
                    'notified_start' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'notified_end' => 'TINYINT(1) NOT NULL DEFAULT 0',
                ],
                'keys' => ['KEY idx_provider_start (provider, started_at)'],
            ],
            self::PRO_MARKER_TABLE => [
                'columns' => [
                    'name' => 'VARCHAR(50) NOT NULL',
                    'value' => 'TEXT DEFAULT NULL',
                ],
                'keys' => ['PRIMARY KEY (name)'],
            ],
        ];
    }
}
