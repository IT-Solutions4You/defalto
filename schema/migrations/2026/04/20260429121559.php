<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260429121559')) {
    class Migration_20260429121559 extends AbstractMigrations
    {
        public function migrate(string $strFileName): void
        {
            $this->db->query('UPDATE df_inventoryitem SET purchase_cost_amount = ROUND(IFNULL(purchase_cost, 0) * IFNULL(quantity, 0), 2)');
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}