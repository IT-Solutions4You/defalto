<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SMSNotifier_Install_Model extends Core_Install_Model
{
    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        ['SMSNotifier', 'HEADERSCRIPT', 'SMSNotifierCommonJS', 'modules/SMSNotifier/SMSNotifierCommon.js'],
        ['Leads', 'LISTVIEWBASIC', 'Send SMS', 'SMSNotifierCommon.displaySelectWizard(this, \'$MODULE$\');'],
        ['Leads', 'DETAILVIEW', 'Send SMS', 'javascript:SMSNotifierCommon.displaySelectWizard_DetailView(\'$MODULE$\', \'$RECORD$\');'],
        ['Contacts', 'LISTVIEWBASIC', 'Send SMS', 'SMSNotifierCommon.displaySelectWizard(this, \'$MODULE$\');'],
        ['Contacts', 'DETAILVIEW', 'Send SMS', 'javascript:SMSNotifierCommon.displaySelectWizard_DetailView(\'$MODULE$\', \'$RECORD$\');'],
        ['Accounts', 'LISTVIEWBASIC', 'Send SMS', 'SMSNotifierCommon.displaySelectWizard(this, \'$MODULE$\');'],
        ['Accounts', 'DETAILVIEW', 'Send SMS', 'javascript:SMSNotifierCommon.displaySelectWizard_DetailView(\'$MODULE$\', \'$RECORD$\');'],
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateCustomLinks();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateCustomLinks(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_SMSNOTIFIER_INFORMATION' => [
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'message' => [
                    'uitype' => 21,
                    'column' => 'message',
                    'table' => 'vtiger_smsnotifier',
                    'label' => 'message',
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'entity_identifier' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'StatusInformation' => [
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'vtiger_smsnotifier',
            'vtiger_smsnotifiercf',
            'vtiger_smsnotifier_servers',
            'vtiger_smsnotifier_status',
        ];
    }

    /**
     * @return void
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_smsnotifier', null)
            ->createTable('smsnotifierid')
            ->createColumn('message', 'text')
            ->createColumn('status', 'varchar(100) DEFAULT NULL');

        $this->getTable('vtiger_smsnotifiercf', null)
            ->createTable('smsnotifierid');

        $this->getTable('vtiger_smsnotifier_servers', 'id')
            ->createTable()
            ->createColumn('password', 'varchar(255) DEFAULT NULL')
            ->createColumn('isactive', 'int(1) DEFAULT NULL')
            ->createColumn('providertype', 'varchar(50) DEFAULT NULL')
            ->createColumn('username', 'varchar(255) DEFAULT NULL')
            ->createColumn('parameters', 'text')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');

        $this->getTable('vtiger_smsnotifier_status', null)
            ->createTable('smsnotifierid')
            ->createColumn('tonumber', 'varchar(20) DEFAULT NULL')
            ->createColumn('status', 'varchar(10) DEFAULT NULL')
            ->createColumn('smsmessageid', 'varchar(50) DEFAULT NULL')
            ->createColumn('needlookup', 'int(1) DEFAULT \'1\'')
            ->createColumn('statusid', 'int(11) NOT NULL')
            ->createColumn('statusmessage', 'varchar(100) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`statusid`)');
    }
}

