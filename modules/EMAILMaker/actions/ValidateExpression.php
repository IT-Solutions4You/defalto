<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class EMAILMaker_ValidateExpression_Action extends Vtiger_Action_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('ForEMAILMakerDisplayEdit');
    }

    public function checkPermission(Vtiger_Request $request)
    {
        return;
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function ForEMAILMakerDisplayEdit(Vtiger_Request $request)
    {
        require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

        $result = new Vtiger_Response();

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
        $result->setResult(array('success' => true));
        $result->emit();
    }
}
