<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_RelatedListSettings_Model extends Core_DatabaseData_Model
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
     *
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable(self::TABLE, null)
            ->createTable('tabid', 'int(19) NOT NULL')
            ->createColumn('columnslist', 'text')
            ->createColumn('sortfield', 'varchar(100) DEFAULT NULL')
            ->createColumn('sortorder', "varchar(4) DEFAULT 'ASC'")
            ->createKey('PRIMARY KEY (tabid)')
            ->createKey('CONSTRAINT fk_df_rls_tabid FOREIGN KEY (tabid) REFERENCES vtiger_tab (tabid) ON DELETE CASCADE');
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
     * Expects 'moduleName' to be set via $this->set('moduleName', ...) before calling.
     *
     * @return array{columnslist: array, sortfield: string, sortorder: string}
     */
    public function getSettings(): array
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($this->get('moduleName'));
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
     * Expects values set via $this->set() before calling:
     *   - 'moduleName' (string)
     *   - 'columnslist' (array) — ordered list of field names
     *   - 'sortfield' (string)
     *   - 'sortorder' (string) — 'ASC' or 'DESC'
     */
    public function save(): void
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($this->get('moduleName'));

        $db->pquery(
            'REPLACE INTO ' . self::TABLE . ' (tabid, columnslist, sortfield, sortorder) VALUES (?, ?, ?, ?)',
            [$tabid, implode(',', (array)$this->get('columnslist')), (string)$this->get('sortfield'), (string)$this->get('sortorder')]
        );
    }
}