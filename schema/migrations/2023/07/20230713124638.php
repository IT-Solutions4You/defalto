<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

if (!class_exists('Migration_20230713124638')) {
    class Migration_20230713124638 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $defaultModules = [
                'include' => [],
                'exclude' => [],
            ];

            $taskTypes[] = [
                'name'         => 'AddSharing',
                'label'        => 'Share with',
                'classname'    => 'AddSharing',
                'classpath'    => 'modules/com_vtiger_workflow/tasks/AddSharing.inc',
                'templatepath' => 'modules/Settings/Workflows/Tasks/AddSharing.tpl',
                'modules'      => $defaultModules,
                'sourcemodule' => '',
            ];
            $taskTypes[] = [
                'name'         => 'RemoveSharing',
                'label'        => 'Do not share with',
                'classname'    => 'RemoveSharing',
                'classpath'    => 'modules/com_vtiger_workflow/tasks/RemoveSharing.inc',
                'templatepath' => 'modules/Settings/Workflows/Tasks/RemoveSharing.tpl',
                'modules'      => $defaultModules,
                'sourcemodule' => '',
            ];

            foreach ($taskTypes as $taskType) {
                VTTaskType::registerTaskType($taskType);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}