<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Settings_MenuEditor_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

    public function addModule(Vtiger_Request $request)
    {
        $sourceModules = [$request->get('sourceModule')];

        if ($request->has('sourceModules')) {
            $sourceModules = $request->get('sourceModules');
        }

        $appName = $request->get('appname');

        foreach ($sourceModules as $sourceModule) {
            Settings_MenuEditor_Module_Model::addModuleToApp($sourceModule, $appName);
            Settings_MenuEditor_Module_Model::setVisible(getTabid($sourceModule), $appName, 1);
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->get('mode');

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function removeModule(Vtiger_Request $request)
    {
        $sourceModule = $request->get('sourceModule');
        $appName = $request->get('appname');

        Settings_MenuEditor_Module_Model::setVisible(getTabid($sourceModule), $appName, 0);

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    public function saveSequence(Vtiger_Request $request)
    {
        $moduleSequence = $request->get('sequence');
        $appName = $request->get('appname');

        foreach ($moduleSequence as $moduleName => $sequence) {
            Settings_MenuEditor_Module_Model::setSequence($moduleName, $appName, $sequence);
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('removeModule');
        $this->exposeMethod('addModule');
        $this->exposeMethod('saveSequence');
    }

}