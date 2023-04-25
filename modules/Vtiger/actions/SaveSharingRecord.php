<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_SaveSharingRecord_Action extends Vtiger_Save_Action
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $recordModel = new Vtiger_SharingRecord_Model();

        if ($recordModel) {
            $recordModel->set('record', $request->get('record'));
            $recordModel->set('memberViewList', $request->get('memberViewList'));
            $recordModel->set('memberEditList', $request->get('memberEditList'));
            $recordModel->save();
        }

        $redirectUrl = $recordModel->getDetailViewUrl($moduleName, $recordId);

        header('Location: '. $redirectUrl);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return bool|void
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}