<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260907120000')) {
    class Migration_20260907120000 extends AbstractMigrations
    {
        public function migrate(string $fileName): void
        {
            $blockName = 'LBL_MODULE_MANAGER';
            $label = 'LBL_SUMMARY_WIDGET_EDITOR';
            $menu = Settings_Vtiger_Menu_Model::createMenu($blockName);

            foreach (Settings_Vtiger_MenuItem_Model::$defaultMenuItemLinks[$blockName] as $sequence => $definition) {
                if ($definition[0] === $label) {
                    Settings_Vtiger_MenuItem_Model::createItem(
                        $label,
                        $definition[1],
                        $menu,
                        $definition[2],
                        $sequence
                    );
                    Settings_Vtiger_MenuItem_Model::activate($label);

                    break;
                }
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
