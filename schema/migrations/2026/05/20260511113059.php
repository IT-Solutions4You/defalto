<?php
/**
 * This file is part of Defalto â€“ a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20260511113059')) {
    class Migration_20260511113059 extends AbstractMigrations
    {
        public function migrate(string $strFileName): void
        {
            Vtiger_Utils::AddColumn('df_inventoryitem', 'margin_combined', 'VARCHAR(255)');
            $this->db->query(
                "UPDATE df_inventoryitemcolumns
                SET columnslist = CASE
                    WHEN columnslist IS NULL OR columnslist = '' THEN 'margin_combined'
                    WHEN FIND_IN_SET('margin_combined', columnslist) = 0 THEN CONCAT(columnslist, ',margin_combined')
                    ELSE columnslist
                END
                WHERE tabid = 0"
            );

            $this->db->query(
                "UPDATE df_inventoryitem
                SET
                    margin = CASE
                        WHEN IFNULL(price_after_overall_discount, 0) > 0
                            THEN ROUND((IFNULL(margin_amount, 0) * 100) / price_after_overall_discount, 0)
                        ELSE 0
                    END,
                    margin_combined = CONCAT(
                        REPLACE(FORMAT(IFNULL(margin_amount, 0), 2), ',', ''),
                        ' (',
                        REPLACE(
                            FORMAT(
                                CASE
                                    WHEN IFNULL(price_after_overall_discount, 0) > 0
                                        THEN ROUND((IFNULL(margin_amount, 0) * 100) / price_after_overall_discount, 0)
                                    ELSE 0
                                END,
                                2
                            ),
                            ',',
                            ''
                        ),
                        '%)'
                    )"
            );

            $tables = [
                'vtiger_quotes'        => 'quoteid',
                'vtiger_purchaseorder' => 'purchaseorderid',
                'vtiger_salesorder'    => 'salesorderid',
                'vtiger_invoice'       => 'invoiceid',
            ];

            foreach ($tables as $tableName => $idColumn) {
                Vtiger_Utils::AddColumn($tableName, 'margin_combined', 'VARCHAR(255)');

                $sql = 'UPDATE ' . $tableName . ' parent
                    LEFT JOIN (
                        SELECT
                            df_inventoryitem.parentid,
                            ROUND(SUM(df_inventoryitem.purchase_cost_amount), 2) AS purchase_cost_amount,
                            ROUND(SUM(df_inventoryitem.margin_amount), 2) AS margin_amount,
                            ROUND(SUM(df_inventoryitem.price_after_overall_discount), 2) AS price_after_overall_discount,
                            CASE
                                WHEN ROUND(SUM(df_inventoryitem.price_after_overall_discount), 2) > 0
                                    THEN ROUND((ROUND(SUM(df_inventoryitem.margin_amount), 2) * 100) / ROUND(SUM(df_inventoryitem.price_after_overall_discount), 2), 0)
                                ELSE 0
                            END AS margin
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
                        END,
                        parent.margin_combined = CONCAT(
                            REPLACE(FORMAT(COALESCE(items.margin_amount, 0), 2), \',\', \'\'),
                            \' (\',
                            REPLACE(
                                FORMAT(
                                    CASE
                                        WHEN COALESCE(items.price_after_overall_discount, 0) > 0
                                            THEN ROUND((COALESCE(items.margin_amount, 0) * 100) / items.price_after_overall_discount, 0)
                                        ELSE 0
                                    END,
                                    2
                                ),
                                \',\',
                                \'\'
                            ),
                            \'%)\'
                        )';

                $this->db->query($sql);
            }
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}