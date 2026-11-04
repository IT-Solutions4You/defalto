<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = array(
        array('EMAILMaker - Birthday email', 'modules/EMAILMaker/cron/BirthdayEmail.php', 86400, 'EMAILMaker', 0, ''),
    );
    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = array(
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerJS', 'layouts/$LAYOUT$/modules/EMAILMaker/resources/EMAILMakerActions.js'],
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerMassEditJS', ''],
    );
    /**
     * @var array|array[]
     */
    public array $registerWorkflows = array(
        ['EMAILMaker', 'VTEMAILMakerMailTask', 'Send Email from EMAIL Maker'],
    );

    /**
     * @var string
     */
    protected string $moduleName = 'EMAILMaker';
    protected string $parentName = 'Tools';

    /**
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->insertEmailTemplates();

        $this->retrieveCustomLinks();
        $this->updateCustomLinks();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->retrieveCustomLinks();
        $this->updateCustomLinks(false);
        $this->updateWorkflows(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'vtiger_emakertemplates',
            'vtiger_emakertemplates_emails',
            'vtiger_emakertemplates_sent',
            'vtiger_emakertemplates_settings',
            'vtiger_emakertemplates_ignorepicklistvalues',
            'vtiger_emakertemplates_profilespermissions',
            'vtiger_emakertemplates_sharing',
            'vtiger_emakertemplates_default_from',
            'vtiger_emakertemplates_documents',
            'vtiger_emakertemplates_userstatus',
            'vtiger_emakertemplates_label_keys',
            'vtiger_emakertemplates_label_vals',
            'vtiger_emakertemplates_images',
            'vtiger_emakertemplates_displayed',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function install(): void
    {
        switch ($this->eventType) {
            case 'module.postinstall':
                $this->updateCron();
                $this->addCustomLinks();
                break;
            case 'module.enabled':
                $this->updateProfilePermissions();
                $this->addCustomLinks();
                $this->updateCron();
                break;
            case 'module.postupdate':
                $this->updateProfilePermissions();
                $this->addCustomLinks();
                break;
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
            case 'module.disabled':
                $this->deleteCustomLinks();
                $this->updateCron(false);
                break;
            case 'module.preuninstall':
                $this->updateCron(false);
                $this->updateWorkflows(false);
                $this->deleteCustomLinks();
                break;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_emakertemplates', 'templateid')
            ->createTable()
            ->createColumn('foldername', 'varchar(100) DEFAULT NULL')
            ->createColumn('templatename', 'varchar(100) DEFAULT NULL')
            ->createColumn('subject', 'varchar(255) DEFAULT NULL')
            ->createColumn('description', 'longtext DEFAULT NULL')
            ->createColumn('body', 'longtext DEFAULT NULL')
            ->createColumn('deleted', 'int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('module', 'varchar(255) DEFAULT NULL')
            ->createColumn('owner', 'int(11) NOT NULL DEFAULT \'1\'')
            ->createColumn('sharingtype', 'char(7) NOT NULL DEFAULT \'public\'')
            ->createColumn('category', 'varchar(255) DEFAULT NULL')
            ->createColumn('is_listview', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('is_theme', 'int(1) DEFAULT \'0\'')
            ->createColumn('load_related_documents', 'TINYINT(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('folders_related_documents', 'VARCHAR(255) NULL')
            ->createKey('KEY IF NOT EXISTS `emakertemplates_foldernamd_templatename_subject_idx` (`foldername`,`templatename`,`subject`)')
            ->createKey('KEY IF NOT EXISTS `deleted` (`deleted`)')
            ->createKey('KEY IF NOT EXISTS `is_listview` (`is_listview`)')
            ->createKey('KEY IF NOT EXISTS `is_theme` (`is_theme`)')
        ;
        $this->getTable('vtiger_emakertemplates_emails', 'emailid')
            ->createTable()
            ->createColumn('esentid', 'int(11) NOT NULL')
            ->createColumn('pid', 'int(11) DEFAULT NULL')
            ->createColumn('email', 'varchar(255) NOT NULL')
            ->createColumn('email_address', 'varchar(250) DEFAULT NULL')
            ->createColumn('cc', 'longtext DEFAULT NULL')
            ->createColumn('bcc', 'longtext DEFAULT NULL')
            ->createColumn('status', 'int(2) NOT NULL DEFAULT \'0\'')
            ->createColumn('parent_id', 'int(11) DEFAULT NULL')
            ->createColumn('error', 'text DEFAULT NULL')
            ->createColumn('cc_ids', 'text DEFAULT NULL')
            ->createColumn('bcc_ids', 'text DEFAULT NULL')
            ->createColumn('email_send_date', 'datetime DEFAULT NULL')
            ->createColumn('deleted', 'int(11) DEFAULT \'0\'')
            ->createKey('KEY IF NOT EXISTS `esentid` (`esentid`)')
            ->createKey('KEY IF NOT EXISTS `pid` (`pid`)')
            ->createKey('KEY IF NOT EXISTS `status` (`status`)')
            ->createKey('KEY IF NOT EXISTS `deleted` (`deleted`)')
            ->createKey('KEY IF NOT EXISTS `parent_id` (`parent_id`)')
            ->createKey('KEY IF NOT EXISTS `esentid_2` (`esentid`,`status`,`deleted`)');

        $this->getTable('vtiger_emakertemplates_sent', 'esentid')
            ->createTable()
            ->createColumn('from_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('from_email', 'varchar(255) DEFAULT NULL')
            ->createColumn('subject', 'varchar(255) DEFAULT NULL')
            ->createColumn('body', 'longtext DEFAULT NULL')
            ->createColumn('type', 'int(5) NOT NULL')
            ->createColumn('pdf_template_ids', 'varchar(255) DEFAULT NULL')
            ->createColumn('pdf_language', 'varchar(255) DEFAULT NULL')
            ->createColumn('total_emails', 'int(11) DEFAULT \'0\'')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('attachments', 'int(2) DEFAULT \'0\'')
            ->createColumn('att_documents', 'text DEFAULT NULL')
            ->createColumn('send_date', 'datetime DEFAULT NULL')
            ->createColumn('drip_group', 'int(11) DEFAULT NULL')
            ->createColumn('drip_delay', 'decimal(11,0) DEFAULT NULL')
            ->createColumn('total_sent_emails', 'decimal(11,0) DEFAULT \'0\'')
            ->createColumn('saved_drip_delay', 'decimal(11,0) DEFAULT NULL')
            ->createColumn('related_to', 'int(11) DEFAULT NULL')
            ->createColumn('ids_for_pdf', 'text DEFAULT NULL')
            ->createColumn('pmodule', 'varchar(255) DEFAULT NULL')
            ->createColumn('language', 'varchar(255) DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `userid` (`userid`)')
            ->createKey('KEY IF NOT EXISTS `related_to` (`related_to`)');

        $this->getTable('vtiger_emakertemplates_settings', null)
            ->createTable('decimals', 'tinyint(2) NOT NULL')
            ->createColumn('decimal_point', 'char(2) NOT NULL')
            ->createColumn('thousands_separator', 'char(2) NOT NULL')
            ->createColumn('phpmailer_version', 'varchar(50) DEFAULT \'emailmaker\'');

        $this->getTable('vtiger_emakertemplates_ignorepicklistvalues', null)
            ->createTable('value', 'varchar(100) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`value`)');

        $this->getTable('vtiger_emakertemplates_profilespermissions', null)
            ->createTable('profileid', 'int(11) NOT NULL')
            ->createColumn('operation', 'int(11) NOT NULL')
            ->createColumn('permissions', 'int(1) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`profileid`,`operation`)');

        $this->getTable('vtiger_emakertemplates_sharing', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('shareid', 'varchar(10) NOT NULL')
            ->createColumn('setype', 'varchar(200) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`templateid`,`shareid`,`setype`)');

        $this->getTable('vtiger_emakertemplates_default_from', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('fieldname', 'varchar(255) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `templateid` (`templateid`,`userid`)');

        $this->getTable('vtiger_emakertemplates_documents', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('documentid', 'int(11) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `templateid_2` (`templateid`,`documentid`)')
            ->createKey('KEY IF NOT EXISTS `templateid` (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `documentid` (`documentid`)');

        $this->getTable('vtiger_emakertemplates_userstatus', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('is_active', 'tinyint(1) NOT NULL')
            ->createColumn('is_default', 'tinyint(1) NOT NULL')
            ->createColumn('sequence', 'int(6) NOT NULL DEFAULT \'1\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`,`userid`)');

        $this->getTable('vtiger_emakertemplates_label_keys', 'label_id')
            ->createTable()
            ->createColumn('label_key', 'varchar(128) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `label_key` (`label_key`)');

        $this->getTable('vtiger_emakertemplates_label_vals', null)
            ->createTable('label_id', 'int(11) NOT NULL')
            ->createColumn('lang_id', 'int(11) NOT NULL')
            ->createColumn('label_value', 'varchar(1024) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`label_id`,`lang_id`)');

        $this->getTable('vtiger_emakertemplates_images', null)
            ->createTable('crmid', 'int(11) NOT NULL')
            ->createColumn('productid', 'int(11) NOT NULL')
            ->createColumn('sequence', 'int(11) NOT NULL')
            ->createColumn('attachmentid', 'int(11) NOT NULL')
            ->createColumn('width', 'int(11) DEFAULT NULL')
            ->createColumn('height', 'int(11) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`crmid`,`productid`,`sequence`)');

        $this->getTable('vtiger_emakertemplates_displayed', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('displayed', 'int(11) NOT NULL')
            ->createColumn('conditions', 'longtext NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `displayed` (`displayed`)');
    }

    /**
     * Removes tables whose EMAIL Maker runtime owners were removed or moved to shared Core storage.
     *
     * @return void
     */
    public function migrate(): void
    {
        $tables = [
            'vtiger_emakertemplates_attch',
            'vtiger_emakertemplates_contents',
            'vtiger_emakertemplates_delay',
            'vtiger_emakertemplates_drip_groups',
            'vtiger_emakertemplates_drip_groups_seq',
            'vtiger_emakertemplates_drip_tpls',
            'vtiger_emakertemplates_drip_tpls_seq',
            'vtiger_emakertemplates_drips',
            'vtiger_emakertemplates_drips_seq',
            'vtiger_emakertemplates_license',
            'vtiger_emakertemplates_me',
            'vtiger_emakertemplates_picklists',
            'vtiger_emakertemplates_productbloc_tpl',
            'vtiger_emakertemplates_relblockcol',
            'vtiger_emakertemplates_relblockcriteria',
            'vtiger_emakertemplates_relblockcriteria_g',
            'vtiger_emakertemplates_relblockdatefilter',
            'vtiger_emakertemplates_relblocks',
            'vtiger_emakertemplates_relblocksortcol',
            'vtiger_emakertemplates_relblocks_seq',
            'vtiger_emakertemplates_seq',
            'vtiger_emakertemplates_sharing_drip',
            'vtiger_emakertemplates_version',
        ];

        foreach ($tables as $table) {
            $this->getTable($table, null)->dropTable();
        }
    }

    public function getTemplateModules(): array
    {
        $emptyModuleName = false;
        $modules = [];
        $result = $this->db->pquery('SELECT module FROM vtiger_emakertemplates WHERE deleted=? GROUP BY module', array('0'));

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!empty($row['module'])) {
                $modules[] = $row['module'];
            } else {
                $emptyModuleName = true;
            }
        }

        if ($emptyModuleName) {
            $entityModules = Vtiger_Module_Model::getEntityModules();

            foreach ($entityModules as $entityModule) {
                $module = $entityModule->getName();

                if ($entityModule->isEntityModule() && $entityModule->isActive()) {
                    $modules[] = $module;
                }
            }
        }

        return $modules;
    }

    /**
     * @return void
     */
    public function retrieveCustomLinks(): void
    {
        $entityModules = Vtiger_Module_Model::getEntityModules();
        $modules = [];

        foreach ($entityModules as $entityModule) {
            $module = $entityModule->getName();

            if ($entityModule->isEntityModule() && $entityModule->isActive()) {
                $modules[] = $module;
            }
        }

        foreach ($modules as $module) {
            $this->registerCustomLinks[] = array(
                $module,
                'DETAILVIEWBASIC',
                'Send Email',
                'javascript:EMAILMaker_Actions_Js.getDetailViewPopup(this,\'$MODULE$\');',
                '<i class="fa fa-paper-plane" aria-hidden="true"></i>',
            );

            $this->registerCustomLinks[] = array(
                $module,
                'LISTVIEWMASSACTION',
                'Send Email',
                'javascript:EMAILMaker_Actions_Js.getListViewPopup(this,\'$MODULE$\');',
                '<i class="fa fa-paper-plane" aria-hidden="true"></i>',
            );
        }
    }

    /**
     * @return void
     */
    public function updateProfilePermissions(): void
    {
        $vtigerResult = $this->db->pquery(
            'SELECT tabid FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = ?)',
            array('EMAILMaker')
        );

        if ($this->db->num_rows($vtigerResult)) {
            $emailMakerResult = $this->db->query('SELECT * FROM vtiger_emakertemplates_profilespermissions');

            if (!$this->db->num_rows($emailMakerResult)) {
                $this->db->pquery(
                    'INSERT INTO vtiger_emakertemplates_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                    array('EMAILMaker')
                );
            }

            $this->db->pquery(
                'DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                array('EMAILMaker')
            );
        }
    }

    public function insertEmailTemplates()
    {
        $userId = Users::getActiveAdminId();
        $templates = [
            'Reminder' => [
                'templatename' => 'Reminder',
                'module' => 'Appointments',
                'description' => 'Reminder',
                'subject' => 'Reminder',
                'body' => 'This is a reminder notification for the Activity',
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Invitation' => [
                'templatename' => 'Invitation',
                'module' => 'Appointments',
                'description' => 'Invitation',
                'subject' => 'Invitation',
                'body' => 'Invitation',
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Customer Login Details' => [
                'templatename' => 'Customer Login Details',
                'module' => 'Contacts',
                'description' => 'Customer Portal Login Details',
                'subject' => 'Customer Portal Login Details',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/CustomerLoginDetails.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Support end notification before a month' => [
                'templatename' => 'Support end notification before a month',
                'module' => 'Contacts',
                'description' => 'Send Notification mail to customer before a month of support end date',
                'subject' => 'VtigerCRM Support Notification',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/SupportNotificationMonth.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Support end notification before a week' => [
                'templatename' => 'Support end notification before a week',
                'module' => 'Contacts',
                'description' => 'Send Notification mail to customer before a week of support end date',
                'subject' => 'VtigerCRM Support Notification',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/SupportNotificationWeek.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
        ];

        if (!empty($this->db->getColumnNames('vtiger_emailtemplates'))) {
            $result = $this->db->pquery('SELECT * FROM vtiger_emailtemplates');

            while ($row = $this->db->fetchByAssoc($result)) {
                $templateName = trim($row['templatename']);

                if (empty($templates[$templateName])) {
                    $templates[$templateName] = [
                        'templatename' => $templateName,
                        'module' => $row['module'],
                        'description' => $templateName,
                        'subject' => $row['subject'],
                        'body' => decode_html($row['body']),
                        'owner' => $userId,
                        'sharingtype' => 'private',
                        'category' => 'email templates',
                        'is_listview' => 0,
                    ];
                }
            }
        }

        foreach ($templates as $template) {
            $sql = 'SELECT templatename FROM vtiger_emakertemplates WHERE templatename=? AND deleted=?';
            $params = [$template['templatename'], '0'];

            if (!empty($template['module'])) {
                $sql .= ' AND module=?';
                $params[] = $template['module'];
            }

            $result = $this->db->pquery($sql, $params);

            if (!$this->db->num_rows($result)) {
                EMAILMaker_Record_Model::saveTemplate($template, 0);
            }
        }
    }
}
