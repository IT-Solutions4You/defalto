<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_BlockUiType_Model extends Vtiger_Base_Model
{
    /**
     * Retrieves the name associated with the given UI type from the database.
     *
     * @param int $uiType The UI type to retrieve the name for. Defaults to 1.
     *
     * @return string The name associated with the given UI type.
     * @throws Exception
     */
    public static function getNameForUIType(int $uiType = 1): string
    {
        $db = PearDatabase::getInstance();
        $query = 'SELECT name FROM df_blockuitype WHERE blockuitype = ?';
        $result = $db->pquery($query, [$uiType]);

        if (!$db->num_rows($result)) {
            return 'Base';
        }

        return ucfirst($db->query_result($result, 0, 'name'));
    }

    /**
     * Returns the id of the given block UI type. If the block UI type does not exist, 0 will be returned.
     *
     * @param string $name
     *
     * @return int
     * @throws Exception
     */
    public static function getUiTypeId(string $name): int
    {
        $db = PearDatabase::getInstance();
        $query = 'SELECT blockuitype FROM df_blockuitype WHERE name = ?';
        $result = $db->pquery($query, [$name]);

        if ($db->num_rows($result)) {
            return $db->query_result($result, 0, 'blockuitype');
        }

        return 0;
    }

    /**
     * Adds new block UI type and returns its id
     * If a block UI type with the same name already exists, its id will be returned
     *
     * @param string $name
     *
     * @return int
     * @throws Exception
     */
    public static function addBlockUiType(string $name): int
    {
        $checkId = self::getUiTypeId($name);

        if ($checkId) {
            return $checkId;
        }

        $db = PearDatabase::getInstance();

        $insertQuery = 'INSERT INTO df_blockuitype (name) VALUES (?)';
        $db->pquery($insertQuery, [$name]);

        return $db->getLastInsertID();
    }

    public function getBlockUiTypeTable()
    {
        return (new Core_DatabaseData_Model())->getTable('df_blockuitype', 'blockuitype');
    }

    public function createTables()
    {
        $this->getBlockUiTypeTable()
            ->createTable()
            ->createColumn('name', 'varchar(255) NOT NULL');
    }
}