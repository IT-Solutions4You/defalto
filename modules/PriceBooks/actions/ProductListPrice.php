<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class PriceBooks_ProductListPrice_Action extends Vtiger_Action_Controller
{

    public function requiresPermission(Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView', 'record_parameter' => 'record'];

        return $permissions;
    }

    function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleModel = $request->getModule();
        $priceBookModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
        $listPrice = $priceBookModel->getProductsListPrice($request->get('itemId'));

        if (empty($listPrice)) {
            $listPrice = 0;
        }

        $response = new Vtiger_Response();
        $response->setResult(['price' => $listPrice, 'pricebookid' => $recordId]);
        $response->emit();
    }
}