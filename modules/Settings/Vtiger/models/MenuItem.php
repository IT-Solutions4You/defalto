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
 * Vtiger Settings MenuItem Model Class
 */

class Settings_Vtiger_MenuItem_Model extends Vtiger_Base_Model
{
    protected static $itemsTable = 'vtiger_settings_field';
    protected static $itemId = 'fieldid';

    public static array $defaultMenuItemLinks = [
        'LBL_USER_MANAGEMENT' => [
            1 => ['LBL_USERS', 'index.php?module=Users&parent=Settings&view=List', 'LBL_USER_DESCRIPTION', 1],
            ['LBL_ROLES', 'index.php?module=Roles&parent=Settings&view=Index', 'LBL_ROLE_DESCRIPTION'],
            ['LBL_PROFILES', 'index.php?module=Profiles&parent=Settings&view=List', 'LBL_PROFILE_DESCRIPTION'],
            ['LBL_SHARING_ACCESS', 'index.php?module=SharingAccess&parent=Settings&view=Index', 'LBL_SHARING_ACCESS_DESCRIPTION'],
            ['USERGROUPLIST', 'index.php?module=Groups&parent=Settings&view=List', 'LBL_GROUP_DESCRIPTION'],
            ['LBL_LOGIN_HISTORY_DETAILS', 'index.php?module=LoginHistory&parent=Settings&view=List', 'LBL_LOGIN_HISTORY_DESCRIPTION'],
            ['LBL_FIELDS_ACCESS', 'index.php?module=FieldAccess&parent=Settings&view=Index', 'LBL_SHARING_FIELDS_DESCRIPTION'],
        ],
        'LBL_MODULE_MANAGER' => [
            1 => ['VTLIB_LBL_MODULE_MANAGER', 'index.php?module=ModuleManager&parent=Settings&view=List', '', 1],
            ['LBL_EDIT_FIELDS', 'index.php?module=LayoutEditor&parent=Settings&view=Index', 'LBL_LAYOUT_EDITOR_DESCRIPTION'],
            ['LBL_CUSTOMIZE_MODENT_NUMBER', 'index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering', 'LBL_CUSTOMIZE_MODENT_NUMBER_DESCRIPTION'],
        ],
        'LBL_AUTOMATION' => [
            1 => ['Webforms'           ,'index.php?module=Webforms&parent=Settings&view=List', 'LBL_WEBFORMS_DESCRIPTION'],
            ['Scheduler'          ,'index.php?module=CronTasks&parent=Settings&view=List', 'Allows you to Configure Cron Task'],
            ['LBL_LIST_WORKFLOWS' ,'index.php?module=Workflows&parent=Settings&view=List', 'LBL_LIST_WORKFLOWS_DESCRIPTION', 1],
        ],
        'LBL_CONFIGURATION' => [
            1 => ['LBL_COMPANY_DETAILS'      ,'index.php?parent=Settings&module=Vtiger&view=CompanyDetails', 'LBL_COMPANY_DESCRIPTION'],
            ['LBL_CURRENCY_SETTINGS'    ,'index.php?parent=Settings&module=Currency&view=List','LBL_CURRENCY_DESCRIPTION'],
            ['LBL_MAIL_SERVER_SETTINGS' ,'index.php?parent=Settings&module=Vtiger&view=OutgoingServerDetail', 'LBL_MAIL_SERVER_DESCRIPTION'],
            ['LBL_CONFIG_EDITOR'     ,'index.php?module=Vtiger&parent=Settings&view=ConfigEditorDetail', 'LBL_CONFIG_EDITOR_DESCRIPTION'],
            ['LBL_PICKLIST_EDITOR'      ,'index.php?parent=Settings&module=Picklist&view=Index', 'LBL_PICKLIST_DESCRIPTION', 1],
            ['LBL_PICKLIST_DEPENDENCY'  ,'index.php?parent=Settings&module=PickListDependency&view=List', 'LBL_PICKLIST_DEPENDENCY_DESCRIPTION'],
            ['LBL_MENU_EDITOR'          ,'index.php?module=MenuEditor&parent=Settings&view=Index',],
            ['LBL_CUSTOMER_PORTAL'      ,'index.php?module=CustomerPortal&parent=Settings&view=Index',],
        ],
        'LBL_MARKETING_SALES' => [
            1 => ['LBL_LEAD_MAPPING'        ,'index.php?parent=Settings&module=Leads&view=MappingDetail', ''],
            ['LBL_OPPORTUNITY_MAPPING' ,'index.php?parent=Settings&module=Potentials&view=MappingDetail', ''],
        ],
        'LBL_INVENTORY' => [
            1 => ['INVENTORYTERMSANDCONDITIONS' ,'index.php?parent=Settings&module=Vtiger&view=TermsAndConditionsEdit', 'LBL_INV_TANDC_DESCRIPTION']
        ],
        'LBL_COMMUNICATION_TEMPLATES' => [
            1 => ['NOTIFICATIONSCHEDULERS', 'index.php?module=Settings&view=listnotificationschedulers&parenttab=Settings', 'LBL_NOTIF_SCHED_DESCRIPTION'],
        ],
        'LBL_MY_PREFERENCES' => [
            1 => ['My Preferences'    ,'index.php?module=Users&view=PreferenceDetail&parent=Settings&record=1', ''],
            ['Calendar Settings' ,'index.php?module=Users&parent=Settings&view=Calendar&record=1', ''],
            ['LBL_MY_TAGS'       ,'index.php?module=Tags&parent=Settings&view=List&record=1', ''],
        ],
        'LBL_EXTENSIONS' => [
            1 => ['LBL_GOOGLE', 'index.php?module=Contacts&parent=Settings&view=Extension&extensionModule=Google&extensionView=Index&mode=settings', '']
        ],
        'LBL_OTHER_SETTINGS' => [
            1 => ['LBL_MAIL_SCANNER', 'index.php?parent=Settings&module=MailConverter&view=List', 'LBL_MAIL_SCANNER_DESCRIPTION'],
        ],
        'LBL_INTEGRATION' => [
            1 => ['LBL_PBXMANAGER', 'index.php?module=PBXManager&parent=Settings&view=Index', 'PBXManager module Configuration'],
        ],
    ];

