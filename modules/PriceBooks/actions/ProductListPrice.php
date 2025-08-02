<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
		} /* Selected product not in pricebook */

		$response = new Vtiger_Response();
		$response->setResult([$listPrice]);
		$response->emit();
	}
}