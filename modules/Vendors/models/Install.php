<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vendors_Install_Model extends Core_Install_Model {

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        // TODO: Implement addCustomLinks() method.
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        // TODO: Implement deleteCustomLinks() method.
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [
            'LBL_VENDOR_INFORMATION' => [
                'vendorname' => [
                    'name' => 'vendorname',
                    'uitype' => 2,
                    'column' => 'vendorname',
                    'table' => 'vtiger_vendor',
                    'label' => 'Vendor Name',
                    'readonly' => 1,
                    'presence' => 0,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                    'entity_identifier' => 1,
                ],
                'phone' => [
                    'name' => 'phone',
                    'uitype' => 1,
                    'column' => 'phone',
                    'table' => 'vtiger_vendor',
                    'label' => 'Phone',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                ],
                'email' => [
                    'name' => 'email',
                    'uitype' => 13,
                    'column' => 'email',
                    'table' => 'vtiger_vendor',
                    'label' => 'Email',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'E~O',
                    'quickcreate' => 2,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                ],
                'website' => [
                    'name' => 'website',
                    'uitype' => 17,
                    'column' => 'website',
                    'table' => 'vtiger_vendor',
                    'label' => 'Website',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'headerfield' => 1,
                ],
                'glacct' => [
                    'name' => 'glacct',
                    'uitype' => 15,
                    'column' => 'glacct',
                    'table' => 'vtiger_vendor',
                    'label' => 'GL Account',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                    'picklist_values' => [
                        '300-Sales-Software',
                        '301-Sales-Hardware',
                        '302-Rental-Income',
                        '303-Interest-Income',
                        '304-Sales-Software-Support',
                        '305-Sales Other',
                        '306-Internet Sales',
                        '307-Service-Hardware Labor',
                        '308-Sales-Books',
                    ],
                ],
                'category' => [
                    'name' => 'category',
                    'uitype' => 1,
                    'column' => 'category',
                    'table' => 'vtiger_vendor',
                    'label' => 'Category',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 1,
                ],
                'assigned_user_id' => [
                    'name' => 'assigned_user_id',
                    'uitype' => 53,
                    'column' => 'smownerid',
                    'table' => 'vtiger_crmentity',
                    'label' => 'Assigned To',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~M',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
            ],
            'LBL_CUSTOM_INFORMATION' => [
            ],
            'LBL_VENDOR_ADDRESS_INFORMATION' => [
                'street' => [
                    'name' => 'street',
                    'uitype' => 21,
                    'column' => 'street',
                    'table' => 'vtiger_vendor',
                    'label' => 'Street',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'pobox' => [
                    'name' => 'pobox',
                    'uitype' => 1,
                    'column' => 'pobox',
                    'table' => 'vtiger_vendor',
                    'label' => 'Po Box',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'city' => [
                    'name' => 'city',
                    'uitype' => 1,
                    'column' => 'city',
                    'table' => 'vtiger_vendor',
                    'label' => 'City',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'state' => [
                    'name' => 'state',
                    'uitype' => 1,
                    'column' => 'state',
                    'table' => 'vtiger_vendor',
                    'label' => 'State',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'postalcode' => [
                    'name' => 'postalcode',
                    'uitype' => 1,
                    'column' => 'postalcode',
                    'table' => 'vtiger_vendor',
                    'label' => 'Postal Code',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
                'country_id' => [
                    'name' => 'country_id',
                    'uitype' => 18,
                    'column' => 'country_id',
                    'table' => 'vtiger_vendor',
                    'label' => 'Country',
                    'readonly' => 1,
                    'presence' => 2,
                    'typeofdata' => 'V~O',
                    'quickcreate' => 1,
                    'displaytype' => 1,
                    'masseditable' => 1,
                    'summaryfield' => 0,
                ],
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
                'vendor_no' => [
                    'name' => 'vendor_no',
                    'uitype' => 4,
                    'column' => 'vendor_no',
                    'table' => 'vtiger_vendor',
                    'label' => 'Vendor No',
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

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_vendor', null)
            ->createTable('vendorid', 'int(19) NOT NULL DEFAULT \'0\'')
            ->createColumn('vendor_no', 'varchar(100) NOT NULL')
            ->createColumn('vendorname', 'varchar(100) DEFAULT NULL')
            ->createColumn('phone', 'varchar(100) DEFAULT NULL')
            ->createColumn('email', 'varchar(100) DEFAULT NULL')
            ->createColumn('website', 'varchar(100) DEFAULT NULL')
            ->createColumn('glacct', 'varchar(200) DEFAULT NULL')
            ->createColumn('category', 'varchar(50) DEFAULT NULL')
            ->createColumn('street', 'text DEFAULT NULL')
            ->createColumn('city', 'varchar(30) DEFAULT NULL')
            ->createColumn('state', 'varchar(30) DEFAULT NULL')
            ->createColumn('pobox', 'varchar(30) DEFAULT NULL')
            ->createColumn('postalcode', 'varchar(100) DEFAULT NULL')
            ->createColumn('country_id', 'varchar(100) DEFAULT NULL')
            ->createColumn('description', 'text DEFAULT NULL')
            ->createColumn('tags', 'varchar(1) DEFAULT NULL')
            ->createColumn('currency_id', 'int(19) DEFAULT NULL')
            ->createColumn('conversion_rate', 'decimal(10,3) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`vendorid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_vendor` FOREIGN KEY IF NOT EXISTS (`vendorid`) REFERENCES `vtiger_crmentity` (`crmid`) ON DELETE CASCADE')
        ;
    }
}