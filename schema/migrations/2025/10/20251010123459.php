<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20251010123459')) {
    class Migration_20251010123459 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            $modules = ['Faq', 'PriceBooks'];

            foreach ($modules as $moduleName) {
                $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);

                if (!$moduleInstance) {
                    continue;
                }

                $this->db->pquery('UPDATE vtiger_tab SET ownedby = 0 WHERE tabid = ?', array($moduleInstance->getId()));
                Vtiger_Access::initSharing($moduleInstance);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}