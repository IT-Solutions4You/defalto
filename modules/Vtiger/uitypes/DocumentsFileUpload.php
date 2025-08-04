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

class Vtiger_DocumentsFileUpload_UIType extends Vtiger_Base_UIType
{
    /**
     * Function to get the Template name for the current UI Type Object
     * @return <String> - Template Name
     */
    public function getTemplateName()
    {
        return 'uitypes/DocumentsFileUpload.tpl';
    }

    /**
     * Function to get the Display Value, for the current field type with given DB Insert Value
     *
     * @param <String>  $value
     * @param <Integer> $recordId
     * @param <Vtiger_Record_Model>
     *
     * @return <String>
     */
    public function getDisplayValue($value, $recordId = false, $recordModel = false)
    {
        if ($recordModel) {
            $fileLocationType = $recordModel->get('filelocationtype');
            $fileStatus = $recordModel->get('filestatus');
            if (!empty($value) && $fileStatus) {
                if ($fileLocationType == 'I') {
                    $db = PearDatabase::getInstance();
                    $fileIdRes = $db->pquery('SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = ?', [$recordId]);
                    $fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
                    if ($fileId) {
                        $value = '<a href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '"' .
                            ' title="' . vtranslate('LBL_DOWNLOAD_FILE', 'Documents') . '" >' . $value . '</a>';
                    }
                } else {
                    $value = '<a href="' . $value . '" target="_blank" title="' . vtranslate('LBL_DOWNLOAD_FILE', 'Documents') . '" >' . textlength_check($value) . '</a>';
                }
            } else {
                $value = textlength_check($value);
            }
        }

        return $value;
    }
}