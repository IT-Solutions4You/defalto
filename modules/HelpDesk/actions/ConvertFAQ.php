<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class HelpDesk_ConvertFAQ_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];
        $permissions[] = ['module_parameter' => 'custom_module', 'action' => 'CreateView'];
        $request->set('custom_module', 'Faq');

        return $permissions;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        $result = [];
        if (!empty ($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

            $faqRecordModel = Faq_Record_Model::getInstanceFromHelpDesk($recordModel);

            $answer = $faqRecordModel->get('faq_answer');
            if ($answer) {
                try {
                    $faqRecordModel->save();
                    header("Location: " . $faqRecordModel->getDetailViewUrl());
                } catch (DuplicateException $e) {
                    $requestData = $request->getAll();
                    unset($requestData['__vtrftk']);
                    unset($requestData['action']);
                    unset($requestData['record']);
                    $requestData['view'] = 'Edit';
                    $requestData['module'] = 'HelpDesk';
                    $requestData['duplicateRecords'] = $e->getDuplicateRecordIds();

                    global $defalto_current_version;
                    $viewer = new Vtiger_Viewer();
                    $viewer->assign('REQUEST_DATA', $requestData);
                    $viewer->assign('REQUEST_URL', $faqRecordModel->getEditViewUrl() . "&parentId=$recordId&parentModule=$moduleName");
                    $viewer->view('RedirectToEditView.tpl', 'Vtiger');
                } catch (Exception $e) {
                }
            } else {
                header("Location: " . $faqRecordModel->getEditViewUrl() . "&parentId=$recordId&parentModule=$moduleName");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}