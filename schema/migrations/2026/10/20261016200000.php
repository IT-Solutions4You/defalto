<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261016200000')) {
    class Migration_20261016200000 extends AbstractMigrations
    {
        public function migrate(string $fileName): void
        {
            Install_Utils_Model::initProfileModulePermissions();
            Users_Privileges_Model::recalculateSharingRules();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
