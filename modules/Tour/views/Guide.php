<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Tour_Guide_View extends Tour_Index_View
{
    /**
     * @param Vtiger_Request $request
     *
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('guide');
        $this->exposeMethod('modal');
        $this->invokeExposedMethod($request->get('mode', 'guide'), $request);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function guide(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $guideName = $request->get('name');
        $guide = Tour_Base_Guide::getInstance($guideName);
        $guide->setStep(0);

        $viewer = $this->getViewer($request);
        $viewer->assign('GUIDE_NAME', $guideName);
        $viewer->assign('GUIDE', $guide);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'guide', $request->getModule(), $viewer, $request);

        $viewer->view('Guide.tpl', $moduleName);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function modal(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $guide = Tour_Base_Guide::getCurrentInstance();

        if (!$guide || !$guide->getStep()) {
            return;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('GUIDE_NAME', $guide->getName());
        $viewer->assign('GUIDE', $guide);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'modal', $request->getModule(), $viewer, $request);

        $viewer->view('Modal.tpl', $moduleName);
    }
}