<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/com_vtiger_workflow/VTTaskManager.inc';

if (!class_exists('Migration_20240126101600')) {
    class Migration_20240126101600 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         */
        public function migrate(string $fileName): void
        {
            Core_Readonly_Model::updateTable();
            Core_Readonly_Model::updateWorkflow();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}