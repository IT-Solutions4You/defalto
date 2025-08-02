<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PriceBooks_Install_Model extends Core_Install_Model
{
    public array $registerRelatedLists = [
        ['Products', 'PriceBooks', 'PriceBooks', 'ADD,SELECT', 'get_product_pricebooks',],
        ['PriceBooks', 'Products', 'Products', 'select', 'get_pricebook_products',],
        ['Services', 'PriceBooks', 'PriceBooks', 'ADD', 'get_service_pricebooks',],
        ['PriceBooks', 'Services', 'Services', 'SELECT', 'get_pricebook_services',],
        ['PriceBooks', 'Appointments', 'Appointments', '', 'get_related_list',],
        ['PriceBooks', 'ITS4YouEmails', 'ITS4YouEmails', 'SELECT', 'get_related_list',],
    ];

    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateRelatedList();
    }

    public function deleteCustomLinks(): void
    {
        $this->updateRelatedList(false);
    }

    public function getBlocks(): array
    {
        return [
            'LBL_PRICEBOOK_INFORMATION' => [
                'bookname' => [
                    'name' => 'bookname',
                    'uitype' => 2,
                    'column' => 'bookname',
                    'table' => 'vtiger_pricebook',
                    'label' => 'Price Book Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                    'filter' => 1,
                    'filter_sequence' => 1,
                ],
                'active' => [
                    'name' => 'active',
                    'uitype' => 56,
                    'column' => 'active',
                    'table' => 'vtiger_pricebook',
                    'label' => 'Active',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'C~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'defaultvalue' => '1',
                    'filter' => 1,
                    'filter_sequence' => 2,
                ],
                'currency_id' => [
                    'name' => 'currency_id',
                    'uitype' => 117,
                    'column' => 'currency_id',
                    'table' => 'vtiger_pricebook',
                    'label' => 'Currency',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'I~M',
                    'quickcreate' => 0,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 3,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
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
                    'summaryfield' => 0,
                    'filter' => 1,
                    'filter_sequence' => 4,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_DESCRIPTION_INFORMATION' => [
                'description' => [
                    'name' => 'description',
                    'uitype' => 19,
                    'column' => 'description',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Description',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_SYSTEM_INFORMATION' => [
                'pricebook_no' => [
                    'name' => 'pricebook_no',
                    'uitype' => 4,
                    'column' => 'pricebook_no',
                    'table' => 'vtiger_pricebook',
                    'label' => 'PriceBook No',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 3,
                    'displaytype' => 1,
                    'masseditable' => 0,
                    'summaryfield' => 0,
                ],
            ]
        ];
    }

    public function getTables(): array
    {
        return [
            'vtiger_pricebook',
            'vtiger_pricebookproductrel',
            'vtiger_pricebookcf',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_pricebook', null)
            ->createTable('pricebookid')
            ->createColumn('pricebook_no', 'varchar(100) NOT NULL')
            ->createColumn('bookname', 'varchar(100) DEFAULT NULL')
            ->createColumn('active', 'int(1) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`pricebookid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_pricebook` FOREIGN KEY IF NOT EXISTS (`pricebookid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE');

        $this->getTable('vtiger_pricebookcf', null)
            ->createTable('pricebookid');

        $this->getTable('vtiger_pricebookproductrel', null)
            ->createTable('pricebookid')
            ->createColumn('productid', 'int(19) NOT NULL')
            ->createColumn('listprice', self::$COLUMN_DECIMAL)
            ->createColumn('usedcurrency', 'int(11) NOT NULL DEFAULT 1')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`pricebookid`,`productid`)')
            ->createKey('KEY IF NOT EXISTS`pricebookproductrel_pricebookid_idx` (`pricebookid`)')
            ->createKey('KEY IF NOT EXISTS`pricebookproductrel_productid_idx` (`productid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_pricebookproductrel` FOREIGN KEY IF NOT EXISTS(`pricebookid`) REFERENCES `vtiger_pricebook` (`pricebookid`) ON DELETE CASCADE');
    }
}