<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_PopupSettings_Model extends Vtiger_Base_Model
{
    const TABLE = 'df_popupsettings';

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static();
    }

    /**
     * Create DB table if it does not exist yet.
     */
    public function createTables(): void
    {
        $db = PearDatabase::getInstance();
        $db->pquery(
            'CREATE TABLE IF NOT EXISTS `' . self::TABLE . '` (
                `tabid` int(19) NOT NULL,
                `columnslist` text,
                PRIMARY KEY (`tabid`),
                CONSTRAINT `fk_df_ps_tabid` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8',
            []
        );
    }

    /**
     * Insert initial values. The logic is:
     * - first, the entity identier columns are selected
     * - then the fields that are in the header (in detail of a record)
     *
     * @return void
     */
    public function initializeColumns(): void
    {
        $db = PearDatabase::getInstance();
        $entityNameResult = $db->pquery('SELECT tabid, fieldname FROM vtiger_entityname');

        while ($entityNameRow = $db->fetchByAssoc($entityNameResult)) {
            $headerFieldsModel = new Settings_LayoutEditor_HeaderFields_Model();
            $fields = explode(',', $entityNameRow['fieldname']);
            $headerFields = $headerFieldsModel->getHeaderFields($entityNameRow['modulename']);
            $iterator = 0;

            while (isset($headerFields[$iterator]) && $iterator < 5) {
                if (!in_array($headerFields[$iterator]['fieldname'], $fields)) {
                    $fields[] = $headerFields[$iterator]['fieldname'];
                }

                $iterator++;
            }

            $db->pquery('REPLACE INTO ' . self::TABLE . ' (tabid, columnslist) VALUES (?, ?)', [$entityNameRow['tabid'], implode(',', $fields)]);
        }
    }

    /**
     * Get saved settings for a module.
     *
     * @param string $moduleName
     *
     * @return array{columnslist: array}
     */
    public function getSettings(string $moduleName): array
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($moduleName);
        $result = $db->pquery('SELECT * FROM ' . self::TABLE . ' WHERE tabid = ?', [$tabid]);
        $row = $db->fetchByAssoc($result);

        if ($row) {
            return [
                'columnslist' => !empty($row['columnslist']) ? explode(',', $row['columnslist']) : [],
            ];
        }

        return [
            'columnslist' => [],
        ];
    }

    /**
     * Save settings for a module.
     *
     * @param string $moduleName
     * @param array  $columnslist Ordered list of field names
     */
    public function save(string $moduleName, array $columnslist): void
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($moduleName);

        $db->pquery(
            'REPLACE INTO ' . self::TABLE . ' (tabid, columnslist) VALUES (?, ?)',
            [$tabid, implode(',', $columnslist)]
        );
    }
}