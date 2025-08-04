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
 * PBXManager ListView Model Class
 */
class PBXManager_ListView_Model extends Vtiger_ListView_Model
{
    /**
     * Overrided to remove add button
     */
    public function getBasicLinks()
    {
        $basicLinks = [];

        return $basicLinks;
    }

    /**
     * Overrided to remove Mass Edit Option
     */
    public function getListViewMassActions($linkParams)
    {
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();

        $linkTypes = ['LISTVIEWMASSACTION'];
        $links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        if ($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
            $massActionLinks[] = [
                'linktype'  => 'LISTVIEWMASSACTION',
                'linklabel' => 'LBL_DELETE',
                'linkurl'   => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module=' . $moduleModel->get('name') . '&action=MassDelete");',
                'linkicon'  => ''
            ];

            foreach ($massActionLinks as $massActionLink) {
                $links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
            }
        }

        return $links;
    }

    /**
     * Overrided to add HTML content for callstatus irrespective of the filters
     */

    public function getListViewEntries($pagingModel)
    {
        $db = PearDatabase::getInstance();

        $moduleName = $this->getModule()->get('name');
        $moduleFocus = CRMEntity::getInstance($moduleName);
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $viewId = $this->getViewId($pagingModel);

        //Add the direction field to the query irrespective of filter
        $queryGenerator = $this->get('query_generator');
        $fields = $queryGenerator->getFields();
        array_push($fields, 'direction');
        $queryGenerator->setFields($fields);
        $this->set('query_generator', $queryGenerator);
        //END

        $listViewContoller = $this->get('listview_controller');

        $searchKey = $this->get('search_key');
        $searchValue = $this->get('search_value');
        $operator = $this->get('operator');
        if (!empty($searchKey)) {
            $queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
        }

        $this->retrieveOrderBy($viewId);
        $listQuery = $this->getQuery();

        $sourceModule = $this->get('src_module');
        if (!empty($sourceModule)) {
            if (method_exists($moduleModel, 'getQueryByModuleField')) {
                $overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
                if (!empty($overrideQuery)) {
                    $listQuery = $overrideQuery;
                }
            }
        }

        $startIndex = $pagingModel->getStartIndex();
        $pageLimit = $pagingModel->getPageLimit();

        $listQuery .= $this->getQueryGenerator()->getOrderByClause();

        ListViewSession::setSessionQuery($moduleName, $listQuery, $viewId);

        $listQuery .= " LIMIT $startIndex," . ($pageLimit + 1);

        $listResult = $db->pquery($listQuery, []);

        $listViewRecordModels = [];
        $listViewEntries = $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);

        $pagingModel->calculatePageRange($listViewEntries);

        if ($db->num_rows($listResult) > $pageLimit) {
            array_pop($listViewEntries);
            $pagingModel->set('nextPageExists', true);
        } else {
            $pagingModel->set('nextPageExists', false);
        }

        //Adding the HTML content based on the callstatus and direction to the records
        foreach ($listViewEntries as $recordId => $record) {
            //To Replace RecordingUrl by Icon
            $recordingUrl = explode('>', $listViewEntries[$recordId]['recordingurl']);
            $url = explode('<', $recordingUrl[1]);
            if ($url[0] != '' && $listViewEntries[$recordId]['callstatus'] == 'completed') {
                $listViewEntries[$recordId]['recordingurl'] = $recordingUrl[0] . '>' . '<i class="icon-volume-up"></i>' . '</a>';
            } else {
                $listViewEntries[$recordId]['recordingurl'] = '';
            }

            if ($listViewEntries[$recordId]['direction'] == 'outbound') {
                if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } elseif ($listViewEntries[$recordId]['callstatus'] == 'completed') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } elseif ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } else {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-up icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                }
            } elseif ($listViewEntries[$recordId]['direction'] == 'inbound') {
                if ($listViewEntries[$recordId]['callstatus'] == 'ringing' || $listViewEntries[$recordId]['callstatus'] == 'in-progress') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-info"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } elseif ($listViewEntries[$recordId]['callstatus'] == 'completed') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-success"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } elseif ($listViewEntries[$recordId]['callstatus'] == 'no-answer') {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-important"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                } else {
                    $listViewEntries[$recordId]['callstatus'] = '<span class="label label-warning"><i class="icon-arrow-down icon-white">
                        </i>&nbsp;' . $listViewEntries[$recordId]["callstatus"] . '</span>';
                }
            }
        }
        //END

        $index = 0;
        foreach ($listViewEntries as $recordId => $record) {
            $rawData = $db->query_result_rowdata($listResult, $index++);
            $record['id'] = $recordId;
            $listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
        }

        return $listViewRecordModels;
    }
}