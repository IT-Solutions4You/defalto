<?php
/*+**********************************************************************************
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 ************************************************************************************/

class Settings_LayoutEditor_SaveHeaderFieldsAjax_Action extends Settings_Vtiger_Basic_Action {

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request): void
    {
        $response = new Vtiger_Response();
        try{
            $selectedModule = $request->get('selected_module');
            $headerFields = $request->get('header_fields');

            $headerFieldsModel = new Settings_LayoutEditor_HeaderFields_Model();
            $headerFieldsModel->saveHeaderFields($selectedModule, $headerFields);

            $response->setResult(true);
        }catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): void
    {
        $request->validateWriteAccess();
    }
}