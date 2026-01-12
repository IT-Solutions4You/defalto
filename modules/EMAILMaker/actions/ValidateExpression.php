<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_ValidateExpression_Action extends Core_Controller_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('ForEMAILMakerDisplayEdit');
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
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
        $result->setResult(['success' => true]);
        $result->emit();
    }
}