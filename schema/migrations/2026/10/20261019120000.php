<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20261019120000')) {
    class Migration_20261019120000 extends AbstractMigrations
    {
        public function migrate(string $fileName): void
        {
            InventoryItem_Conversion_Model::registerDefaultConversions();
            (new InventoryItem_ConversionMapping_Model())->createTables();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}
