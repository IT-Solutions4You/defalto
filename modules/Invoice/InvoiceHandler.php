<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/Retrieve.php';

class InvoiceHandler extends VTEventHandler
{

    function handleEvent($eventName, $entityData)
    {
        $moduleName = $entityData->getModuleName();

        // Validate the event target
        if ($moduleName != 'Invoice') {
            return;
        }

        //Get Current User Information
        global $current_user, $currentModule;

        // Trigger from another module (due to indirect save) needs to be ignored - to avoid inconsistency.
        if ($currentModule != 'Invoice') {
            return;
        }

        /*
         * Adjust the balance amount against total and received amount
         * NOTE: beforesave the total amount will not be populated in event data.
         */
        if ($eventName == 'vtiger.entity.aftersave') {
            $entityDelta = new VTEntityDelta();
            $oldCurrency = $entityDelta->getOldValue($entityData->getModuleName(), $entityData->getId(), 'currency_id');
            $newCurrency = $entityDelta->getCurrentValue($entityData->getModuleName(), $entityData->getId(), 'currency_id');
            $oldConversionRate = $entityDelta->getOldValue($entityData->getModuleName(), $entityData->getId(), 'conversion_rate');
            $db = PearDatabase::getInstance();
            $wsId = vtws_getWebserviceEntityId('Invoice', $entityData->getId());
            $wsRecord = vtws_retrieve($wsId, $current_user);

            if ($oldCurrency != $newCurrency && $oldCurrency != '') {
                if ($oldConversionRate != '') {
                    $wsRecord['received'] = ((float)$wsRecord['received'] / $oldConversionRate) * (float)$wsRecord['conversion_rate'];
                }
            }

            $wsRecord['balance'] = (float)$wsRecord['price_total'] - (float)$wsRecord['received'];

            if ($wsRecord['balance'] == 0) {
                $wsRecord['invoicestatus'] = 'Paid';
            }

            $query = "UPDATE vtiger_invoice SET balance=?, received=? WHERE invoiceid=?";
            $db->pquery($query, [$wsRecord['balance'], $wsRecord['received'], $entityData->getId()]);
        }
    }
}