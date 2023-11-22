<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Appointments_SaveOverlay_Action extends Appointments_Save_Action
{
    public function process(Vtiger_Request $request)
    {
        try {
            $recordModel = $this->saveRecord($request);

            $result = [];
            $result['_recordLabel'] = decode_html($recordModel->getName());
            $result['_recordId'] = $recordModel->getId();

            $response = new Vtiger_Response();
            $response->setResult($result);
            $response->emit();
        } catch (DuplicateException $e) {
            $requestData = $request->getAll();
            $moduleName = $request->getModule();
            unset($requestData['action']);
            unset($requestData['__vtrftk']);

            if ($request->isAjax()) {
                $response = new Vtiger_Response();
                $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
                $response->emit();
            } else {
                $requestData['view'] = 'Edit';
                $requestData['duplicateRecords'] = $e->getDuplicateRecordIds();
                $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

                global $vtiger_current_version;
                $viewer = new Vtiger_Viewer();

                $viewer->assign('REQUEST_DATA', $requestData);
                $viewer->assign('REQUEST_URL', $moduleModel->getCreateRecordUrl() . '&record=' . $request->get('record'));
                $viewer->view('RedirectToEditView.tpl', 'Vtiger');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}