<?php

class ITS4YouEmails_Install_Model extends Vtiger_Install_Model
{

    protected string $moduleName = 'ITS4YouEmails';
    protected string $moduleNumbering = 'MAIL';
    protected string $parentName = 'Tools';

    /**
     * @var array
     * [Module, RelatedModule, RelatedLabel, RelatedActions, RelatedFunction]
     */
    public array $registerRelatedLists = array(
        ['ITS4YouEmails', 'Documents', 'Documents', 'ADD,SELECT', 'get_attachments'],
    );

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = array(
        ['ITS4YouEmails', 'HEADERSCRIPT', 'ITS4YouEmails_HS_Js', 'layouts/$LAYOUT$/modules/ITS4YouEmails/resources/ITS4YouEmails_HS.js'],
        ['ITS4YouEmails', 'HEADERSCRIPT', 'ITS4YouEmails_MassEdit_Js', 'layouts/$LAYOUT$/modules/ITS4YouEmails/resources/MassEdit.js'],
    );

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateNumbering();
        $this->updateCustomLinks();
        $this->updateRelatedToModules();

        $this->retrieveRelatedList();
        $this->updateRelatedList();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);

        ModComments::addWidgetTo([$this->moduleName]);
        ModTracker::enableTrackingForModule(getTabid($this->moduleName));
    }

    public function updateRelatedToModules(): void
    {
        $supportedModules = ITS4YouEmails_Integration_Model::getSupportedModules();
        $moduleInstance = Vtiger_Module_Model::getInstance($this->moduleName);
        $fieldInstance = Vtiger_Field_Model::getInstance('related_to', $moduleInstance);

        if ($fieldInstance) {
            foreach ($supportedModules as $supportedModule) {
                $fieldInstance->setRelatedModules([$supportedModule->getName()]);
            }
        }
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);

        $this->retrieveRelatedList();
        $this->updateRelatedList(false);

        ModComments::removeWidgetFrom([$this->moduleName]);
        ModTracker::disableTrackingForModule(getTabid($this->moduleName));
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_BASIC_INFORMATION' => [
                'subject' => [
                    'uitype' => 2,
                    'column' => 'subject',
                    'label' => 'Subject',
                    'typeofdata' => 'V~M',
                    'masseditable' => '0',
                    'summaryfield' => '1',
                    'entity_identifier' => '1',
                    'filter' => '1',
                ],
                'email_flag' => [
                    'uitype' => 16,
                    'column' => 'email_flag',
                    'label' => 'Email Flag',
                    'masseditable' => '0',
                    'picklist_values' => [
                        'SENT',
                        'SAVED',
                        'ERROR',
                        'UNSUBSCRIBED',
                    ],
                    'filter' => '1',
                ],
                'createdtime' => [
                    'uitype' => 70,
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => '2',
                    'masseditable' => '0',
                    'filter' => '1',
                ],
                'source' => [
                    'uitype' => 1,
                    'column' => 'source',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Source',
                    'displaytype' => '2',
                    'masseditable' => '0',
                    'summaryfield' => '1',
                    'filter' => '1',
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'typeofdata' => 'V~M',
                    'masseditable' => '0',
                    'summaryfield' => '1',
                    'filter' => '1',
                ],
                'access_count' => [
                    'uitype' => 1,
                    'column' => 'access_count',
                    'label' => 'Access count',
                    'typeofdata' => 'I~O',
                    'masseditable' => '0',
                    'filter' => '1',
                ],
                'click_count' => [
                    'uitype' => 1,
                    'column' => 'click_count',
                    'label' => 'Click count',
                    'typeofdata' => 'I~O',
                    'masseditable' => '0',
                ],
                'result' => [
                    'uitype' => 19,
                    'column' => 'result',
                    'label' => 'Email result',
                    'masseditable' => '0',
                ],
            ],
            'LBL_RELATED_TO' => [
                'related_to' => [
                    'uitype' => 10,
                    'column' => 'related_to',
                    'label' => 'Related Record',
                    'masseditable' => '0',
                    'summaryfield' => '1',
                    'related_modules' => [
                    ],
                    'filter' => '1',
                ],
                'account_id' => [
                    'uitype' => 10,
                    'column' => 'account_id',
                    'label' => 'Account Name',
                    'masseditable' => '0',
                    'related_modules' => [
                        'Accounts',
                    ],
                    'filter' => '1',
                ],
                'contact_id' => [
                    'uitype' => 10,
                    'column' => 'contact_id',
                    'label' => 'Contact Name',
                    'masseditable' => '0',
                    'related_modules' => [
                        'Contacts',
                    ],
                    'filter' => '1',
                ],
                'vendor_id' => [
                    'uitype' => 10,
                    'column' => 'vendor_id',
                    'label' => 'Vendor Name',
                    'masseditable' => '0',
                    'related_modules' => [
                        'Vendors',
                    ],
                ],
                'user_id' => [
                    'uitype' => 52,
                    'column' => 'user_id',
                    'label' => 'User Name',
                    'masseditable' => '0',
                ],
                'lead_id' => [
                    'uitype' => 10,
                    'column' => 'lead_id',
                    'label' => 'Lead Name',
                    'masseditable' => '0',
                    'related_modules' => [
                        'Leads',
                    ],
                ],
                'recipient_id' => [
                    'uitype' => 1,
                    'column' => 'recipient_id',
                    'label' => 'Marketing Recipient',
                    'masseditable' => '0',
                ],

            ],
            'LBL_BODY_INFORMATION' => [
                'body' => [
                    'uitype' => 19,
                    'column' => 'body',
                    'label' => 'Body',
                    'masseditable' => '0',
                ],
            ],
            'LBL_TEMPLATE_INFORMATION' => [
                'email_template_ids' => [
                    'uitype' => 1,
                    'column' => 'email_template_ids',
                    'label' => 'Email Template Ids',
                    'masseditable' => '0',
                ],
                'email_template_language' => [
                    'uitype' => 1,
                    'column' => 'email_template_language',
                    'label' => 'Email Template Language',
                    'masseditable' => '0',

                ],
                'pdf_template_ids' => [
                    'uitype' => 1,
                    'column' => 'pdf_template_ids',
                    'label' => 'PDF Template Ids',
                    'masseditable' => '0',
                ],
                'pdf_template_language' => [
                    'uitype' => 1,
                    'column' => 'pdf_template_language',
                    'label' => 'PDF Template Language',
                    'masseditable' => '0',
                ],
                'is_merge_templates' => [
                    'uitype' => 56,
                    'column' => 'is_merge_templates',
                    'label' => 'Is Merge Templates',
                    'typeofdata' => 'C~O',
                    'masseditable' => '0',
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
                'from_email' => [
                    'uitype' => 12,
                    'column' => 'from_email',
                    'label' => 'From Email',
                    'typeofdata' => 'V~M',
                    'masseditable' => '0',
                ],
                'from_email_ids' => [
                    'uitype' => 1,
                    'column' => 'from_email_ids',
                    'label' => 'From Email Id',
                    'masseditable' => '0',
                ],
                'reply_email' => [
                    'uitype' => 1,
                    'column' => 'reply_email',
                    'label' => 'Reply To Email',
                    'masseditable' => '0',
                ],
                'reply_email_ids' => [
                    'uitype' => 1,
                    'column' => 'reply_email_ids',
                    'label' => 'Reply Email Id',
                    'masseditable' => '0',
                ],
                'to_email' => [
                    'uitype' => 8,
                    'column' => 'to_email',
                    'label' => 'To Emails',
                    'typeofdata' => 'V~M',
                    'masseditable' => '0',
                ],
                'to_email_ids' => [
                    'uitype' => 8,
                    'column' => 'to_email_ids',
                    'label' => 'To Emails Ids',
                    'masseditable' => '0',
                ],
                'cc_email' => [
                    'uitype' => 8,
                    'column' => 'cc_email',
                    'label' => 'CC Emails',
                    'masseditable' => '0',
                ],
                'cc_email_ids' => [
                    'uitype' => 8,
                    'column' => 'cc_email_ids',
                    'label' => 'CC Emails Ids',
                    'masseditable' => '0',
                ],
                'bcc_email' => [
                    'uitype' => 8,
                    'column' => 'bcc_email',
                    'label' => 'BCC Emails',
                    'masseditable' => '0',
                ],
                'bcc_email_ids' => [
                    'uitype' => 8,
                    'column' => 'bcc_email_ids',
                    'label' => 'BCC Emails Ids',
                    'masseditable' => '0',
                ],
                'smtp' => [
                    'uitype' => 1,
                    'column' => 'smtp',
                    'label' => 'SMTP',
                    'masseditable' => '0',
                ],
                'its4you_email_no' => [
                    'uitype' => 4,
                    'column' => 'its4you_email_no',
                    'label' => 'Email No',
                    'masseditable' => '0',
                ],
                'sending_id' => [
                    'uitype' => 1,
                    'column' => 'sending_id',
                    'label' => 'Sending Id',
                    'masseditable' => '0',
                ],
                'workflow_id' => [
                    'uitype' => 1,
                    'column' => 'workflow_id',
                    'label' => 'Workflow',
                    'masseditable' => '0',
                ],
                'smcreatorid' => [
                    'uitype' => 52,
                    'column' => 'smcreatorid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Creator',
                    'displaytype' => '2',
                    'masseditable' => '0',
                ],
                'parent_id' => [
                    'uitype' => 10,
                    'column' => 'parent_id',
                    'label' => 'Parent Id',
                    'masseditable' => '0',
                    'related_modules' => [
                        'ITS4YouEmails',
                    ],
                ],
                'modifiedby' => [
                    'uitype' => 52,
                    'column' => 'modifiedby',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Last Modified By',
                    'displaytype' => '2',
                    'masseditable' => '0',
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'typeofdata' => 'DT~O',
                    'displaytype' => '2',
                    'masseditable' => '0',
                ],
                'attachment_ids' => [
                    'uitype' => 1,
                    'column' => 'attachment_ids',
                    'label' => 'Attachment Ids',
                    'masseditable' => '0',
                ],
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'masseditable' => '0',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'its4you_emails',
            'its4you_emailscf',
            'its4you_emails_access',
            'its4you_emails_sending_seq',
            'vtiger_its4youemails_user_field',
        ];
    }

    /**
     * @return void
     */
    public function installTables(): void
    {
        $this->getTable('its4you_emails', null)
            ->createTable('its4you_emails_id','int(19) NOT NULL')
            ->createColumn('from_email','varchar(50) DEFAULT NULL')
            ->createColumn('from_email_ids','varchar(200) DEFAULT NULL')
            ->createColumn('reply_email','varchar(50) DEFAULT NULL')
            ->createColumn('reply_email_ids','varchar(200) DEFAULT NULL')
            ->createColumn('to_email','text DEFAULT NULL')
            ->createColumn('to_email_ids','text DEFAULT NULL')
            ->createColumn('cc_email','text DEFAULT NULL')
            ->createColumn('cc_email_ids','text DEFAULT NULL')
            ->createColumn('bcc_email','text DEFAULT NULL')
            ->createColumn('bcc_email_ids','text DEFAULT NULL')
            ->createColumn('subject','varchar(255) DEFAULT NULL')
            ->createColumn('result','text DEFAULT NULL')
            ->createColumn('email_flag','varchar(50) DEFAULT NULL')
            ->createColumn('related_to','int(11) DEFAULT NULL')
            ->createColumn('parent_id','int(11) DEFAULT NULL')
            ->createColumn('sending_id','int(11) DEFAULT NULL')
            ->createColumn('attachment_ids','text DEFAULT NULL')
            ->createColumn('is_merge_templates','tinyint(4) DEFAULT NULL')
            ->createColumn('body','longtext DEFAULT NULL')
            ->createColumn('email_template_ids','varchar(200) DEFAULT NULL')
            ->createColumn('email_template_language','varchar(50) DEFAULT NULL')
            ->createColumn('pdf_template_ids','varchar(200) DEFAULT NULL')
            ->createColumn('pdf_template_language','varchar(50) DEFAULT NULL')
            ->createColumn('smtp','int(11) DEFAULT NULL')
            ->createColumn('access_count','int(11) DEFAULT NULL')
            ->createColumn('click_count','int(11) DEFAULT NULL')
            ->createColumn('its4you_email_no','varchar(100) DEFAULT NULL')
            ->createColumn('emails_module','varchar(100) DEFAULT NULL')
            ->createColumn('account_id','int(11) DEFAULT NULL')
            ->createColumn('contact_id','int(11) DEFAULT NULL')
            ->createColumn('lead_id','int(11) DEFAULT NULL')
            ->createColumn('vendor_id','int(11) DEFAULT NULL')
            ->createColumn('user_id','int(11) DEFAULT NULL')
            ->createColumn('workflow_id','varchar(100) DEFAULT NULL')
            ->createColumn('tags','varchar(1) DEFAULT NULL')
            ->createColumn('recipient_id','int(11) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`its4you_emails_id`)');

        $this->getTable('its4you_emailscf', null)
            ->createTable('its4you_emails_id','int(19) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`its4you_emails_id`)');

        $this->getTable('vtiger_its4youemails_user_field', null)
            ->createTable('recordid','int(25) NOT NULL')
            ->createColumn('userid','int(25) NOT NULL')
            ->createColumn('starred','varchar(100) DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `recordid` (`recordid`)');

        $this->getTable('its4you_emails_access', null)
            ->createTable('mail_id','int(25) NOT NULL')
            ->createColumn('record_id','int(25) NOT NULL')
            ->createColumn('access_id','varchar(50) DEFAULT NULL')
            ->createColumn('access_time','datetime DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `record_id` (`record_id`)');
    }

    public function retrieveRelatedList(): void
    {
        $supportedModules = ITS4YouEmails_Integration_Model::getSupportedModules();

        foreach ($supportedModules as $supportedModule) {
            $supportedModuleName = $supportedModule->getName();

            $this->registerRelatedLists[] = [$supportedModuleName, 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list'];
        }
    }
}