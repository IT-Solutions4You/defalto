<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260429090059')) {
    class Migration_20260429090059 extends AbstractMigrations
    {
        public function migrate(string $fileName): void
        {
            $defaultColumns = 'productid,quantity,unit,price,subtotal,discount_amount,price_after_overall_discount,tax,tax_amount,price_total';

            $this->db->pquery(
                'UPDATE df_inventoryitemcolumns SET columnslist = ? WHERE tabid = ?',
                [$defaultColumns, 0]
            );
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}