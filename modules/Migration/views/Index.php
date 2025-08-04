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

class Migration_Index_View extends Vtiger_View_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('step1');
        $this->exposeMethod('step2');
        $this->exposeMethod('applyDBChanges');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        parent::checkPermission($request);
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        // Override error reporting to production mode
        // error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        // Migration could be heavy at-times.
        set_time_limit(0);

        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }

        $this->step1($request);
    }

    protected function step1(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);

        $viewer->assign('MODULENAME', $moduleName);
        $viewer->view('MigrationStep1.tpl', $moduleName);
    }

    protected function step2(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $viewer = $this->getViewer($request);

        $viewer->assign('MODULE', $moduleName);
        $viewer->view('MigrationStep2.tpl', $moduleName);
    }

    public function preProcess(Vtiger_Request $request, $display = true)
    {
        parent::preProcess($request, false);

        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEW', 'Index');
        $viewer->view('MigrationPreProcess.tpl', $moduleName);
    }

    public function postProcess(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('VIEW', 'Index');
        $viewer->view('MigrationPostProcess.tpl', $moduleName);
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = [];
        $moduleName = $request->getModule();

        $jsFileNames = [
            'modules.Vtiger.resources.Popup',
            "modules.Vtiger.resources.List",
            "modules.$moduleName.resources.Index"
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        return $headerScriptInstances;
    }

    /**
     * @throws Exception
     */
    public function applyDBChanges()
    {
        vglobal('debug', true);

        $migrationModuleModel = Migration_Module_Model::getInstance();
        $reach = null;
        $getAllowedMigrationVersions = $migrationModuleModel->getAllowedMigrationVersions();
        $getDBVersion = str_replace(['.', ' '], '', $migrationModuleModel->getDBVersion());
        $getLatestSourceVersion = str_replace(['.', ' '], '', $migrationModuleModel->getLatestSourceVersion());
        $migrateVersions = [];

        foreach ($getAllowedMigrationVersions as $getAllowedMigrationVersion) {
            foreach ($getAllowedMigrationVersion as $version => $label) {
                if (strcasecmp($version, $getDBVersion) == 0 || $reach == 1) {
                    $reach = 1;
                    $migrateVersions[] = $version;
                }
            }
        }

        $migrateVersions[] = $getLatestSourceVersion;

        $patchCount = php7_count($migrateVersions);

        define('VTIGER_UPGRADE', $getDBVersion);

        for ($i = 0; $i < $patchCount; $i++) {
            $filename = "modules/Migration/schema/" . $migrateVersions[$i] . "_to_" . $migrateVersions[$i + 1] . ".php";
            if (is_file($filename)) {
                if (!defined('INSTALLATION_MODE')) {
                    echo "<table class='config-table'><tr><th><span><b><font color='red'>" . $migrateVersions[$i] . " ==> " . $migrateVersions[$i + 1] . " Database changes -- Starts. </font></b></span></th></tr></table>";
                    echo "<table class='config-table'>";
                }
                $_i_statesaved = $i;
                include($filename);
                $i = $_i_statesaved;
                if (!defined('INSTALLATION_MODE')) {
                    echo "<table class='config-table'><tr><th><span><b><font color='red'>" . $migrateVersions[$i] . " ==> " . $migrateVersions[$i + 1] . " Database changes -- Ends.</font></b></span></th></tr></table>";
                }
            } elseif (isset($migrateVersions[$patchCount + 1])) {
                echo "<table class='config-table'><tr><th><span><b><font color='red'> There is no Database Changes from " . $migrateVersions[$i] . " ==> " . $migrateVersions[$i + 1] . "</font></b></span></th></tr></table>";
            }
        }

        Install_Utils_Model::installTables();

        Install_Utils_Model::installAdditionalModulesAndLanguages();

        Install_Utils_Model::installMigrations();

        //update vtiger version in db
        $migrationModuleModel->updateVtigerVersion();
        // To carry out all the necessary actions after migration
        $migrationModuleModel->postMigrateActivities();
    }

    public static function ExecuteQuery($query, $params)
    {
        $adb = PearDatabase::getInstance();
        $status = $adb->pquery($query, $params);
        if (!defined('INSTALLATION_MODE')) {
            $query = $adb->convert2sql($query, $params);
            if (is_object($status)) {
                echo '<tr><td width="80%"><span>' . $query . '</span></td><td style="color:green">Success</td></tr>';
            } else {
                echo '<tr><td width="80%"><span>' . $query . '</span></td><td style="color:red">Failure</td></tr>';
            }
        }

        return $status;
    }

    /**
     * Function to transform workflow filter of old look in to new look
     *
     * @param <type> $conditions
     *
     * @return <type>
     */
    public static function transformAdvanceFilterToWorkFlowFilter($conditions)
    {
        $wfCondition = [];

        if (!empty($conditions)) {
            $previousConditionGroupId = 0;
            foreach ($conditions as $condition) {
                $fieldName = $condition['fieldname'];
                $fieldNameContents = explode(' ', $fieldName);
                if (php7_count($fieldNameContents) > 1) {
                    $fieldName = '(' . $fieldName . ')';
                }

                $groupId = $condition['groupid'] ?? null;

                if (!$groupId) {
                    $groupId = 0;
                }

                $groupCondition = 'or';
                if ($groupId === $previousConditionGroupId || php7_count($conditions) === 1) {
                    $groupCondition = 'and';
                }

                $joinCondition = 'or';
                if (isset ($condition['joincondition'])) {
                    $joinCondition = $condition['joincondition'];
                } elseif ($groupId === 0) {
                    $joinCondition = 'and';
                }

                $value = $condition['value'];
                switch ($value) {
                    case 'false:boolean'    :
                        $value = 0;
                        break;
                    case 'true:boolean'        :
                        $value = 1;
                        break;
                    default                    :
                        $value;
                        break;
                }

                $wfCondition[] = [
                    'fieldname'     => $fieldName,
                    'operation'     => $condition['operation'],
                    'value'         => $value,
                    'valuetype'     => 'rawtext',
                    'joincondition' => $joinCondition,
                    'groupjoin'     => $groupCondition,
                    'groupid'       => $groupId
                ];
                $previousConditionGroupId = $groupId;
            }
        }

        return $wfCondition;
    }
}