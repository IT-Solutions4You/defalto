<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Potentials_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		//Restrict to store indirect relationship from Potentials to Contacts
		$sourceModule = $request->get('sourceModule');
		$relationOperation = $request->get('relationOperation');
		$skip = true;

		if ($relationOperation && $sourceModule === 'Contacts') {
			$request->set('relationOperation', false);
			$skip = false;
		}

		parent::process($request);

		// to link the relation in updates
		if (!$skip) {
			$sourceRecordId = $request->get('sourceRecord');
			$focus = CRMEntity::getInstance($sourceModule);
			$destinationModule = $request->get('module');
			$destinationRecordId = $this->savedRecordId;

            $focus->setTrackLinkedInfo((int)$sourceRecordId, (int)$destinationRecordId);
            $focus->trackLinkedInfo($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);
		}
	}
}
