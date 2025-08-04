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

if (!class_exists('Migration_20250624145600')) {
    class Migration_20250624145600 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            Core_Install_Model::getTableInstance('com_vtiger_workflows', 'workflow_id')->deleteData(['summary' => 'Calculate or Update forecast amount']);
            Core_Install_Model::getTableInstance('com_vtiger_workflowtasks', 'task_id')->deleteData(['summary' => 'update forecast amount']);
            Core_Install_Model::logSuccess('Delete workflow: Calculate or Update forecast amount');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}