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

class Vtiger_DetailView_Model extends Vtiger_Base_Model
{
    public const LINK_ADVANCED = 'DETAILVIEWADVANCED';
    public const LINK_BASIC = 'DETAILVIEWBASIC';
    public const LINK_MORE = 'DETAILVIEW';
    public const LINK_RECORD = 'DETAILVIEWRECORD';
    public array $skipDetailLinkByLabel = [
        'View History',
    ];

    protected $module = false;
    protected $record = false;

    /**
     * Function to get Module instance
     * @return Vtiger_Module_Model
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Function to set the module instance
     *
     * @param <Vtiger_Module_Model> $moduleInstance - module model
     *
     * @return Vtiger_DetailView_Model>
     */
    public function setModule($moduleInstance)
    {
        $this->module = $moduleInstance;

        return $this;
    }

    /**
     * Function to get the Record model
     * @return Vtiger_Record_Model
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Function to set the record instance3
     *
     * @param <type> $recordModuleInstance - record model
     *
     * @return Vtiger_DetailView_Model
     */
    public function setRecord($recordModuleInstance)
    {
        $this->record = $recordModuleInstance;

        return $this;
    }

    /**
     * Function to get the detail view links (links and widgets)
     *
     * @param <array> $linkParams - parameters which will be used to calicaulate the params
     *
     * @return <array> - array of link models in the format as below
     *                   array('linktype'=>list of link models);
     */
    public function getDetailViewLinks($linkParams)
    {
        $linkTypes = [Vtiger_DetailView_Model::LINK_BASIC, Vtiger_DetailView_Model::LINK_MORE];
        $moduleModel = $this->getModule();
        $recordModel = $this->getRecord();

        $moduleName = $moduleModel->getName();
        $recordId = $recordModel->getId();
        $links = [];

        if ($moduleModel->isShowMapSupported()) {
            $links[] = [
                'linktype'  => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_SHOW_MAP',
                'linkurl'   => sprintf('Vtiger_Index_Js.showMap(this, "%s", "%d");', $moduleName, $recordId),
                'linkicon'  => '<i class="fa fa-map-marker"></i>',
            ];
        }

        if ($recordModel->isEditable()) {
            $links[] = [
                'linktype'  => 'DETAILVIEWRECORD',
                'linklabel' => 'LBL_EDIT',
                'linkurl'   => $recordModel->getEditViewUrl(),
                'linkicon'  => '<i class="fa fa-pencil"></i>',
            ];
        }

        if ($recordModel->isDeletable()) {
            $links[] = [
                'linktype'  => 'DETAILVIEWRECORD',
                'linklabel' => vtranslate('LBL_DELETE', $moduleName),
                'linkurl'   => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
                'linkicon'  => '<i class="fa-solid fa-trash"></i>',
            ];
        }

        if ($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
            $links[] = [
                'linktype'  => 'DETAILVIEWRECORD',
                'linklabel' => 'LBL_DUPLICATE',
                'linkurl'   => $recordModel->getDuplicateRecordUrl(),
                'linkicon'  => '<i class="fa-solid fa-copy"></i>',
            ];
        }

        if ($moduleModel->isCreateOptionAllowed()) {
            $links[] = [
                'linktype'  => 'DETAILVIEWRECORD',
                'linklabel' => 'LBL_ADD_RECORD',
                'linkurl'   => $moduleModel->getCreateRecordUrl(),
                'linkicon'  => '<i class="fa-solid fa-plus"></i>',
            ];
        }

        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

        foreach ($linkModelListDetails as $linkModelListDetail) {
            foreach ($linkModelListDetail as $linkModel) {
                $links[] = $linkModel;
            }
        }

        $links[] = $this->getTagsLinkInfo();

        $relatedLinks = $this->getDetailViewRelatedLinks();

        foreach ($relatedLinks as $relatedLinkEntry) {
            $links[] = $relatedLinkEntry;
        }

        $widgets = $this->getWidgets();

        foreach ($widgets as $widgetLinkModel) {
            $links[] = $widgetLinkModel;
        }

        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if ($currentUserModel->isAdminUser()) {
            $settingsLinks = $moduleModel->getSettingLinks();

            foreach ($settingsLinks as $settingsLink) {
                $links[] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
            }
        }

        if ($currentUserModel->isAdminUser() || $currentUserModel->getId() === $recordModel->get('assigned_user_id')) {
            if (Core_Readonly_Model::isButtonPermitted($moduleName, $recordId)) {
                $links[] = Vtiger_Link_Model::getInstanceFromValues(
                    [
                        'linktype'  => 'DETAILVIEWADVANCED',
                        'linklabel' => 'LBL_MAKE_EDITABLE',
                        'linkurl'   => $recordModel->getEditableUrl(),
                        'linkicon'  => '<i class="fa-solid fa-eye-low-vision"></i>',
                    ]
                );
            } else {
                $links[] = Vtiger_Link_Model::getInstanceFromValues(
                    [
                        'linktype'  => 'DETAILVIEW',
                        'linklabel' => 'LBL_MAKE_READONLY',
                        'linkurl'   => $recordModel->getReadonlyUrl(),
                        'linkicon'  => '<i class="fa-solid fa-eye-low-vision"></i>',
                    ]
                );
            }
        }

        return Vtiger_Link_Model::checkAndConvertLinks($links, $this->skipDetailLinkByLabel);
    }

