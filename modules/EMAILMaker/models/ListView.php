<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_ListView_Model extends Vtiger_ListView_Model
{


    private $querySelectColumns = array('templatename, subject', 'module', 'description');
    private $listViewColumns = array('templatename', 'subject', 'description', 'module');

    public static function getInstance($moduleName, $viewId = '0', $listHeaders = [])
    {
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        return $instance->set('module', $moduleModel);
    }

    public static function getInstanceForPopup($value)
    {
        $db = PearDatabase::getInstance();
        $currentUser = vglobal('current_user');

        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
        $instance = new $modelClassName();
        $moduleModel = Vtiger_Module_Model::getInstance($value);

        $queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);

        $listFields = $moduleModel->getPopupFields();
        $listFields[] = 'id';
        $queryGenerator->setFields($listFields);

        $controller = new ListViewController($db, $currentUser, $queryGenerator);

        return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
    }

    public function addColumnToSelectCaluse($columName)
    {
        if (!is_array($columName)) {
            $columNameList = array($columName);
        } else {
            $columNameList = $columName;
        }

        $this->querySelectColumns = array_merge($this->querySelectColumns, $columNameList);
        return $this;
    }

    public function getSideBarLinks($linkParams)
    {
        $linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
        $moduleLinks = $this->getModule()->getSideBarLinks($linkParams);
        $listLinkTypes = array('LISTVIEWSIDEBARLINK', 'LISTVIEWSIDEBARWIDGET');
        $listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

        if ($listLinks['LISTVIEWSIDEBARLINK']) {
            foreach ($listLinks['LISTVIEWSIDEBARLINK'] as $link) {
                $moduleLinks['SIDEBARLINK'][] = $link;
            }
        }
        if ($listLinks['LISTVIEWSIDEBARWIDGET']) {
            foreach ($listLinks['LISTVIEWSIDEBARWIDGET'] as $link) {
                $moduleLinks['SIDEBARWIDGET'][] = $link;
            }
        }
        return $moduleLinks;
    }

    public function getModule()
    {
        return $this->get('module');
    }

    public function getListViewLinks($linkParams)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleModel = $this->getModule();

        $linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        $basicLinks = $this->getBasicLinks();
        foreach ($basicLinks as $basicLink) {
            $links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
        }
        $advancedLinks = $this->getAdvancedLinks();
        foreach ($advancedLinks as $advancedLink) {
            $links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
        }
        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $this->getSettingLinks();
            foreach ($settingsLinks as $settingsLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }
        return $links;
    }

    public function getBasicLinks()
    {
        $basicLinks = array();
        $moduleModel = $this->getModule();
        $createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
        if ($createPermission) {
            $basicLinks[] = array(
                'linktype' => 'LISTVIEWBASIC',
                'linklabel' => 'LBL_ADD_RECORD',
                'linkurl' => $moduleModel->getCreateRecordUrl(),
                'linkicon' => ''
            );
        }
        return $basicLinks;
    }

    public function getAdvancedLinks()
    {
        $moduleModel = $this->getModule();
        $createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
        $advancedLinks = array();
        $importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
        if ($importPermission && $createPermission) {
            $advancedLinks[] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl' => $moduleModel->getImportUrl(),
                'linkicon' => ''
            );
        }
        $exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
        if ($exportPermission) {
            $advancedLinks[] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_EXPORT',
                'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("' . $this->getModule()->getExportUrl() . '")',
                'linkicon' => ''
            );
        }
        $duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
        if ($duplicatePermission) {
            $advancedLinks[] = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_FIND_DUPLICATES',
                'linkurl' => 'Javascript:Vtiger_List_Js.showDuplicateSearchForm("index.php?module=' . $moduleModel->getName() .
                    '&view=MassActionAjax&mode=showDuplicatesSearchForm")',
                'linkicon' => ''
            );
        }
        return $advancedLinks;
    }

    public function getSettingLinks()
    {
        return $this->getModule()->getSettingLinks();
    }

    public function getListViewMassActions($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();
        $linkTypes = array('LISTVIEWMASSACTION');
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);
        $massActionLinks = array();
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
            $massActionLinks[] = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_EDIT',
                'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showMassEditForm");',
                'linkicon' => ''
            );
        }
        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            $massActionLinks[] = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
                'linkicon' => ''
            );
        }
        if ($moduleModel->isCommentEnabled()) {
            $massActionLinks[] = array(
                'linktype' => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_ADD_COMMENT',
                'linkurl' => 'index.php?module=' . $moduleModel->get('name') . '&view=MassActionAjax&mode=showAddCommentForm',
                'linkicon' => ''
            );
        }
        foreach ($massActionLinks as $massActionLink) {
            $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
        }
        return $links;
    }

    /**
     * Function to get the list view header
     * @return <Array> - List of Vtiger_Field_Model instances
     */
    public function getListViewHeaders()
    {
        $fieldObjects = array();
        $listViewHeaders = array('Template Name' => 'templatename', 'Subject' => 'subject', 'Description' => 'description', 'Module Name' => 'module');
        foreach ($listViewHeaders as $key => $fieldName) {
            $fieldModel = new EMAILMaker_Field_Model();
            $fieldModel->set('name', $fieldName);
            $fieldModel->set('label', $key);
            $fieldModel->set('column', $fieldName);
            $fieldObjects[] = $fieldModel;
        }
        return $fieldObjects;
    }

    public function getListViewEntries($pagingModel)
    {
        $sourceModule = $this->get('src_module');
        $sourceRecord = $this->get('src_record');
        $default_charset = vglobal('default_charset');
        $listViewRecordModels = [];

        $forListView = false;

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $Templates = $EMAILMaker->GetAvailableTemplatesArray($sourceModule, $forListView, $sourceRecord, true, true);

        foreach ($Templates as $row) {
            $recordModel = new EMAILMaker_Record_Model();
            $recordModel->setModule('EMAILMaker');

            foreach ($row as $key => $value) {
                if ($key == "body" || $key == "subtest") {
                    $row[$key] = html_entity_decode($value, ENT_QUOTES, $default_charset);
                }
            }

            $row = $this->retrieveTemplateDocuments($row);
            $recordModel->setRawData($row);

            foreach ($row as $key => $value) {
                if ($key == "module") {
                    $value = vtranslate($value, $value);
                }

                if (in_array($key, $this->listViewColumns)) {
                    $value = textlength_check($value);
                }

                $row[$key] = $value;
            }
            $listViewRecordModels[$row['templateid']] = $recordModel->setData($row);
        }

        $pagingModel->calculatePageRange($listViewRecordModels);
        $pagingModel->set('nextPageExists', false);

        return $listViewRecordModels;
    }

    /**
     * @param array $data
     * @return array
     */
    public function retrieveTemplateDocuments(array $data): array
    {
        $documents = [];
        $EMAILMakerUtils = new EMAILMaker_EMAILContentUtils_Model();
        $documentIds = $EMAILMakerUtils->getAttachmentsForId($data['templateid']);

        foreach ($documentIds as $documentId) {
            $document = Vtiger_Record_Model::getInstanceById($documentId, 'Documents');

            if ($document) {
                $documents[$documentId] = $document->getData();
            }
        }

        $data['document_records'] = $documents;

        return $data;
    }


    public function getQuery()
    {
        $listQuery = 'SELECT templateid,' . implode(',', $this->querySelectColumns) . ' FROM vtiger_emakertemplates
						LEFT JOIN vtiger_tab ON vtiger_tab.name = vtiger_emakertemplates.module
						AND (vtiger_tab.isentitytype=1 or vtiger_tab.name = "Users") ';
        return $listQuery;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getListViewCount()
    {
        $db = PearDatabase::getInstance();
        $queryGenerator = $this->get('query_generator');
        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
        }
        $listQuery = $this->getQuery();
        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            $moduleModel = $this->getModule();
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }

        $split = preg_split(' from ', $listQuery);

        if (1 < count($split)) {
            unset($split[0]);
            $listQuery = 'SELECT count(*) AS count ' . implode(' FROM ', $split);
        }

        $listResult = $db->pquery($listQuery, array());
        return $db->query_result($listResult, 0, 'count');
    }

    public function extendPopupFields($fieldsList)
    {
        $moduleModel = $this->get('module');
        $queryGenerator = $this->get('query_generator');
        $listFields = $moduleModel->getPopupFields();
        $listFields[] = 'id';
        $listFields = array_merge($listFields, $fieldsList);
        $queryGenerator->setFields($listFields);
        $this->get('query_generator', $queryGenerator);
    }
}
