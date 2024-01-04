<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Import_View extends Vtiger_Index_View
{

    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign("MODULE", $request->get('module'));
        $viewer->assign("MODULELABEL", vtranslate($request->get('module'), $request->get('module')));

        $viewer->assign('IMPORT_UPLOAD_SIZE_MB', Vtiger_Util_Helper::getMaxUploadSize());
        $viewer->assign('IMPORT_UPLOAD_SIZE', Vtiger_Util_Helper::getMaxUploadSizeInBytes());

        $viewer->view('ImportEMAILTemplate.tpl', 'EMAILMaker');
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            'modules.EMAILMaker.resources.Import'
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}