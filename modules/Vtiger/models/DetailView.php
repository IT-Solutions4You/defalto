<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */
class Vtiger_DetailView_Model extends Vtiger_Base_Model {

	protected $module = false;
	protected $record = false;

	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 * Function to get the Record model
	 * @return <Vtiger_Record_Model>
	 */
	public function getRecord() {
		return $this->record;
	}

	/**
	 * Function to set the record instance3
	 * @param <type> $recordModuleInstance - record model
	 * @return Vtiger_DetailView_Model
	 */
	public function setRecord($recordModuleInstance) {
		$this->record = $recordModuleInstance;
		return $this;
	}

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC','DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

        $detailViewLinks = array();
		$linkModelList = array();

        if($moduleModel->isShowMapSupported()) {
            $detailViewLinks[] = array(
                'linktype' => 'DETAILVIEWBASIC',
                'linklabel' => 'LBL_SHOW_MAP',
                'linkurl' => sprintf('Vtiger_Index_Js.showMap(this, "%s", "%d");', $moduleName, $recordId),
                'linkicon' => '<i class="fa fa-map-marker"></i>'
            );
        }

		if(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)) {
			$detailViewLinks[] = array(
					'linktype' => 'DETAILVIEWBASIC',
					'linklabel' => 'LBL_EDIT',
					'linkurl' => $recordModel->getEditViewUrl(),
					'linkicon' => '<i class="fa fa-pencil"></i>'
			);
		}


        foreach ($detailViewLinks as $detailViewLink) {
            $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
        }

		if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
			$deletelinkModel = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => sprintf("%s %s", getTranslatedString('LBL_DELETE', $moduleName), vtranslate('SINGLE_'. $moduleName, $moduleName)),
					'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
					'linkicon' => '<i class="fa-solid fa-trash"></i>'
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}

		if($moduleModel->isDuplicateOptionAllowed('CreateView', $recordId)) {
			$duplicateLinkModel = array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_DUPLICATE',
						'linkurl' => $recordModel->getDuplicateRecordUrl(),
						'linkicon' => '<i class="fa-solid fa-copy"></i>'
				);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}

		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(),$linkTypes,$linkParams);
		foreach($linkTypes as $linkType) {
			if(!empty($linkModelListDetails[$linkType])) {
				foreach($linkModelListDetails[$linkType] as $linkModel) {
					// Remove view history, needed in vtiger5 to see history but not in vtiger6
					if($linkModel->linklabel == 'View History') {
						continue;
					}
					$linkModelList[$linkType][] = $linkModel;
				}
			}
			unset($linkModelListDetails[$linkType]);
		}

		$relatedLinks = $this->getDetailViewRelatedLinks();

		foreach($relatedLinks as $relatedLinkEntry) {
			$relatedLink = Vtiger_Link_Model::getInstanceFromValues($relatedLinkEntry);
			$linkModelList[$relatedLink->getType()][] = $relatedLink;
		}

		$widgets = $this->getWidgets();
		foreach($widgets as $widgetLinkModel) {
			$linkModelList['DETAILVIEWWIDGET'][] = $widgetLinkModel;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($currentUserModel->isAdminUser()) {
			$settingsLinks = $moduleModel->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$linkModelList['DETAILVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

        if ($currentUserModel->isAdminUser() || $currentUserModel->getId() === $recordModel->get('assigned_user_id')) {
            if (Core_Readonly_Model::isButtonPermitted($moduleName, $recordId)) {
                $linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues(
                    [
                        'linktype' => 'DETAILVIEWBASIC',
                        'linklabel' => 'LBL_MAKE_EDITABLE',
                        'linkurl' => $recordModel->getEditableUrl(),
                        'linkicon' => '<i class="fa-solid fa-eye-low-vision"></i>',
                    ]
                );
            } else {
                $linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues(
                    [
                        'linktype' => 'DETAILVIEW',
                        'linklabel' => 'LBL_MAKE_READONLY',
                        'linkurl' => $recordModel->getReadonlyUrl(),
                        'linkicon' => '<i class="fa-solid fa-eye-low-vision"></i>',
                    ]
                );
            }
        }

        return $linkModelList;
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
        $relatedLinks = array();

        if ($parentModuleModel->isSummaryViewSupported()) {
            $relatedLinks = array(
                array(
                    'linktype' => 'DETAILVIEWTAB',
                    'linklabel' => vtranslate('LBL_SUMMARY', $moduleName),
                    'linkKey' => 'LBL_RECORD_SUMMARY',
                    'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
                    'linkicon' => '<i class="fa-solid fa-lightbulb"></i>',
                ),
            );
        }

        $relatedLinks[] = array(
            'linktype' => 'DETAILVIEWTAB',
            'linklabel' => vtranslate('LBL_DETAILS', $moduleName),
            'linkKey' => 'LBL_RECORD_DETAILS',
            'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
            'linkicon' => '<i class="fa-solid fa-circle-info"></i>',
        );

        if ($parentModuleModel->isTrackingEnabled()) {
            $relatedLinks[] = array(
                'linktype' => 'DETAILVIEWTAB',
                'linklabel' => 'LBL_UPDATES',
                'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
                'linkicon' => '<i class="fa-solid fa-arrows-rotate"></i>',
            );
        }

        $relatedLinks[] = [
            'linktype' => 'DETAILVIEWTAB',
            'linklabel' => 'LBL_SHARING_RECORD',
            'linkurl' => $recordModel->getDetailViewUrl() . '&mode=DetailSharingRecord',
            'linkicon' => '<i class="fa-solid fa-share-nodes"></i>',
        ];

        $relationModels = $parentModuleModel->getRelations();

        foreach ($relationModels as $relation) {
            $link = array(
                'linktype' => 'DETAILVIEWRELATED',
                'linklabel' => $relation->get('label'),
                'linkurl' => $relation->getListUrl($recordModel),
                'linkicon' => '',
                'relatedModuleName' => $relation->get('relatedModuleName'),
                'linkid' => $relation->getId(),
            );
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
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'LBL_UPDATES',
            'linkurl' => $this->getTrackingWidgetInfo(),
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
        return [
            'linktype' => 'DETAILVIEWWIDGET',
            'linklabel' => 'ModComments',
            'linkurl' => $this->getCommentWidgetUrl(),
        ];
    }

    public function getPlaceholderWidgetInfo(): array
    {
        return [
            'linktype' => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryPlaceholder.tpl',
        ];
    }

    public function getKeyFieldsWidgetInfo(): array
    {
        return [
            'linktype' => 'DETAILVIEWWIDGET',
            'link_template' => 'SummaryKeyFields.tpl'
        ];
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

        $appointmentsInstance = Vtiger_Module_Model::getInstance('Appointments');

        if ($userPrivilegesModel->hasModuleActionPermission($appointmentsInstance->getId(), 'DetailView') && $moduleModel->isModuleRelated('Appointments')) {
            $createPermission = $userPrivilegesModel->hasModuleActionPermission($appointmentsInstance->getId(), 'CreateView');
            $widgets[] = [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'Appointments',
                'linkName' => $appointmentsInstance->getName(),
                'linkurl' => $this->getWidgetUrl('getEvents'),
                'action' => $createPermission ? ['Add'] : [],
                'actionURL' => $appointmentsInstance->getQuickCreateUrl(),
            ];
        } else {
            $widgets[] = $this->getPlaceholderWidgetInfo();
        }

        $documentsInstance = Vtiger_Module_Model::getInstance('Documents');

        if ($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView') && $moduleModel->isModuleRelated('Documents')) {
            $createPermission = $userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'CreateView');
            $widgets[] = [
                'linktype' => 'DETAILVIEWWIDGET',
                'linklabel' => 'Documents',
                'linkName' => $documentsInstance->getName(),
                'linkurl' => $this->getWidgetUrl('showRelatedRecords') . '&relatedModule=Documents',
                'action' => $createPermission ? ['Add'] : [],
                'actionURL' => $documentsInstance->getQuickCreateUrl(),
            ];
        } else {
            $widgets[] = $this->getPlaceholderWidgetInfo();
        }

        $modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');

        if ($moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
            $widgets[] = $this->getCommentWidgetInfo();
        } else {
            $widgets[] = $this->getPlaceholderWidgetInfo();
        }

        $widgetLinks = [];

        foreach ($widgets as $widgetDetails) {
            $widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
        }

        return $widgetLinks;
    }

    /**
     * Function to get the Quick Links for the Detail view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);

		$listLinkTypes = array('DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET');
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if($listLinks['DETAILVIEWSIDEBARLINK']) {
			foreach($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
				$link->linkurl = $link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName();
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if($currentUser->getTagCloudStatus()) {
			$tagWidget = array(
				'linktype' => 'DETAILVIEWSIDEBARWIDGET',
				'linklabel' => 'LBL_TAG_CLOUD',
				'linkurl' => 'module='.$this->getModule()->getName().'&view=ShowTagCloud&mode=showTags',
				'linkicon' => '',
			);
			$linkModel = Vtiger_Link_Model::getInstanceFromValues($tagWidget);
			if($listLinks['DETAILVIEWSIDEBARWIDGET']) array_push($listLinks['DETAILVIEWSIDEBARWIDGET'], $linkModel);
			else $listLinks['DETAILVIEWSIDEBARWIDGET'][] = $linkModel;
		}

		if($listLinks['DETAILVIEWSIDEBARWIDGET']) {
			foreach($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
				$link->linkurl = $link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName();
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}

		return $moduleLinks;
	}

	/**
	 * Function to get the module label
	 * @return <String> - label
	 */
	public function getModuleLabel() {
		return $this->getModule()->get('label');
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName() {
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @param <String> $recordId - record id
	 * @return <Vtiger_DetailView_Model>
	 */
	public static function getInstance($moduleName,$recordId) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		return $instance->setModule($moduleModel)->setRecord($recordModel);
	}
}
