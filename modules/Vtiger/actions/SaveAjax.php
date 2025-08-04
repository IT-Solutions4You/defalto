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

class Vtiger_SaveAjax_Action extends Vtiger_Save_Action
{
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        try {
            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
            $recordModel = $this->saveRecord($request);
            vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);

            $fieldModelList = $recordModel->getModule()->getFields();
            $result = [];

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                $picklistColorMap = [];

                if ($fieldModel->isViewable()) {
                    $recordFieldValue = $recordModel->get($fieldName);

                    if ($fieldModel->getFieldDataType() == 'multipicklist') {
                        if (!is_array($recordFieldValue)) {
                            $recordFieldValue = explode(' |##| ', $recordFieldValue);
                        }

                        foreach ($recordFieldValue as $picklistValue) {
                            $picklistColorMap[$picklistValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $picklistValue);
                        }

                        $recordFieldValue = implode(' |##| ', $recordFieldValue);
                    }

                    if ($fieldModel->getFieldDataType() == 'picklist') {
                        $picklistColorMap[$recordFieldValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $recordFieldValue);
                    }

                    $fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);

                    if ($fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date') {
                        $displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
                    }

                    if (!empty($picklistColorMap)) {
                        $result[$fieldName] = ['value' => $fieldValue, 'display_value' => $displayValue, 'colormap' => $picklistColorMap];
                    } else {
                        $result[$fieldName] = ['value' => $fieldValue, 'display_value' => $displayValue];
                    }
                }
            }

            //Handling salutation type
            if ($request->get('field') === 'firstname' && in_array($request->getModule(), ['Contacts', 'Leads'])) {
                $salutationType = $recordModel->getDisplayValue('salutationtype');
                $firstNameDetails = $result['firstname'];
                $firstNameDetails['display_value'] = $salutationType . " " . $firstNameDetails['display_value'];
                if ($salutationType != '--None--') {
                    $result['firstname'] = $firstNameDetails;
                }
            }

            // removed decode_html to eliminate XSS vulnerability
            $result['_recordLabel'] = decode_html($recordModel->getName());
            $result['_recordId'] = $recordModel->getId();
            $response->setEmitType(Vtiger_Response::$EMIT_JSON);
            $response->setResult($result);
        } catch (DuplicateException $e) {
            $response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
        } catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        $response->emit();
    }

    /**
     * Function to get the record model based on the request parameters
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Record_Model or Module specific Record Model instance
     */
    public function getRecordModelFromRequest(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if (!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');
            $fieldModelList = $recordModel->getModule()->getFields();

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                //For not converting createdtime and modified time to user format
                $fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));
                // To support Inline Edit in Vtiger7
                if ($request->has($fieldName)) {
                    $fieldValue = $request->get($fieldName, null);
                } elseif ($fieldName === $request->get('field')) {
                    $fieldValue = $request->get('value');
                }

                $fieldValue = $this->purifyCkeditorField($fieldName, $fieldValue);

                if ($fieldValue !== null) {
                    $fieldValue = $fieldModel->getUiTypeModel()->getRequestValue($fieldValue);
                    $recordModel->set($fieldName, $fieldValue);
                }

                $recordModel->set($fieldName, $fieldValue);

                if ($fieldName === 'contact_id' && isRecordExists($fieldValue)) {
                    $contactRecord = Vtiger_Record_Model::getInstanceById($fieldValue, 'Contacts');
                    $recordModel->set("relatedContact", $contactRecord);
                }
            }
        } else {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
            $fieldModelList = $moduleModel->getFields();

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                if ($request->has($fieldName)) {
                    $fieldValue = $request->get($fieldName, null);
                } else {
                    $fieldValue = $fieldModel->getDefaultFieldValue();
                }

                $fieldValue = $this->purifyCkeditorField($fieldName, $fieldValue);

                if ($fieldValue !== null) {
                    $fieldValue = $fieldModel->getUiTypeModel()->getRequestValue($fieldValue);
                    $recordModel->set($fieldName, $fieldValue);
                }
            }
        }

        return $recordModel;
    }

    public function purifyCkeditorField($fieldName, $fieldValue)
    {
        $ckeditorFields = ['commentcontent', 'notecontent', 'signature'];
        if ((in_array($fieldName, $ckeditorFields)) && $fieldValue !== null) {
            $purifiedContent = vtlib_purify(decode_html($fieldValue));
            // Purify malicious html event attributes
            $fieldValue = purifyHtmlEventAttributes(decode_html($purifiedContent), true);
        }

        return $fieldValue;
    }
}