    /**
     * Function to get the Id of the menu item
     * @return <Number> - Menu Item Id
     */
    public function getId()
    {
        return $this->get(self::$itemId);
    }

    /**
     * Function to get the Menu to which the Item belongs
     * @return Settings_Vtiger_Menu_Model instance
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Function to set the Menu to which the Item belongs, given Menu Id
     *
     * @param <Number> $menuId
     *
     * @return Settings_Vtiger_MenuItem_Model
     */
    public function setMenu($menuId)
    {
        $this->menu = Settings_Vtiger_Menu_Model::getInstanceById($menuId);

        return $this;
    }

    /**
     * Function to set the Menu to which the Item belongs, given Menu Model instance
     *
     * @param <Settings_Vtiger_Menu_Model> $menu - Settings Menu Model instance
     *
     * @return Settings_Vtiger_MenuItem_Model
     */
    public function setMenuFromInstance($menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Function to get the url to get to the Settings Menu Item
     * @return <String> - Menu Item landing url
     */
    public function getUrl()
    {
        $url = decode_html($this->get('linkto'));
        $menu = $this->getMenu();
        $url .= '&block=' . $this->getMenu()->getId() . '&fieldid=' . $this->getId();

        return $url;
    }

    /**
     * Function to get the module name, to which the Settings Menu Item belongs to
     * @return <String> - Module to which the Menu Item belongs
     */
    public function getModuleName()
    {
        return 'Settings:Vtiger';
    }

    /**
     *  Function to get the pin and unpin action url
     */
    public function getPinUnpinActionUrl()
    {
        return 'index.php?module=Vtiger&parent=Settings&action=Basic&mode=updateFieldPinnedStatus&fieldid=' . $this->getId();
    }

    /**
     * Function to verify whether menuitem is pinned or not
     * @return <Boolean> true to pinned, false to not pinned.
     */
    public function isPinned()
    {
        $pinStatus = $this->get('pinned');

        return $pinStatus == '1' ? true : false;
    }

    /**
     * Function which will update the pin status
     *
     * @param <Boolean> $pinned - true to enable , false to disable
     */
    private function updatePinStatus($pinned = false)
    {
        $db = PearDatabase::getInstance();

        $pinnedStaus = 0;
        if ($pinned) {
            $pinnedStaus = 1;
        }

        $query = 'UPDATE ' . self::$itemsTable . ' SET pinned=' . $pinnedStaus . ' WHERE ' . self::$itemId . '=' . $this->getId();
        $db->pquery($query, []);
    }

    /**
     * Function which will enable the field as pinned
     */
    public function markPinned()
    {
        $this->updatePinStatus(1);
    }

    /**
     * Function which will disable the field pinned status
     */
    public function unMarkPinned()
    {
        $this->updatePinStatus();
    }

    /**
     * Function to get the instance of the Menu Item model given the valuemap array
     *
     * @param <Array> $valueMap
     *
     * @return Settings_Vtiger_MenuItem_Model instance
     */
    public static function getInstanceFromArray($valueMap)
    {
        return new self($valueMap);
    }

    /**
     * Function to get the instance of the Menu Item model, given name and Menu instance
     *
     * @param <String>                     $name
     * @param <Settings_Vtiger_Menu_Model> $menuModel
     *
     * @return Settings_Vtiger_MenuItem_Model instance
     */
    public static function getInstance($name, $menuModel = false)
    {
        $db = PearDatabase::getInstance();

        $sql = 'SELECT * FROM ' . self::$itemsTable . ' WHERE name = ?';
        $params = [$name];

        if ($menuModel) {
            $sql .= ' AND blockid = ?';
            $params[] = $menuModel->getId();
        }
        $result = $db->pquery($sql, $params);

        if ($db->num_rows($result) > 0) {
            $rowData = $db->query_result_rowdata($result, 0);
            $menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
            if ($menuModel) {
                $menuItem->setMenuFromInstance($menuModel);
            } else {
                $menuItem->setMenu($rowData['blockid']);
            }

            return $menuItem;
        }

        return false;
    }

    /**
     * Function to get the instance of the Menu Item model, given item id and Menu instance
     *
     * @param <String>                     $name
     * @param <Settings_Vtiger_Menu_Model> $menuModel
     *
     * @return Settings_Vtiger_MenuItem_Model instance
     */
    public static function getInstanceById($id, $menuModel = false)
    {
        $db = PearDatabase::getInstance();

        $sql = 'SELECT * FROM ' . self::$itemsTable . ' WHERE ' . self::$itemId . ' = ?';
        $params = [$id];

        if ($menuModel) {
            $sql .= ' WHERE blockid = ?';
            $params[] = $menuModel->getId();
        }
        $result = $db->pquery($sql, $params);

        if ($db->num_rows($result) > 0) {
            $rowData = $db->query_result_rowdata($result, 0);
            $menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
            if ($menuModel) {
                $menuItem->setMenuFromInstance($menuModel);
            } else {
                $menuItem->setMenu($rowData['blockid']);
            }

            return $menuItem;
        }

        return false;
    }

    /**
     * Static function to get the list of all the items of the given Menu, all items if Menu is not specified
     *
     * @param <Settings_Vtiger_Menu_Model> $menuModel
     *
     * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
     */
    public static function getAll($menuModel = false, $onlyActive = true)
    {
        $skipMenuItemList = [
            'LBL_AUDIT_TRAIL',
            'LBL_SYSTEM_INFO',
            'LBL_PROXY_SETTINGS',
            'LBL_DEFAULT_MODULE_VIEW',
            'LBL_FIELDFORMULAS',
            'LBL_FIELDS_ACCESS',
            'LBL_MAIL_MERGE',
            'NOTIFICATIONSCHEDULERS',
            'ModTracker',
            'LBL_WORKFLOW_LIST',
            'LBL_TOOLTIP_MANAGEMENT',
            'Webforms Configuration Editor'
        ];

        $db = PearDatabase::getInstance();
        $sql = 'SELECT * FROM ' . self::$itemsTable;
        $params = [];

        $conditionsSqls = [];
        if ($menuModel != false) {
            $conditionsSqls[] = 'blockid = ?';
            $params[] = $menuModel->getId();
        }
        if ($onlyActive) {
            $conditionsSqls[] = 'active = 0';
        }
        if (php7_count($conditionsSqls) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditionsSqls);
        }
        $sql .= ' AND name NOT IN (' . generateQuestionMarks($skipMenuItemList) . ')';

        $sql .= ' ORDER BY sequence';
        $result = $db->pquery($sql, array_merge($params, $skipMenuItemList));
        $noOfMenus = $db->num_rows($result);

        $menuItemModels = [];
        for ($i = 0; $i < $noOfMenus; ++$i) {
            $fieldId = $db->query_result($result, $i, self::$itemId);
            $rowData = $db->query_result_rowdata($result, $i);
            $menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
            if ($menuModel) {
                $menuItem->setMenuFromInstance($menuModel);
            } else {
                $menuItem->setMenu($rowData['blockid']);
            }
            $menuItemModels[$fieldId] = $menuItem;
        }

        return $menuItemModels;
    }

