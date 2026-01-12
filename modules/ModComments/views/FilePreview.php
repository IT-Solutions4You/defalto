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

class ModComments_FilePreview_View extends Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $moduleName = $request->getModule();

        if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $request->get('record'))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $attachmentId = $request->get('attachmentid');
        $basicFileTypes = ['txt', 'csv', 'ics'];
        $imageFileTypes = ['image/gif', 'image/png', 'image/jpeg'];
        //supported by video js
        $videoFileTypes = ['video/mp4', 'video/ogg', 'audio/ogg', 'video/webm'];
        $audioFileTypes = ['audio/mp3', 'audio/mpeg', 'audio/wav'];
        //supported by viewer js
        $opendocumentFileTypes = ['odt', 'ods', 'odp', 'fodt'];
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $attachments = $recordModel->getFileDetails($attachmentId);
        $fileDetails = $attachments[0];
        $fileContent = false;

        if (!empty($fileDetails)) {
            $filePath = $fileDetails['path'];
            $fileName = $fileDetails['name'];
            $storedFileName = $fileDetails['storedname'];
            $fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
            $savedFile = $fileDetails['attachmentsid'] . "_" . $storedFileName;
            $fileSize = filesize($filePath . $savedFile);
            $fileSize = $fileSize + ($fileSize % 1024);

            if (fopen($filePath . $savedFile, "r")) {
                $fileContent = fread(fopen($filePath . $savedFile, "r"), $fileSize);
            }
        }

        $type = $fileDetails['type'];
        $contents = $fileContent;
        $filename = $fileDetails['name'];
        $parts = explode('.', $filename);

        if ($recordId && $attachmentId) {
            $fileDetails = $recordModel->getFileNameAndDownloadURL($recordId, $attachmentId);
            $downloadUrl = $recordModel->getDownloadFileURL($attachmentId);
            $trimmedFileName = $fileDetails[0]['trimmedFileName'];
        }

        //support for plain/text document
        $extn = 'txt';

        if (php7_count($parts) > 1) {
            $extn = end($parts);
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);

        if (in_array($extn, $basicFileTypes)) {
            $viewer->assign('BASIC_FILE_TYPE', 'yes');
        } elseif (in_array($type, $videoFileTypes)) {
            $viewer->assign('VIDEO_FILE_TYPE', 'yes');
        } elseif (in_array($type, $imageFileTypes)) {
            $viewer->assign('IMAGE_FILE_TYPE', 'yes');
        } elseif (in_array($type, $audioFileTypes)) {
            $viewer->assign('AUDIO_FILE_TYPE', 'yes');
        } elseif (in_array($extn, $opendocumentFileTypes)) {
            $viewer->assign('OPENDOCUMENT_FILE_TYPE', 'yes');
            $downloadUrl .= "&type=$extn";
        } elseif ($extn == 'pdf') {
            $viewer->assign('PDF_FILE_TYPE', 'yes');
        } else {
            $viewer->assign('FILE_PREVIEW_NOT_SUPPORTED', 'yes');
        }

        $viewer->assign('DOWNLOAD_URL', $downloadUrl);
        $viewer->assign('TRIMMED_FILE_NAME', $trimmedFileName);
        $viewer->assign('FILE_NAME', $filename);
        $viewer->assign('FILE_EXTN', $extn);
        $viewer->assign('FILE_TYPE', $type);
        $viewer->assign('FILE_CONTENTS', $contents);
        $viewer->assign('SITE_URL', vglobal('site_URL'));

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $viewer->view('FilePreview.tpl', $moduleName);
    }
}