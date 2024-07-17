<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20240614103500')) {
    class Migration_20240614103500 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws AppException
         */
        public function migrate(string $strFileName): void
        {
            global $current_user;

            if (empty($current_user)) {
                $user = new Users();
                $current_user = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
            }

            /** @var Core_Country_Model $countryModel */
            $countryModel = Core_Country_Model::getInstance();
            $countryModel->createTables();
            $countryModel->createLinks();

            $moduleNames = [
                'Users',
                'Contacts',
                'Accounts',
                'Leads',
                'Vendors',
                'Quotes',
                'PurchaseOrder',
                'SalesOrder',
                'Invoice',
            ];

            foreach ($moduleNames as $moduleName) {
                Core_Install_Model::getInstance('module.postinstall', $moduleName)->installModule();
            }

            $countryFields = [
                ['mailingcountry', 'vtiger_contactaddress',],
                ['othercountry', 'vtiger_contactaddress',],
                ['bill_country', 'vtiger_invoicebillads',],
                ['ship_country', 'vtiger_invoiceshipads',],
                ['country', 'vtiger_leadaddress',],
                ['bill_country', 'vtiger_pobillads',],
                ['ship_country', 'vtiger_poshipads',],
                ['bill_country', 'vtiger_quotesbillads',],
                ['ship_country', 'vtiger_quotesshipads',],
                ['bill_country', 'vtiger_sobillads',],
                ['ship_country', 'vtiger_soshipads',],
                ['address_country', 'vtiger_users',],
                ['country', 'vtiger_vendor',],
            ];
            $fieldTable = (new Core_DatabaseData_Model())->getTable('vtiger_field', null);

            foreach ($countryFields as $countryField) {
                $fieldTable->deleteData(['fieldname' => $countryField[0], 'tablename' => $countryField[1]]);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}