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

/**
 * Vtiger MenuStructure Model
 */
class Vtiger_MenuStructure_Model extends Vtiger_Base_Model
{
    protected $limit = 5; // Max. limit of persistent top-menu items to display.
    protected $enableResponsiveMode = true; // Should the top-menu items be responsive (width) on UI?

    const TOP_MENU_INDEX = 'top';
    const MORE_MENU_INDEX = 'more';

    protected $menuGroupByParent = [];

    /**
     * @var array
     */
    public static array $ignoredModules = [
        'ModComments',
    ];

    /**
     * Function to get all the top menu models
     * @return <array> - list of Vtiger_Menu_Model instances
     */
    public function getTop()
    {
        return $this->get(self::TOP_MENU_INDEX);
    }

    /**
     * Function to get all the more menu models
     * @return <array> - Associate array of Parent name mapped to Vtiger_Menu_Model instances
     */
    public function getMore()
    {
        $moreTabs = $this->get(self::MORE_MENU_INDEX);
        foreach ($moreTabs as $key => $value) {
            if (!$value) {
                unset($moreTabs[$key]);
            }
        }

        return $moreTabs;
    }

    /**
     * Function to get the limit for the number of menu models on the Top list
     * @return <Number>
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Function to determine if the structure should support responsive UI.
     */
    public function getResponsiveMode()
    {
        return $this->enableResponsiveMode;
    }

    public function getMenuGroupedByParent()
    {
        return $this->menuGroupByParent;
    }

    public function setMenuGroupedByParent($structure)
    {
        $this->menuGroupByParent = $structure;

        return $this;
    }

    /**
     * Function to get an instance of the Vtiger MenuStructure Model from list of menu models
     *
     * @param <array> $menuModelList - array of Vtiger_Menu_Model instances
     *
     * @return Vtiger_MenuStructure_Model instance
     */
    public static function getInstanceFromMenuList($menuModelList, $selectedMenu = '')
    {
        $structureModel = new self();
        $topMenuLimit = $structureModel->getResponsiveMode() ? 0 : $structureModel->getLimit();
        $currentTopMenuCount = 0;
        $menuGroupedListByParent = [];
        $menuListArray = [];
        $menuListArray[self::TOP_MENU_INDEX] = [];
        $menuListArray[self::MORE_MENU_INDEX] = $structureModel->getEmptyMoreMenuList();

        foreach ($menuModelList as $menuModel) {
            if (($menuModel->get('tabsequence') != -1 && (!$topMenuLimit || $currentTopMenuCount < $topMenuLimit))) {
                $menuListArray[self::TOP_MENU_INDEX][$menuModel->get('name')] = $menuModel;
                $currentTopMenuCount++;
            }

            $parent = ucfirst(strtolower($menuModel->get('parent') ? $menuModel->get('parent') : ''));
            $menuListArray[self::MORE_MENU_INDEX][strtoupper($parent)][$menuModel->get('name')] = $menuModel;
            $menuGroupedListByParent[strtoupper($parent)][$menuModel->get('name')] = $menuModel;
        }

        if (!empty($selectedMenu) && isset($menuModelList[$selectedMenu]) && !array_key_exists($selectedMenu, $menuListArray[self::TOP_MENU_INDEX])) {
            $selectedMenuModel = $menuModelList[$selectedMenu];
            if ($selectedMenuModel) {
                $menuListArray[self::TOP_MENU_INDEX][$selectedMenuModel->get('name')] = $selectedMenuModel;
            }
        }

        // Apply custom comparator
        foreach ($menuListArray[self::MORE_MENU_INDEX] as $parent => &$values) {
            uksort($values, ['Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess']);
        }

        //uksort($menuListArray[self::TOP_MENU_INDEX], array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));

        return $structureModel->setData($menuListArray)->setMenuGroupedByParent($menuGroupedListByParent);
    }

