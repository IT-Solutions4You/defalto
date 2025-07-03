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

if (!class_exists('Migration_20250624145600')) {
    class Migration_20250624145600 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws AppException
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