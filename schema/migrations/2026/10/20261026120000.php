<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261026120000')) {
    class Migration_20261026120000 extends AbstractMigrations
    {
        /**
         * Invoice (Received) and Purchase Order (Paid) amounts were list-only fields; Balance is calculated by the module handler.
         *
         * @throws Exception
         */
        public function migrate(string $fileName): void
        {
            $db = PearDatabase::getInstance();
            $fields = [
                'Invoice' => 'received',
                'PurchaseOrder' => 'paid',
            ];

            foreach ($fields as $moduleName => $amountFieldName) {
                $tabId = getTabid($moduleName);

                $db->pquery('UPDATE vtiger_field SET displaytype=1 WHERE tabid=? AND fieldname=? AND displaytype=3', [$tabId, $amountFieldName]);
                $db->pquery('UPDATE vtiger_field SET displaytype=2, masseditable=0 WHERE tabid=? AND fieldname=? AND displaytype=3', [$tabId, 'balance']);

                Core_Install_Model::getInstance('module.postupdate', $moduleName)->updateEventHandler();
                Core_Install_Model::logSuccess($moduleName . ': fields ' . $amountFieldName . ' and balance updated, handler registered');
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
