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

if (!class_exists('Migration_20240116152400')) {
    class Migration_20240116152400 extends AbstractMigrations
    {
        /**
         * @param string $fileName
         * @throws Exception
         */
        public function migrate(string $fileName): void
        {
            Vtiger_Link::addLink(getTabid('Potentials'), 'LISTVIEWBASIC', 'Kanban View', 'index.php?module=Potentials&view=Kanban', 'fa-solid fa-chart-simple fa-rotate-180');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}