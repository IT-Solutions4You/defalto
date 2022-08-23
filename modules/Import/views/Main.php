<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'vtlib/Vtiger/Cron.php';

class Import_Main_View extends Vtiger_View_Controller{

	var $request;
	var $user;
	var $numberOfRecords;

	public function process(Vtiger_Request $request) {
		return;
	}

	public function  __construct($request, $user) {
		$this->request = $request;
		$this->user = $user;
	}

	public static function import($request, $user) {
		$importController = new Import_Main_View($request, $user);

		$importController->saveMap();
		$fileReadStatus = $importController->copyFromFileToDB();
		if($fileReadStatus) {
			$importController->queueDataImport();
		}

		$isImportScheduled = $importController->request->get('is_scheduled');
                $enableCron = $importController->request->get('enable_cron');
		if($isImportScheduled) {
			$importInfo = Import_Queue_Action::getUserCurrentImportInfo($importController->user);
			self::showScheduledStatus($importInfo, $enableCron);
		} else {
			$importController->triggerImport();
		}
	}

	public function triggerImport($batchImport=false) {
		$importInfo = Import_Queue_Action::getImportInfo($this->request->get('module'), $this->user);
		$importDataController = new Import_Data_Action($importInfo, $this->user);

		if(!$batchImport) {
			if(!$importDataController->initializeImport()) {
				Import_Utils_Helper::showErrorPage(vtranslate('ERR_FAILED_TO_LOCK_MODULE', 'Import'));
				exit;
			}
		}

		$importDataController->importData();
		Import_Queue_Action::updateStatus($importInfo['id'], Import_Queue_Action::$IMPORT_STATUS_HALTED);
		$importInfo = Import_Queue_Action::getImportInfo($this->request->get('module'), $this->user);

		self::showImportStatus($importInfo, $this->user);
	}

	public static function showImportStatus($importInfo, $user) {
		if($importInfo == null) {
			Import_Utils_Helper::showErrorPage(vtranslate('ERR_IMPORT_INTERRUPTED', 'Import'));
			exit;
		}
		$importDataController = new Import_Data_Action($importInfo, $user);
		if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_HALTED ||
				$importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_NONE) {
			$continueImport = true;
		} else {
			$continueImport = false;
		}

