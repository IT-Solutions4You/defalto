<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
