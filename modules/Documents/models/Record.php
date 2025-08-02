<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Documents_Record_Model extends Vtiger_Record_Model
{
    /**
     * Function to get the Display Name for the record
     * @return <String> - Entity Display Name for the record
     */
    function getDisplayName()
    {
        return Vtiger_Util_Helper::getRecordName($this->getId());
    }

    function getDownloadFileURL($attachmentId = false)
    {
        if ($this->get('filelocationtype') == 'I') {
            $fileDetails = $this->getFileDetails();

            return 'index.php?module=' . $this->getModuleName() . '&action=DownloadFile&record=' . $this->getId() . '&fileid=' . $fileDetails['attachmentsid'];
        } else {
            return $this->get('filename');
        }
    }

    function checkFileIntegrityURL()
    {
        return "javascript:Documents_Detail_Js.checkFileIntegrity('index.php?module=" . $this->getModuleName() . "&action=CheckFileIntegrity&record=" . $this->getId() . "')";
    }

    function checkFileIntegrity()
    {
        $recordId = $this->get('id');
        $downloadType = $this->get('filelocationtype');
        $returnValue = false;

        if ($downloadType == 'I') {
            $fileDetails = $this->getFileDetails();
            if (!empty ($fileDetails)) {
                $filePath = $fileDetails['path'];
                $storedFileName = $fileDetails['storedname'];

                $savedFile = $fileDetails['attachmentsid'] . "_" . $storedFileName;

                if (fopen($filePath . $savedFile, "r")) {
                    $returnValue = true;
                }
            }
        }

        return $returnValue;
    }

    function getFileDetails($attachmentId = false)
    {
        $db = PearDatabase::getInstance();
        $fileDetails = [];

        $result = $db->pquery(
            "SELECT * FROM vtiger_attachments
							INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
							WHERE crmid = ?",
            [$this->get('id')]
        );

        if ($db->num_rows($result)) {
            $fileDetails = $db->query_result_rowdata($result);
        }

        return $fileDetails;
    }

    function downloadFile($attachmentId = false)
    {
        $fileDetails = $this->getFileDetails();
        $fileContent = false;

        if (!empty ($fileDetails)) {
            $filePath = $fileDetails['path'];
            $fileName = $fileDetails['name'];
            $storedFileName = $fileDetails['storedname'];

            if ($this->get('filelocationtype') == 'I') {
                $fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
                if (!empty($fileName)) {
                    if (!empty($storedFileName)) {
                        $savedFile = $fileDetails['attachmentsid'] . "_" . $storedFileName;
                    } elseif (is_null($storedFileName)) {
                        $savedFile = $fileDetails['attachmentsid'] . "_" . $fileName;
                    }
                    while (ob_get_level()) {
                        ob_end_clean();
                    }
                    $fileSize = filesize($filePath . $savedFile);
                    $fileSize = $fileSize + ($fileSize % 1024);

                    if (fopen($filePath . $savedFile, "r")) {
                        $fileContent = fread(fopen($filePath . $savedFile, "r"), $fileSize);

                        header("Content-type: " . $fileDetails['type']);
                        header("Pragma: public");
                        header("Cache-Control: private");
                        header("Content-Disposition: attachment; filename=\"$fileName\"");
                        header("Content-Description: PHP Generated Data");
                        header("Content-Encoding: none");
                    }
                }
            }
        }
        echo $fileContent;
    }

    function updateFileStatus()
    {
        $db = PearDatabase::getInstance();

        $db->pquery("UPDATE vtiger_notes SET filestatus = 0 WHERE notesid= ?", [$this->get('id')]);
    }

    function updateDownloadCount()
    {
        $db = PearDatabase::getInstance();
        $notesId = $this->get('id');

        $result = $db->pquery("SELECT filedownloadcount FROM vtiger_notes WHERE notesid = ?", [$notesId]);
        $downloadCount = $db->query_result($result, 0, 'filedownloadcount') + 1;

        $db->pquery("UPDATE vtiger_notes SET filedownloadcount = ? WHERE notesid = ?", [$downloadCount, $notesId]);
    }

    function getDownloadCountUpdateUrl()
    {
        return "index.php?module=Documents&action=UpdateDownloadCount&record=" . $this->getId();
    }

    function get($key)
    {
        $value = parent::get($key);
        if ($key === 'notecontent') {
            return decode_html($value);
        }

        return $value;
    }

    /**
     * @param int $recordId
     * @param int $attachmentId
     *
     * @return void
     */
    public function saveAttachmentsRelation(int $recordId, int $attachmentId): void
    {
        if (empty($recordId) || empty($attachmentId)) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $params = [$recordId, $attachmentId];
        $result = $adb->pquery('SELECT * FROM vtiger_seattachmentsrel WHERE crmid=? AND attachmentsid=?', $params);

        if ($adb->num_rows($result)) {
            return;
        }

        $adb->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)', $params);
    }

    /**
     * @param int $recordId
     * @param int $documentId
     *
     * @return void
     */
    public function saveDocumentsRelation(int $recordId, int $documentId): void
    {
        if (empty($recordId) || empty($documentId)) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $params = [$recordId, $documentId];
        $result = $adb->pquery('SELECT * FROM vtiger_senotesrel WHERE crmid=? AND notesid=?', $params);

        if ($adb->num_rows($result)) {
            return;
        }

        $adb->pquery('INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)', $params);
    }
}