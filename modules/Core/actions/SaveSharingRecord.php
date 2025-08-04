<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_SaveSharingRecord_Action extends Vtiger_Save_Action
{
    /**
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $recordModel = new Core_SharingRecord_Model();

        if ($recordModel) {
            $recordModel->set('record', $request->get('record'));
            $recordModel->set('memberViewList', $request->get('memberViewList'));
            $recordModel->set('memberEditList', $request->get('memberEditList'));
            $recordModel->save();
        }

        $redirectUrl = $recordModel->getDetailViewUrl($moduleName, $recordId);

        header('Location: ' . $redirectUrl);
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