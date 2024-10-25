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

if (!class_exists('Migration_20241024112259')) {
    class Migration_20241024112259 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         * @throws AppException
         * @throws Exception
         */
        public function migrate(string $strFileName): void
        {
            $menu = Settings_Vtiger_Menu_Model::getInstance('LBL_INVENTORY');

            if (!$menu) {
                $menu = Settings_Vtiger_Menu_Model::getInstanceFromArray(['label' => 'LBL_INVENTORY']);
                $menu->save();
            }

            $link = Settings_Vtiger_MenuItem_Model::getInstance('Inventory Item Block', $menu);

            if (!$link) {
                $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(['name' => 'Inventory Item Block', 'blockid' => $menu->getId(), 'linkto' => 'index.php?module=InventoryItem&parent=Settings&view=Index']);
                $link->save();
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}