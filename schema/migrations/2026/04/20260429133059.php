<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260429133059')) {
    class Migration_20260429133059 extends AbstractMigrations
    {
        public function migrate(string $strFileName): void
        {
            $this->db->query('UPDATE df_inventoryitem
                SET margin = CASE
                    WHEN IFNULL(price_after_overall_discount, 0) > 0
                        THEN ROUND((IFNULL(purchase_cost_amount, 0) * 100) / price_after_overall_discount, 0)
                    ELSE 0
                END');

            $tables = [
                'vtiger_quotes' => 'quoteid',
                'vtiger_purchaseorder' => 'purchaseorderid',
                'vtiger_salesorder' => 'salesorderid',
                'vtiger_invoice' => 'invoiceid',
            ];

            foreach ($tables as $tableName => $idColumn) {
                $sql = 'UPDATE ' . $tableName . ' parent
                    LEFT JOIN (
                        SELECT
                            df_inventoryitem.parentid,
                            ROUND(SUM(df_inventoryitem.purchase_cost_amount), 2) AS purchase_cost_amount,
                            ROUND(SUM(df_inventoryitem.margin_amount), 2) AS margin_amount,
                            ROUND(SUM(df_inventoryitem.price_after_overall_discount), 2) AS price_after_overall_discount
                        FROM df_inventoryitem
                        INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = df_inventoryitem.inventoryitemid
                        WHERE vtiger_crmentity.deleted = 0
                        GROUP BY df_inventoryitem.parentid
                    ) items ON items.parentid = parent.' . $idColumn . '
                    SET
                        parent.purchase_cost_amount = COALESCE(items.purchase_cost_amount, 0),
                        parent.margin = CASE
                            WHEN COALESCE(items.price_after_overall_discount, 0) > 0
                                THEN ROUND((COALESCE(items.margin_amount, 0) * 100) / items.price_after_overall_discount, 0)
                            ELSE 0
                        END
                ';

                $this->db->query($sql);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}