    /**
     * Function to get the detail view related links
     * @return <array> - list of links parameters
     */
    public function getDetailViewRelatedLinks()
    {
        $recordModel = $this->getRecord();
        $moduleName = $recordModel->getModuleName();
        $parentModuleModel = $this->getModule();
        $relatedLinks = [];

        if ($parentModuleModel->isSummaryViewSupported()) {
            $relatedLinks = [
                [
                    'linktype'  => 'DETAILVIEWTAB',
                    'linklabel' => vtranslate('LBL_SUMMARY', $moduleName),
                    'linkKey'   => 'LBL_RECORD_SUMMARY',
                    'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
                    'linkicon'  => '<i class="fa-solid fa-lightbulb"></i>',
                ],
            ];
        }

        $relatedLinks[] = [
            'linktype'  => 'DETAILVIEWTAB',
            'linklabel' => vtranslate('LBL_DETAILS', $moduleName),
            'linkKey'   => 'LBL_RECORD_DETAILS',
            'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
            'linkicon'  => '<i class="fa-solid fa-circle-info"></i>',
        ];

        if ($parentModuleModel->isTrackingEnabled()) {
            $relatedLinks[] = [
                'linktype'  => 'DETAILVIEWTAB',
                'linklabel' => 'LBL_UPDATES',
                'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
                'linkicon'  => '<i class="fa-solid fa-arrows-rotate"></i>',
            ];
        }

        $relatedLinks[] = [
            'linktype'  => 'DETAILVIEWTAB',
            'linklabel' => 'LBL_SHARING_RECORD',
            'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=DetailSharingRecord',
            'linkicon'  => '<i class="fa-solid fa-share-nodes"></i>',
        ];

        $relationModels = $parentModuleModel->getRelations();

        foreach ($relationModels as $relation) {
            $link = [
                'linktype'          => 'DETAILVIEWRELATED',
                'linklabel'         => $relation->get('label'),
                'linkurl'           => $relation->getListUrl($recordModel),
                'linkicon'          => '',
                'relatedModuleName' => $relation->get('relatedModuleName'),
                'linkid'            => $relation->getId(),
            ];
            $relatedLinks[] = $link;
        }

        return $relatedLinks;
    }

    /**
     * @return string
     */
    public function getTrackingWidgetUrl(): string
    {
        return $this->getWidgetUrl('showRecentActivities');
    }

    /**
     * @return array
     */
    public function getTrackingWidgetInfo(): array
    {
        return [
            'linktype'  => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_UPDATES',
            'linkurl'   => $this->getTrackingWidgetInfo(),
        ];
    }

    public function getWidgetUrl(string $mode): string
    {
        return sprintf('module=%s&view=Detail&record=%d&mode=%s&page=1&limit=5', $this->getModuleName(), $this->getRecord()->getId(), $mode);
    }

    public function getCommentWidgetUrl(): string
    {
        return $this->getWidgetUrl('showRecentComments');
    }