    /**
     * Function to get the pinned items
     *
     * @param array of fieldids.
     *
     * @return <Array> - List of Settings_Vtiger_MenuItem_Model instances
     */
    public static function getPinnedItems($fieldList = [])
    {
        $skipMenuItemList = [
            'LBL_AUDIT_TRAIL',
            'LBL_SYSTEM_INFO',
            'LBL_PROXY_SETTINGS',
            'LBL_DEFAULT_MODULE_VIEW',
            'LBL_FIELDFORMULAS',
            'LBL_FIELDS_ACCESS',
            'LBL_MAIL_MERGE',
            'NOTIFICATIONSCHEDULERS',
            'ModTracker',
            'LBL_WORKFLOW_LIST',
            'LBL_TOOLTIP_MANAGEMENT',
            'Webforms Configuration Editor'
        ];

        $db = PearDatabase::getInstance();

        $query = 'SELECT * FROM ' . self::$itemsTable . ' WHERE pinned=1 AND active = 0';
        if (!empty($fieldList)) {
            if (!is_array($fieldList)) {
                $fieldList = [$fieldList];
            }
            $query .= ' AND ' . self::$itemsId . ' IN (' . generateQuestionMarks($fieldList) . ')';
        }
        $query .= ' AND name NOT IN (' . generateQuestionMarks($skipMenuItemList) . ')';

        $result = $db->pquery($query, array_merge($fieldList, $skipMenuItemList));
        $noOfMenus = $db->num_rows($result);

        $menuItemModels = [];
        for ($i = 0; $i < $noOfMenus; ++$i) {
            $fieldId = $db->query_result($result, $i, self::$itemId);
            $rowData = $db->query_result_rowdata($result, $i);
            $menuItem = Settings_Vtiger_MenuItem_Model::getInstanceFromArray($rowData);
            $menuItem->setMenu($rowData['blockid']);
            $menuItemModels[$fieldId] = $menuItem;
        }

        return $menuItemModels;
    }