    /**
     * Custom comparator to sort the menu items by process.
     * Refer: http://php.net/manual/en/function.uksort.php
     */
    static function sortMenuItemsByProcess($a, $b)
    {
        static $order = null;
        if ($order == null) {
            $order = [
                'Campaigns',
                'Leads',
                'Contacts',
                'Accounts',
                'Potentials',
                'Quotes',
                'Invoice',
                'SalesOrder',
                'HelpDesk',
                'Faq',
                'Project',
                'Assets',
                'ServiceContracts',
                'Products',
                'Services',
                'PriceBooks',
                'Vendors',
                'PurchaseOrder',
                'MailManager',
                'Documents',
                'SMSNotifier',
                'RecycleBin',
                'ProjectTask',
                'ProjectMilestone'
            ];
        }
        $apos = array_search($a, $order);
        $bpos = array_search($b, $order);

        if ($apos === false) {
            return PHP_INT_MAX;
        }
        if ($bpos === false) {
            return -1 * PHP_INT_MAX;
        }

        return ($apos - $bpos);
    }

    private function getEmptyMoreMenuList()
    {
        return ['CONTACT' => [], 'MARKETING_AND_SALES' => [], 'SUPPORT' => [], 'INVENTORY' => [], 'TOOLS' => [], 'ANALYTICS' => []];
    }

    public static function getIgnoredModules()
    {
        return self::$ignoredModules;
    }

    /**
     * @return array
     */
    public static function getAdditionsToAppMap(): array
    {
        $sequences = (new Settings_MenuEditor_Module_Model())->getDefaultSequence();
        $additions = [];

        foreach ($sequences as $sequenceParent => $sequenceModules) {
            foreach ($sequenceModules as $sequenceModule) {
                $additions[$sequenceModule][] = $sequenceParent;
            }
        }

        return $additions;
    }

    public function regroupMenuByParent($menuGroupedByParent): array
    {
        $additionsToAppMap = self::getAdditionsToAppMap();
        $ignoredModules = self::getIgnoredModules();
        $oldToNewAppMap = self::getOldToNewAppMapping();
        $regroupMenuByParent = [];

        foreach ($menuGroupedByParent as $appName => $appModules) {
            foreach ($appModules as $moduleName => $moduleModel) {
                if (in_array($moduleName, $ignoredModules)) {
                    continue;
                }

                if (!empty($additionsToAppMap[$moduleName])) {
                    foreach ($additionsToAppMap[$moduleName] as $app) {
                        $regroupMenuByParent[$app][$moduleName] = $moduleModel;
                    }
                } elseif (isset($oldToNewAppMap[$appName])) {
                    $app = $oldToNewAppMap[$appName];
                    $regroupMenuByParent[$app][$moduleName] = $moduleModel;
                }
            }
        }

        return $regroupMenuByParent;
    }

    public static function getOldToNewAppMapping()
    {
        return [
            'CONTACT'             => 'SALES',
            'MARKETING_AND_SALES' => 'MARKETING',
            'INVENTORY'           => 'INVENTORY',
            'SUPPORT'             => 'SUPPORT',
            'PROJECT'             => 'PROJECT',
            'TOOLS'               => 'TOOLS',
        ];
    }

    /**
     * Function to get the app menu items in order
     * @return <array>
     */
    public static function getAppMenuList()
    {
        return ['HOME', 'MARKETING', 'SALES', 'INVENTORY', 'SUPPORT', 'PROJECT', 'ANALYTICS', 'TOOLS'];
    }

    public static function getAppIcons()
    {
        return [
            'HOME'      => 'fa-home',
            'MARKETING' => 'fa-users',
            'SALES'     => 'fa-regular fa-circle-dot',
            'SUPPORT'   => 'fa-life-ring',
            'INVENTORY' => 'vicon-inventory',
            'PROJECT'   => 'fa-briefcase',
            'ANALYTICS' => 'fa-solid fa-chart-pie',
            'TOOLS'     => 'fa-wrench',
        ];
    }
}