    public function getCommentWidgetInfo()
    {
        $moduleModel = $this->getModule();
        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');

        if ($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
            return [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'ModComments',
                'linkurl' => $this->getCommentWidgetUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    public function getPlaceholderWidgetInfo(): array
    {
        return [
            'linktype'      => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryPlaceholder.tpl',
        ];
    }

    public function getKeyFieldsWidgetInfo(): array
    {
        return [
            'linklabel'     => 'LBL_KEY_FIELDS',
            'linktype'      => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryKeyFields.tpl'
        ];
    }

    public function getAppointmentsWidgetInfo(): array
    {
        $moduleModel = $this->getModule();
        $appointmentsInstance = Vtiger_Module_Model::getInstance('Appointments');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($userPrivilegesModel->hasModuleActionPermission($appointmentsInstance->getId(), 'DetailView') && $moduleModel->isModuleRelated('Appointments')) {
            return [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'Appointments',
                'linkName' => $appointmentsInstance->getName(),
                'linkurl' => $this->getWidgetUrl('getEvents'),
                'action' => $userPrivilegesModel->hasModuleActionPermission($appointmentsInstance->getId(), 'CreateView') ? ['Add'] : [],
                'actionURL' => $appointmentsInstance->getQuickCreateUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    public function getDocumentsWidgetInfo(): array
    {
        $moduleModel = $this->getModule();
        $documentsInstance = Vtiger_Module_Model::getInstance('Documents');
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        if ($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView') && $moduleModel->isModuleRelated('Documents')) {
            return [
                'linktype'  => 'DETAILVIEWWIDGET',
                'linklabel' => 'Documents',
                'linkName'  => $documentsInstance->getName(),
                'linkurl'   => $this->getWidgetUrl('showRelatedRecords') . '&relatedModule=Documents',
                'action'    => $userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'CreateView') ? ['Add'] : [],
                'actionURL' => $documentsInstance->getQuickCreateUrl(),
            ];
        }

        return $this->getPlaceholderWidgetInfo();
    }

    /**
     * Function to get the detail view widgets
     * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
     */
    public function getWidgets()
    {
        $moduleModel = $this->getModule();
        $userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $widgets = [];
        $widgets[] = $this->getKeyFieldsWidgetInfo();
        $widgets[] = $this->getAppointmentsWidgetInfo();
        $widgets[] = $this->getDocumentsWidgetInfo();
        $widgets[] = $this->getCommentWidgetInfo();

        $widgetLinks = [];

        foreach ($widgets as $widgetDetails) {
            $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
        }

        return $widgetLinks;
    }

    /**
     * Function to get the Quick Links for the Detail view of the module
     *
     * @param <Array> $linkParams
     *
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $linkTypes = ['SIDEBARLINK', 'SIDEBARWIDGET'];
        $moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);

        $listLinkTypes = ['DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET'];
        $listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

        if ($listLinks['DETAILVIEWSIDEBARLINK']) {
            foreach ($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
                $link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
                $moduleLinks['SIDEBARLINK'][] = $link;
            }
        }

        if ($currentUser->getTagCloudStatus()) {
            $tagWidget = [
                'linktype'  => 'DETAILVIEWSIDEBARWIDGET',
                'linklabel' => 'LBL_TAG_CLOUD',
                'linkurl'   => 'module=' . $this->getModule()->getName() . '&view=ShowTagCloud&mode=showTags',
                'linkicon'  => '',
            ];
            $linkModel = Vtiger_Link_Model::getInstanceFromValues($tagWidget);
            if ($listLinks['DETAILVIEWSIDEBARWIDGET']) {
                array_push($listLinks['DETAILVIEWSIDEBARWIDGET'], $linkModel);
            } else {
                $listLinks['DETAILVIEWSIDEBARWIDGET'][] = $linkModel;
            }
        }

        if ($listLinks['DETAILVIEWSIDEBARWIDGET']) {
            foreach ($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
                $link->linkurl = $link->linkurl . '&record=' . $this->getRecord()->getId() . '&source_module=' . $this->getModule()->getName();
                $moduleLinks['SIDEBARWIDGET'][] = $link;
            }
        }

        return $moduleLinks;
    }

    /**
     * Function to get the module label
     * @return <String> - label
     */
    public function getModuleLabel()
    {
        return $this->getModule()->get('label');
    }

    /**
     *  Function to get the module name
     * @return <String> - name of the module
     */
    public function getModuleName()
    {
        return $this->getModule()->get('name');
    }

    /**
     * Function to get the instance
     *
     * @param <String> $moduleName - module name
     * @param <String> $recordId   - record id
     *
     * @return <Vtiger_DetailView_Model>
     */
    public static function getInstance($moduleName, $recordId)
    {
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
        $instance = new $modelClassName();

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

        return $instance->setModule($moduleModel)->setRecord($recordModel);
    }

    /**
     * @return array
     */
    public function getTagsLinkInfo(): array
    {
        $record = $this->getRecord();

        return [
            'linktype'    => Vtiger_DetailView_Model::LINK_BASIC,
            'linklabel'   => 'LBL_ADD_TAG',
            'linkurl'     => sprintf('Vtiger_Index_Js.addTags(this, "%s", "%d");', $record->getModuleName(), $record->getId()),
            'linkicon'    => '<i class="fa fa-tag"></i>',
            'style_class' => 'text-secondary addTagsButton',
        ];
    }
}