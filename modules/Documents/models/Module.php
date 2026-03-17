<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_Module_Model extends Vtiger_Module_Model
{
    /**
     * Functions tells if the module supports workflow
     * @return boolean
     */
    public function isWorkflowSupported()
    {
        return true;
    }

    /**
     * Function to check whether the module is summary view supported
     * @return <Boolean> - true/false
     */
    public function isSummaryViewSupported()
    {
        return false;
    }

    /**
     * Function returns the url which gives Documents that have Internal file upload
     * @return string
     */
    public function getInternalDocumentsURL()
    {
        return 'view=Popup&module=Documents&src_module=ITS4YouEmails&src_field=composeEmail';
    }

    /**
     * Function returns list of folders
     * @return <Array> folder list
     */
    public static function getAllFolders()
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT * FROM vtiger_attachmentsfolder ORDER BY sequence', []);

        $folderList = [];
        for ($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $folderList[] = Documents_Folder_Model::getInstanceByArray($row);
        }

        return $folderList;
    }

    /**
     * Funtion that returns fields that will be showed in the record selection popup
     * @return <Array of fields>
     */
    public function getPopupFields()
    {
        $popupFields = parent::getPopupFields();
        $reqPopUpFields = [
            'filestatus',
            'filesize',
            'filelocationtype',
        ];

        return array_merge($popupFields, $reqPopUpFields);
    }

    /**
     * Function to get the url for add folder from list view of the module
     * @return <string> - url
     */
    public function getAddFolderUrl()
    {
        return 'index.php?module=' . $this->getName() . '&view=AddFolder';
    }

    /**
     * Function to get Alphabet Search Field
     */
    public function getAlphabetSearchField()
    {
        return 'notes_title';
    }

    /**
     * Function that returns related list header fields that will be showed in the Related List View
     * @return <Array> returns related fields list.
     */
    public function getRelatedListFields()
    {
        $relatedListFields = parent::getRelatedListFields();

        //Adding filestatus, filelocationtype in the related list to be used for file download
        $relatedListFields['filestatus'] = 'filestatus';
        $relatedListFields['filelocationtype'] = 'filelocationtype';

        return $relatedListFields;
    }

    /**
     * Function is used to give links in the All menu bar
     */
    public function getQuickMenuModels()
    {
        if ($this->isEntityModule()) {
            $moduleName = $this->getName();

            $createPermission = Users_Privileges_Model::isPermitted($moduleName, 'CreateView');
            if ($createPermission) {
                $basicListViewLinks[] = [
                    'linktype'  => 'LISTVIEW',
                    'linklabel' => 'LBL_INTERNAL_DOCUMENT_TYPE',
                    'linkurl'   => 'javascript:Vtiger_Header_Js.getQuickCreateFormForModule("index.php?module=Documents&view=EditAjax&type=I","Documents")',
                    'linkicon'  => ''
                ];
                $basicListViewLinks[] = [
                    'linktype'  => 'LISTVIEW',
                    'linklabel' => 'LBL_EXTERNAL_DOCUMENT_TYPE',
                    'linkurl'   => 'javascript:Vtiger_Header_Js.getQuickCreateFormForModule("index.php?module=Documents&view=EditAjax&type=E")',
                    'linkicon'  => ''
                ];
                $basicListViewLinks[] = [
                    'linktype'  => 'LISTVIEW',
                    'linklabel' => 'LBL_WEBDOCUMENT_TYPE',
                    'linkurl'   => 'javascript:Vtiger_Header_Js.getQuickCreateFormForModule("index.php?module=Documents&view=EditAjax&type=W")',
                    'linkicon'  => ''
                ];
            }
        }
        if ($basicListViewLinks) {
            foreach ($basicListViewLinks as $basicListViewLink) {
                if (is_array($basicListViewLink)) {
                    $links[] = Vtiger_Link_Model::getInstanceFromValues($basicListViewLink);
                } elseif (is_a($basicListViewLink, 'Vtiger_Link_Model')) {
                    $links[] = $basicListViewLink;
                }
            }
        }

        return $links;
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return ['Export'];
    }

    public function getConfigureRelatedListFields()
    {
        $relatedListFields = parent::getConfigureRelatedListFields();

        $checkAddFields = [
            'title'    => 'notes_title',
            'filename' => 'filename',
        ];

        foreach ($checkAddFields as $columnName => $fieldName) {
            if (!array_key_exists($columnName, $relatedListFields)) {
                $relatedListFields[$columnName] = $fieldName;
            }
        }

        return $relatedListFields;
    }

    public function isFieldsDuplicateCheckAllowed()
    {
        return false;
    }

    public function getQueryByModuleField($sourceModule, $field, $record, $listQuery)
    {
        $db = PearDatabase::getInstance();
        $condition = ' vtiger_crmentity.crmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = ? UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = ?) ';
        $params = [$record, $record];
        $condition = $db->convert2Sql($condition, $params);

        return $this->addConditionToQuery($listQuery, $condition);
    }

}