    public static function getNewSequence($blockId = null)
    {
        $sql = sprintf('SELECT max(sequence) AS max_seq FROM %s', self::$itemsTable);
        $params = [];

        if (!empty($blockId)) {
            $sql .= ' WHERE blockid=? ';
            $params = [$blockId];
        }

        $adb = PearDatabase::getInstance();
        $sequenceResult = $adb->pquery($sql, $params);

        return intval($adb->query_result($sequenceResult, 0, 'max_seq')) + 1;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $table = (new Core_DatabaseData_Model())->getTable(self::$itemsTable, self::$itemId);
        $data = $table->selectData([], ['name' => $this->get('name')]);

        if ($this->isEmpty('sequence')) {
            $this->set('sequence', self::getNewSequence($this->get('blockid')));
        }

        if (empty($data)) {
            $db = PearDatabase::getInstance();
            $table->insertData([
                self::$itemId => $db->getUniqueID(self::$itemsTable),
                'blockid'     => $this->get('blockid'),
                'name'        => $this->get('name'),
                'linkto'      => $this->get('linkto'),
                'sequence'    => $this->get('sequence'),
            ]);
        } else {
            $table->updateData([
                'blockid'  => $this->get('blockid'),
                'name'     => $this->get('name'),
                'linkto'   => $this->get('linkto'),
                'sequence' => $this->get('sequence'),
            ], [
                self::$itemId => $data[self::$itemId],
            ]);
        }
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        $table = (new Core_DatabaseData_Model())->getTable(self::$itemsTable, self::$itemId);
        $table->deleteData([
            'name' => $this->get('name'),
        ]);
    }

