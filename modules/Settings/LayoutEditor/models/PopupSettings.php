<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_LayoutEditor_PopupSettings_Model extends Core_DatabaseData_Model
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
     *
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getTable(self::TABLE, null)
            ->createTable('tabid', 'int(19) NOT NULL')
            ->createColumn('columnslist', 'text')
            ->createKey('PRIMARY KEY (tabid)')
            ->createKey('CONSTRAINT fk_df_ps_tabid FOREIGN KEY (tabid) REFERENCES vtiger_tab (tabid) ON DELETE CASCADE');
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
     * Expects 'moduleName' to be set via $this->set('moduleName', ...) before calling.
     *
     * @return array{columnslist: array}
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
            ];
        }

        return [
            'columnslist' => [],
        ];
    }

    /**
     * Save settings for a module.
     * Expects values set via $this->set() before calling:
     *   - 'moduleName' (string)
     *   - 'columnslist' (array) — ordered list of field names
     */
    public function save(): void
    {
        $db = PearDatabase::getInstance();
        $tabid = getTabid($this->get('moduleName'));

        $db->pquery(
            'REPLACE INTO ' . self::TABLE . ' (tabid, columnslist) VALUES (?, ?)',
            [$tabid, implode(',', (array)$this->get('columnslist'))]
        );
    }
}