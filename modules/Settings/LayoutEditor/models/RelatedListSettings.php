<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_RelatedListSettings_Model extends Vtiger_Base_Model
{
    const TABLE = 'df_relatedlistsettings';

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
                `sortfield` varchar(100) DEFAULT NULL,
                `sortorder` varchar(4) DEFAULT \'ASC\',
                PRIMARY KEY (`tabid`),
                CONSTRAINT `fk_df_rls_tabid` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8',
            []
        );
    }

    /**
     * Insert initial values. The logic is:
     * - first, the entity identier columns are selected
     * - then the fields that are in the header (in detail of a record) are added
     *
     * @return void
     */
    public function initializeColumns(): void
    {
        $db = PearDatabase::getInstance();
        $entityNameResult = $db->pquery('SELECT tabid, modulename, fieldname FROM vtiger_entityname');

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
     * @return array{columnslist: array, sortfield: string, sortorder: string}
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
                'sortfield'   => $row['sortfield'] ?? '',
                'sortorder'   => $row['sortorder'] ?? 'ASC',
            ];
        }

        return [
            'columnslist' => [],
            'sortfield'   => '',
            'sortorder'   => 'ASC',
        ];
    }

    /**
     * Save settings for a module.
     *
     * @param string $moduleName
     * @param array  $columnslist Ordered list of field names
     * @param string $sortfield
     * @param string $sortorder   'ASC' or 'DESC'
     */
    public function save(string $moduleName, array $columnslist, string $sortfield, string $sortorder): void
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($moduleName);

        $db->pquery(
            'REPLACE INTO ' . self::TABLE . ' (tabid, columnslist, sortfield, sortorder) VALUES (?, ?, ?, ?)',
            [$tabid, implode(',', $columnslist), $sortfield, $sortorder]
        );
    }
}