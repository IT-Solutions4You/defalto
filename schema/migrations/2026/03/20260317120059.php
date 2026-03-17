<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260317120059')) {
    class Migration_20260317120059 extends AbstractMigrations
    {
        public function migrate(string $strFileName): void
        {
            $popupSettings = Settings_LayoutEditor_PopupSettings_Model::getInstance();
            $popupSettings->createTables();
            $popupSettings->initializeColumns();
        }
    }
} else {
    $this->makeAborting('Migration_20260317120059');
}