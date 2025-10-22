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
 * Settings List View Model Class
 */

class Settings_Vtiger_ListView_Model extends Vtiger_Base_Model
{
    protected $module;

    /**
     * Function to get the Module Model
     * @return Vtiger_Module_Model instance
     */
    public function getModule()
    {
        return $this->module;
    }

    public function setModule($name)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
        $this->module = new $modelClassName();

        return $this;
    }

    public function setModuleFromInstance($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Function to get the list view header
     * @return <Array> - List of Vtiger_Field_Model instances
     */
    public function getListViewHeaders()
    {
        $module = $this->getModule();

        return $module->getListFields();
    }

    public function getBasicListQuery()
    {
        $module = $this->getModule();

        return 'SELECT * FROM ' . $module->getBaseTable();
    }

    /**
     * Function to get the list view entries
     *
     * @param Vtiger_Paging_Model $pagingModel
     *
     * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
     */
    public function getListViewEntries($pagingModel)
    {
        $db = PearDatabase::getInstance();

        $module = $this->getModule();
        $moduleName = $module->getName();
        $parentModuleName = $module->getParentName();
        $qualifiedModuleName = $moduleName;
        if (!empty($parentModuleName)) {
            $qualifiedModuleName = $parentModuleName . ':' . $qualifiedModuleName;
        }
        $recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
        $listQuery = $this->getBasicListQuery();

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();

        $orderBy = $this->getForSql('orderby');
        if (!empty($orderBy) && $orderBy === 'assigned_user_id') {
            $fieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', $moduleModel);
            if ($fieldModel->getFieldDataType() == 'owner') {
                $orderBy = 'COALESCE(vtiger_users.userlabel,vtiger_groups.groupname)';
            }
        }
        if (!empty($orderBy)) {
            $listQuery .= ' ORDER BY ' . $orderBy . ' ' . $this->getForSql('sortorder');
        }
        if ($module->isPagingSupported()) {
            $listQuery .= " LIMIT $startIndex, " . ($pageLimit + 1);
        }

        $listResult = $db->pquery($listQuery, []);
        $noOfRecords = $db->num_rows($listResult);

        $listViewRecordModels = [];
        for ($i = 0; $i < $noOfRecords; ++$i) {
            $row = $db->query_result_rowdata($listResult, $i);
            $record = new $recordModelClass();
            $record->setData($row);

            if (method_exists($record, 'getModule') && method_exists($record, 'setModule')) {
                $moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
                $record->setModule($moduleModel);
            }

            $listViewRecordModels[$record->getId()] = $record;
        }
        if ($module->isPagingSupported()) {
            $pagingModel->calculatePageRange($listViewRecordModels);
            if (php7_count($listViewRecordModels) > $pageLimit) {
                array_pop($listViewRecordModels);
                $pagingModel->set('nextPageExists', true);
            } else {
                $pagingModel->set('nextPageExists', false);
            }
        }

        return $listViewRecordModels;
    }

    public function getListViewLinks()
    {
        $links = [];
        $basicLinks = $this->getBasicLinks();

        foreach ($basicLinks as $basicLink) {
            $links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
        }

        return $links;
    }

    /*
     * Function to get Basic links
     * @return array of Basic links
     */
    public function getBasicLinks()
    {
        $basicLinks = [];
        $moduleModel = $this->getModule();
        if ($moduleModel->hasCreatePermissions()) {
            $basicLinks[] = [
                'linktype'    => 'LISTVIEWBASIC',
                'linklabel'   => 'LBL_ADD_RECORD',
                'linkurl'     => $moduleModel->getCreateRecordUrl(),
                'linkicon'    => 'fa fa-plus',
                'style_class' => Vtiger_Link_Model::PRIMARY_STYLE_CLASS,
            ];
        }

        return $basicLinks;
    }

    /*	 * *
     * Function which will get the list view count
     * @return - number of records
     */

    public function getListViewCount()
    {
        $db = PearDatabase::getInstance();

        $listQuery = $this->getBasicListQuery();

        $position = stripos($listQuery, ' from ');
        if ($position) {
            $split = preg_split('/ from /i', $listQuery);
            $splitCount = php7_count($split);
            $listQuery = 'SELECT count(*) AS count ';
            for ($i = 1; $i < $splitCount; $i++) {
                $listQuery = $listQuery . ' FROM ' . $split[$i];
            }
        }

        $listResult = $db->pquery($listQuery, []);

        return $db->query_result($listResult, 0, 'count');
    }

    /**
     * Function to get the instance of Settings module model
     * @return Settings_Vtiger_Module_Model instance
     */
    public static function getInstance($name = 'Settings:Vtiger')
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $name);
        $instance = new $modelClassName();

        return $instance->setModule($name);
    }
}