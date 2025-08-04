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

class Settings_Workflows_Save_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $summary = $request->get('summary');
        $moduleName = $request->get('module_name');
        $conditions = $request->get('conditions');
        $filterSavedInNew = $request->get('filtersavedinnew');
        $executionCondition = $request->get('execution_condition');

        if ($recordId) {
            $workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
        } else {
            $workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
            $workflowModel->set('name', $summary);
        }

        require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';
        $response = new Vtiger_Response();
        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $info) {
                if (!empty($info['columns']) && is_array($info['columns'])) {
                    foreach ($info['columns'] as $conditionRow) {
                        if ($conditionRow['valuetype'] == 'expression') {
                            try {
                                $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($conditionRow['value'])));
                                $expression = $parser->expression();
                            } catch (Exception $e) {
                                $response->setError($conditionRow, vJsTranslate('LBL_EXPRESSION_INVALID', $request->getModule(false)));
                                $response->emit();

                                return;
                            }
                        }
                    }
                }
            }
        }
        $workflowModel->set('summary', $summary);
        $workflowModel->set('module_name', $moduleName);
        $workflowModel->set('conditions', $conditions);
        $workflowModel->set('execution_condition', $executionCondition);
        $workflowModel->set('status', 1);

        if ($executionCondition == '6') {
            $schtime = $request->get("schtime");
            if (!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime, 0, 2) > 23) {  // invalid time format
                $schtime = '00:00';
            }
            $schtime .= ':00';

            $workflowModel->set('schtime', $schtime);

            $workflowScheduleType = $request->get('schtypeid');
            $workflowModel->set('schtypeid', $workflowScheduleType);

            $dayOfMonth = null;
            $dayOfWeek = null;
            $month = null;
            $annualDates = null;

            if ($workflowScheduleType == Workflow::$SCHEDULED_WEEKLY) {
                $dayOfWeek = Zend_Json::encode($request->get('schdayofweek'));
            } elseif ($workflowScheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
                $dayOfMonth = Zend_Json::encode($request->get('schdayofmonth'));
            } elseif ($workflowScheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
                $date = $request->get('schdate');
                $dateDBFormat = DateTimeField::convertToDBFormat($date);
                $nextTriggerTime = $dateDBFormat . ' ' . $schtime;
                $currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
                if ($nextTriggerTime > $currentTime) {
                    $workflowModel->set('nexttrigger_time', $nextTriggerTime);
                } else {
                    $workflowModel->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
                }
                $annualDates = Zend_Json::encode([$dateDBFormat]);
            } elseif ($workflowScheduleType == Workflow::$SCHEDULED_ANNUALLY) {
                $annualDates = Zend_Json::encode($request->get('schannualdates'));
            }
            $workflowModel->set('schdayofmonth', $dayOfMonth);
            $workflowModel->set('schdayofweek', $dayOfWeek);
            $workflowModel->set('schannualdates', $annualDates);
        }

        // Added to save the condition only when its changed from vtiger6
        if ($filterSavedInNew == '6') {
            //Added to change advanced filter condition to workflow
            $workflowModel->transformAdvanceFilterToWorkFlowFilter();
        }
        $workflowModel->set('filtersavedinnew', $filterSavedInNew);
        $workflowModel->save();

        //Update only for scheduled workflows other than specific date
        if ($workflowScheduleType != Workflow::$SCHEDULED_ON_SPECIFIC_DATE && $executionCondition == '6') {
            $workflowModel->updateNextTriggerTime();
        }
        $response->setResult(['id' => $workflowModel->get('workflow_id')]);
        $response->emit();
    }

    public function validateRequest(Vtiger_Request $request)
    {
        $request->validateWriteAccess();
    }
}