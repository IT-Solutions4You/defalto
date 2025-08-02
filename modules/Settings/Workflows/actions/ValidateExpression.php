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

class Settings_Workflows_ValidateExpression_Action extends Settings_Vtiger_Basic_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('ForTaskEdit');
        $this->exposeMethod('ForWorkflowEdit');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);

            return;
        }
    }

    public function ForTaskEdit(Vtiger_Request $request)
    {
        require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

        $result = new Vtiger_Response();
        $fieldMapping = Zend_Json::decode($request->getRaw('field_value_mapping'));
        if (empty($fieldMapping)) {
            $fieldMapping = [];
        }
        foreach ($fieldMapping as $key => $mappingInfo) {
            if ($mappingInfo['valuetype'] == 'expression') {
                try {
                    $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($mappingInfo['value'])));
                    $expression = $parser->expression();
                } catch (Exception $e) {
                    $result->setError($mappingInfo);
                    $result->emit();

                    return;
                }
            }
        }
        $result->setResult(['success' => true]);
        $result->emit();
    }

    public function ForWorkflowEdit(Vtiger_Request $request)
    {
        require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

        $result = new Vtiger_Response();

        //For workflows that are created in vtiger5 we are ignoring checking of expression validation
        if ($request->get('filtersavedinnew') != '6') {
            $result->setResult(['success' => false]);
            $result->emit();

            return;
        }

        $conditions = $request->get('conditions');

        foreach ($conditions as $info) {
            foreach ($info['columns'] as $conditionRow) {
                if ($conditionRow['valuetype'] == 'expression') {
                    try {
                        $parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($conditionRow['value'])));
                        $expression = $parser->expression();
                    } catch (Exception $e) {
                        $result->setError($conditionRow);
                        $result->emit();

                        return;
                    }
                }
            }
        }
        $result->setResult(['success' => true]);
        $result->emit();
    }
}