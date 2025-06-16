<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Tour_Guide_Action extends Vtiger_BasicAjax_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function deleteDemoData(Vtiger_Request $request): void
    {
        $guideName = $request->get('name');
        $guide = Tour_Base_Guide::getInstance($guideName);
        $guide->deleteDemoData();

        header('location:' . $_SERVER['HTTP_REFERER']);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function importDemoData(Vtiger_Request $request): void
    {
        $guideName = $request->get('name');
        $guide = Tour_Base_Guide::getInstance($guideName);
        $guide->importDemoData();

        header('location:' . $_SERVER['HTTP_REFERER']);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function nextStep(Vtiger_Request $request): void
    {
        $guideName = $request->get('name');
        $guide = Tour_Base_Guide::getInstance($guideName);
        $guide->setNextStep();

        header('location:' . $guide->getStepUrl());
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function prevStep(Vtiger_Request $request): void
    {
        $guideName = $request->get('name');
        $guide = Tour_Base_Guide::getInstance($guideName);
        $guide->setPrevStep();

        header('location:' . $guide->getStepUrl());
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('importDemoData');
        $this->exposeMethod('deleteDemoData');
        $this->exposeMethod('nextStep');
        $this->exposeMethod('prevStep');
        $this->invokeExposedMethod($request->getMode(), $request);
    }
}