		$focus = CRMEntity::getInstance($importInfo['module']);
		if(method_exists($focus, 'getImportStatusCount')) {
			$importStatusCount = $focus->getImportStatusCount($importDataController);
		} else {
			$importStatusCount = $importDataController->getImportStatusCount();
		}
		$totalRecords = $importStatusCount['TOTAL'];
		if($totalRecords > ($importStatusCount['IMPORTED'] + $importStatusCount['FAILED'])) {
//			if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_SCHEDULED) {
//				self::showScheduledStatus($importInfo);
//				exit;
//			}
			self::showCurrentStatus($importInfo, $importStatusCount, $continueImport);
			exit;
		} else {
			$importDataController->finishImport();
			self::showResult($importInfo, $importStatusCount);
		}
	}

	public static function showCurrentStatus($importInfo, $importStatusCount, $continueImport) {
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];

		$params = array('module' => $moduleName);
		$request = new Vtiger_Request($params);
		$indexViewer = new Vtiger_Index_View();
		$viewer = $indexViewer->getViewer($request);  

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('IMPORT_ID', $importId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$inventoryModules = getInventoryModules();
		array_push($inventoryModules, 'Users');
		$viewer->assign('INVENTORY_MODULES', $inventoryModules);
		$viewer->assign('CONTINUE_IMPORT', $continueImport);
		$viewer->assign('JS_SCRIPTS', $indexViewer->getHeaderScripts($request));
		$viewer->view('ImportStatus.tpl', 'Import');
	}

	public static function showResult($importInfo, $importStatusCount) {
		$moduleName = $importInfo['module'];
		$ownerId = $importInfo['user_id'];

		$viewer = new Vtiger_Viewer();

		$viewer->assign('SKIPPED_RECORDS',$skippedRecords);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('OWNER_ID', $ownerId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$inventoryModules = getInventoryModules();
		array_push($inventoryModules, 'Users');
		$viewer->assign('INVENTORY_MODULES', $inventoryModules);
		$viewer->assign('MERGE_ENABLED', $importInfo['merge_type']);

		$viewer->view('ImportResult.tpl', 'Import');
	}

	public static function showScheduledStatus($importInfo, $enableCronStatus) {
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];

		$viewer = new Vtiger_Viewer();
                $viewer->assign('ENABLE_SCHEDULE_IMPORT_CRON', $enableCronStatus);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('IMPORT_ID', $importId);

		$viewer->view('ImportSchedule.tpl', 'Import');
	}

	public function saveMap() {
		$saveMap = $this->request->get('save_map');
		$mapName = $this->request->get('save_map_as');
		if($saveMap && !empty($mapName)) {
			$fieldMapping = $this->request->get('field_mapping');
			$fileReader = Import_Utils_Helper::getFileReader($this->request, $this->user);
			if($fileReader == null) {
				return false;
			}
			$hasHeader = $fileReader->hasHeader();
			if($hasHeader) {
				$firstRowData = $fileReader->getFirstRowData($hasHeader);
				$headers = array_keys($firstRowData);
				foreach($fieldMapping as $fieldName => $index) {
					$saveMapping["$headers[$index]"] = $fieldName;
				}
			} else {
				$saveMapping = array_flip($fieldMapping);
			}

			$map = array();
			$map['name'] = $mapName;
			$map['content'] = $saveMapping;
			$map['module'] = $this->request->get('module');
			$map['has_header'] = ($hasHeader)?1:0;
			$map['assigned_user_id'] = $this->user->id;

			$importMap = new Import_Map_Model($map, $this->user);
			$importMap->save();
		}
	}

	public function copyFromFileToDB() {
		$fileReader = Import_Utils_Helper::getFileReader($this->request, $this->user);
		$fileReader->read();
		$fileReader->deleteFile();
		if($fileReader->getStatus() == 'success') {
			$this->numberOfRecords = $fileReader->getNumberOfRecordsRead();
			return true;
		} else {
			Import_Utils_Helper::showErrorPage(vtranslate('ERR_FILE_READ_FAILED', 'Import').' - '.vtranslate($fileReader->getErrorMessage(), 'Import'));
			return false;
		}
	}

	public function queueDataImport() {
		$configReader = new Import_Config_Model();
		$immediateImportRecordLimit = $configReader->get('immediateImportLimit');
		$pagingLimit  = $configReader->get('importPagingLimit');

                $cronTasks = Vtiger_Cron::listAllInstancesByModule('Import');
                $importCronTask = $cronTasks[0];
                if(!empty($importCronTask)){
                    $cronStatus = $importCronTask->getStatus();
                    $enableCron = false;
                    if(empty($cronStatus)){
                        $enableCron = true;
                    }
                    $this->request->set('enable_cron', $enableCron);
                }
		$numberOfRecordsToImport = $this->numberOfRecords;
		if($numberOfRecordsToImport > $immediateImportRecordLimit) {
			$this->request->set('is_scheduled', true);
		}
		if($numberOfRecordsToImport > $pagingLimit){
			$this->request->set('paging_enabled', true);
		}
		Import_Queue_Action::add($this->request, $this->user);
	}

	public static function deleteMap($request) {
		$moduleName = $request->getModule();
		$mapId = $request->get('mapid');
		if(!empty($mapId)) {
			Import_Map_Model::markAsDeleted($mapId);
		}

		$viewer = new Vtiger_Viewer();
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('SAVED_MAPS', Import_Map_Model::getAllByModule($moduleName));
		$viewer->view('Import_Saved_Maps.tpl', 'Import');
	}

	static function updateMap($request) {
		$moduleName = $request->getModule();
		$mapId = $request->get('mapid');
		if (!empty($mapId)) {
			$mapping = $request->get('mapping');
			Import_Map_Model::updateMap($mapId, $mapping);
		}
		$viewer = new Vtiger_Viewer();
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('SAVED_MAPS', Import_Map_Model::getAllByModule($moduleName));
		$viewer->assign('SELECTED_MAP_ID', $mapId);
		$viewer->view('Import_Saved_Maps.tpl', 'Import');
	}
}
?>