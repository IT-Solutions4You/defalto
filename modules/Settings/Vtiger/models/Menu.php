<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/*
 * Settings Menu Model Class
 */

class Settings_Vtiger_Menu_Model extends Vtiger_Base_Model
{
    protected static $menusTable = 'vtiger_settings_blocks';
    protected static $menuId = 'blockid';

    /**
     * Function to get the Id of the Menu Model
     * @return <Number> - Menu Id
     */
    public function getId()
    {
        return $this->get(self::$menuId);
    }

    /**
     * Function to get the menu label
     * @return <String> - Menu Label
     */
    public function getLabel()
    {
        return $this->get('label');
    }

    /**
     * Function to get the url to list the items of the Menu
     * @return <String> - List url
     */
    public function getListUrl()
    {
        return 'index.php?module=Vtiger&parent=Settings&view=ListMenu&block=' . $this->getId();
    }

    /**
     * Function to get all the menu items of the current menu
     * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
     */
    public function getItems()
    {
        return Settings_Vtiger_MenuItem_Model::getAll($this);
    }

    /**
     * Static function to get the list of all the Settings Menus
     * @return <Array> - List of Settings_Vtiger_Menu_Model instances
     */
    public static function getAll()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM ' . self::$menusTable . ' ORDER BY sequence', []);
        $noOfMenus = $db->num_rows($result);

        $menuModels = [];
        for ($i = 0; $i < $noOfMenus; ++$i) {
            $blockId = $db->query_result($result, $i, self::$menuId);
            $rowData = $db->query_result_rowdata($result, $i);
            $menuModels[$blockId] = Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
        }

        return $menuModels;
    }

    /**
     * Static Function to get the instance of Settings Menu model with the given value map array
     *
     * @param <Array> $valueMap
     *
     * @return Settings_Vtiger_Menu_Model instance
     */
    public static function getInstanceFromArray($valueMap)
    {
        return new self($valueMap);
    }

    /**
     * Static Function to get the instance of Settings Menu model for given menu id
     *
     * @param <Number> $id - Menu Id
     *
     * @return Settings_Vtiger_Menu_Model instance
     */
    public static function getInstanceById($id)
    {
        $db = PearDatabase::getInstance();

        $sql = 'SELECT * FROM ' . self::$menusTable . ' WHERE ' . self::$menuId . ' = ?';
        $params = [$id];

        $result = $db->pquery($sql, $params);

        if ($db->num_rows($result) > 0) {
            $rowData = $db->query_result_rowdata($result, 0);

            return Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
        }

        return false;
    }

    /**
     * Static Function to get the instance of Settings Menu model for the given menu name
     *
     * @param <String> $name - Menu Name
     *
     * @return Settings_Vtiger_Menu_Model instance
     */
    public static function getInstance($name)
    {
        $db = PearDatabase::getInstance();

        $sql = 'SELECT * FROM ' . self::$menusTable . ' WHERE label = ?';
        $params = [$name];

        $result = $db->pquery($sql, $params);

        if ($db->num_rows($result) > 0) {
            $rowData = $db->query_result_rowdata($result, 0);

            return Settings_Vtiger_Menu_Model::getInstanceFromArray($rowData);
        }

        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function save(): bool
    {
        $db = PearDatabase::getInstance();
        $label = $this->getLabel();

        if ($this->isEmpty('label')) {
            return false;
        }

        if ($this->isEmpty('sequence')) {
            $this->set('sequence', $this->getNewSequence());
        }

        $table = (new Core_DatabaseData_Model())->getTable(self::$menusTable, self::$menuId);
        $sequence = $this->get('sequence');

        if (empty($this->getId())) {
            $this->set(self::$menuId, $db->getUniqueID('vtiger_settings_blocks'));
            $table->insertData([self::$menuId => $this->getId(), 'label' => $label, 'sequence' => $sequence]);
        } else {
            $table->updateData(['label' => $label, 'sequence' => $sequence], [self::$menuId => $this->getId()]);
        }

        return true;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getNewSequence(): int
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT max(sequence) AS max_seq FROM vtiger_settings_blocks');

        return intval($db->query_result($result, 0, 'max_seq')) + 1;
    }

    /**
     * Function returns menu items for the current menu
     * @return <Settings_Vtiger_MenuItem_Model>
     */
    public function getMenuItems()
    {
        return Settings_Vtiger_MenuItem_Model::getAll($this);
    }

    /**
     * @throws Exception
     */
    public static function createMenu(string $label): Settings_Vtiger_Menu_Model|bool
    {
        $menu = Settings_Vtiger_Menu_Model::getInstance($label);

        if (!$menu) {
            $menu = Settings_Vtiger_Menu_Model::getInstanceFromArray(['label' => $label]);
            $menu->save();
        }

        return $menu;
    }
}