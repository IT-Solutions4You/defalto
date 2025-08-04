<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PBXManager_Install_Model extends Core_Install_Model
{

    public function addCustomLinks(): void
    {
        // TODO: Implement addCustomLinks() method.
    }

    public function deleteCustomLinks(): void
    {
        // TODO: Implement deleteCustomLinks() method.
    }

    public function getBlocks(): array
    {
        return [
            'LBL_PBXMANAGER_INFORMATION' => [
                'direction' => [
                    'uitype' => 1,
                    'column' => 'direction',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Direction',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'callstatus' => [
                    'uitype' => 1,
                    'column' => 'callstatus',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Call Status',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'starttime' => [
                    'uitype' => 70,
                    'column' => 'starttime',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Start Time',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'endtime' => [
                    'name' => 'endtime',
                    'uitype' => 70,
                    'column' => 'endtime',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'End Time',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'totalduration' => [
                    'uitype' => 7,
                    'column' => 'totalduration',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Total Duration',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'filter' => 1,
                ],
                'billduration' => [
                    'uitype' => 7,
                    'column' => 'billduration',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Bill Duration',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'I~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'recordingurl' => [
                    'uitype' => 17,
                    'column' => 'recordingurl',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Recording URL',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'filter' => 1,
                ],
                'sourceuuid' => [
                    'uitype' => 1,
                    'column' => 'sourceuuid',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Source UUID',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'gateway' => [
                    'uitype' => 1,
                    'column' => 'gateway',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Gateway',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'customer' => [
                    'uitype' => 10,
                    'column' => 'customer',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Customer',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'related_modules' => [
                        'Leads',
                        'Contacts',
                        'Accounts',
                    ],
                    'filter' => 1,
                ],
                'user' => [
                    'uitype' => 52,
                    'column' => 'user',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'User',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'customernumber' => [
                    'uitype' => 11,
                    'column' => 'customernumber',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Customer Number',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'entity_identifier' => 1,
                ],
                'customertype' => [
                    'uitype' => 1,
                    'column' => 'customertype',
                    'table' => 'vtiger_pbxmanager',
                    'label' => 'Customer Type',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'assigned_user_id' => [
                    'uitype' => 53,
                    'column' => 'assigned_user_id',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                ],
                'createdtime' => [
                    'uitype' => 70,
                    'name' => 'createdtime',
                    'column' => 'createdtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Created Time',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 1,
                ],
                'modifiedtime' => [
                    'uitype' => 70,
                    'name' => 'modifiedtime',
                    'column' => 'modifiedtime',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Modified Time',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'DT~O',
                    'quickcreate' => 1,
                    'displaytype' => 2,
                    'masseditable' => 1,
                ],
            ],
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_pbxmanager',
            'vtiger_pbxmanagercf',
            'vtiger_pbxmanager_gateway',
            'vtiger_pbxmanager_phonelookup',
        ];
    }

    public function installTables(): void
    {
        $this->getTable('vtiger_pbxmanager', 'pbxmanagerid')
            ->createTable()
            ->createColumn('direction', 'varchar(10) DEFAULT NULL')
            ->createColumn('callstatus', 'varchar(20) DEFAULT NULL')
            ->createColumn('starttime', 'datetime DEFAULT NULL')
            ->createColumn('endtime', 'datetime DEFAULT NULL')
            ->createColumn('totalduration', 'int(11) DEFAULT NULL')
            ->createColumn('billduration', 'int(11) DEFAULT NULL')
            ->createColumn('recordingurl', 'varchar(200) DEFAULT NULL')
            ->createColumn('sourceuuid', 'varchar(100) DEFAULT NULL')
            ->createColumn('gateway', 'varchar(20) DEFAULT NULL')
            ->createColumn('customer', 'varchar(100) DEFAULT NULL')
            ->createColumn('user', 'varchar(100) DEFAULT NULL')
            ->createColumn('customernumber', 'varchar(100) DEFAULT NULL')
            ->createColumn('customertype', 'varchar(100) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`pbxmanagerid`)')
            ->createKey('KEY IF NOT EXISTS `index_sourceuuid` (`sourceuuid`)')
            ->createKey('KEY IF NOT EXISTS `index_pbxmanager_id` (`pbxmanagerid`)');

        $this->getTable('vtiger_pbxmanagercf', null)
            ->createTable('pbxmanagerid')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`pbxmanagerid`)');

        $this->getTable('vtiger_pbxmanager_gateway', 'id')
            ->createTable()
            ->createColumn('gateway', 'varchar(20) DEFAULT NULL')
            ->createColumn('parameters', 'text')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');

        $this->getTable('vtiger_pbxmanager_phonelookup', null)
            ->createTable('crmid', 'int(20) DEFAULT NULL')
            ->createColumn('setype', 'varchar(30) DEFAULT NULL')
            ->createColumn('fnumber', 'varchar(100) DEFAULT NULL')
            ->createColumn('rnumber', 'varchar(100) DEFAULT NULL')
            ->createColumn('fieldname', 'varchar(50) DEFAULT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `unique_key` (`crmid`,`setype`,`fieldname`)')
            ->createKey('KEY IF NOT EXISTS `index_phone_number` (`fnumber`,`rnumber`)')
            ->createKey('CONSTRAINT `vtiger_pbxmanager_phonelookup_ibfk_1` FOREIGN KEY IF NOT EXISTS (`crmid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');
    }
}