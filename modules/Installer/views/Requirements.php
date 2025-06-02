<?php

class Installer_Requirements_View extends Installer_Index_View
{
    public function process(Vtiger_Request $request, $display = true)
    {
        $this->exposeMethod('Module');
        $this->exposeMethod('System');

        if (!$request->isEmpty('mode')) {
            $this->invokeExposedMethod($request->getMode(), $request);
            return;
        }

        $this->System($request);
    }

    /**
     * @param Vtiger_Request $request
     */
    public function Module(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule();
        $sourceModule = $request->get('sourceModule');
        $requirements = false;

        if(!empty($sourceModule)) {
            $requirements = Installer_ModuleRequirements_Model::getInstance($sourceModule);
            $requirements->retrieveData();
        }

        $viewer = $this->getViewer($request);

        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('SOURCE_MODULES', Installer_ModuleRequirements_Model::getSourceModules());
        $viewer->assign('SOURCE_MODULE_NAME', $request->get('sourceModule'));
        $viewer->assign('REQUIREMENTS', $requirements);
        $viewer->assign('REQUIREMENT_VALIDATIONS', $requirements->getValidations());
        $viewer->assign('TEMPLATE', 'RequirementsModule.tpl');

        $viewer->view('Index.tpl', $qualifiedModule);
    }

    /**
     * @param Vtiger_Request $request
     */
    public function System(Vtiger_Request $request): void
    {
        $qualifiedModule = $request->getModule();
        $requirements = Installer_Requirements_Model::getInstance();

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
        $viewer->assign('REQUIREMENTS', $requirements);
        $viewer->assign('REQUIREMENTS_TITLE', 'Module' === $request->getMode() ? 'LBL_MODULE_REQUIREMENTS' : 'LBL_SYSTEM_REQUIREMENTS');
        $viewer->assign('SOURCE_MODULES', Installer_ModuleRequirements_Model::getSourceModules());
        $viewer->assign('SOURCE_MODULE_NAME', $request->get('sourceModule'));
        $viewer->assign('TEMPLATE', 'Requirements.tpl');

        $viewer->view('Index.tpl', $qualifiedModule);
    }
}