    /**
     * @param string $label
     * @param string $linkUrl
     * @param object $menu
     * @return Settings_Vtiger_MenuItem_Model|bool
     */
    public static function createItem(string $label, string $linkUrl, object $menu, string $description = '', int $sequence = 0, int $pinned = 0): Settings_Vtiger_MenuItem_Model|bool
    {
        $link = Settings_Vtiger_MenuItem_Model::getInstance($label, $menu);

        if (!$link) {
            $link = Settings_Vtiger_MenuItem_Model::getInstanceFromArray(['name' => $label]);
        }

        $link->set('blockid', $menu->getId());
        $link->set('linkto', $linkUrl);

        if($description) $link->set('description', $description);
        if($sequence) $link->set('sequence', $sequence);
        if($pinned) $link->set('pinned', $pinned);

        $link->save();

        return $link;
    }

    public static function deleteItem(string $label): void
    {
        $instance = self::getInstance($label);

        if ($instance) {
            $instance->delete();
        }
    }

    /**
     * @throws Exception
     */
    public function createLinks(): void
    {
        self::deleteItem('Configuration Editor');

        foreach (self::$defaultMenuItemLinks as $blockName => $fields) {
            foreach ($fields as $sequence => $field) {
                [$label, $link, $description, $pinned] = array_pad($field, 4, null);

                $menu = Settings_Vtiger_Menu_Model::createMenu($blockName);

                self::createItem($label, $link, $menu, (string)$description, (int)$sequence, (int)$pinned);
            }
        }
    }

    public function getSettingsMenuItemTable(): Core_DatabaseData_Model
    {
        return (new Core_DatabaseData_Model())->getTable(self::$itemsTable, self::$itemId);
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getSettingsMenuItemTable()
            ->createTable(self::$itemId)
            ->createColumn('blockid','int(19) DEFAULT NULL')
            ->createColumn('name','varchar(250) DEFAULT NULL')
            ->createColumn('iconpath','varchar(300) DEFAULT NULL')
            ->createColumn('description','text DEFAULT NULL')
            ->createColumn('linkto','text DEFAULT NULL')
            ->createColumn('sequence','int(19) DEFAULT NULL')
            ->createColumn('active','int(19) DEFAULT \'0\'')
            ->createColumn('pinned','int(1) DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`fieldid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_settings_field` FOREIGN KEY IF NOT EXISTS (`blockid`) REFERENCES `vtiger_settings_blocks` (`blockid`) ON DELETE CASCADE')
        ;
    }

    /**
     * @param string $name
     * @return void
     */
    public static function activate(string $name): void
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE vtiger_settings_field SET active=0 WHERE name=?', [$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    public static function deactivate(string $name): void
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE vtiger_settings_field SET active=1 WHERE name=?', [$name]);
    }
}