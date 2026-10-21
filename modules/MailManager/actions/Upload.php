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

include_once 'modules/MailManager/third-party/AjaxUpload/ajaxUpload.php';
require_once 'modules/MailManager/MailManager.php';

class MailManager_UploadFileXHR extends qqUploadedFileXhr
{
    /**
     * Create a Document
     *
     * @return array|false
     * @throws Exception
     */
    public function createDocument()
    {
        return MailManager_Upload_Model::createDocument($this, null);
    }

    /**
     * Save an attachment
     *
     * @return int|false
     */
    public function saveAttachment()
    {
        return MailManager_Upload_Model::saveAttachment($this);
    }

    /**
     * Function used to Create Document and Attachments
     */
    public function process()
    {
        return $this->createDocument();
    }
}

/**
 * Class used to Upload file using Form, used to IE
 */
class MailManager_UploadFileForm extends qqUploadedFileForm
{

    /**
     * Saves the uploaded file
     *
     * @param String  $path
     *
     * @return Boolean
     */
    public function save($path)
    {
        $filePath = Core_Storage_Model::getInstance()->getFilePath(dirname($path), basename($path));

        if (is_file($filePath)) {
            return true;
        } elseif (move_uploaded_file($_FILES['qqfile']['tmp_name'], $filePath)) {
            return true;
        }

        return false;
    }

    /**
     * Function used to Create Document and Attachments
     */
    public function process()
    {
        return $this->createDocument();
    }

    /**
     * Used to create Documents
     *
     * @return array|false
     * @throws Exception
     */
    public function createDocument()
    {
        return MailManager_Upload_Model::createDocument($this, 'MailManager');
    }

    /**
     * Creates an Attachments
     *
     * @return int|false
     */
    public function saveAttachment()
    {
        return MailManager_Upload_Model::saveAttachment($this);
    }
}

/**
 * Class used to control Uploading files
 */
class MailManager_Upload_Action extends qqFileUploader
{

    /**
     * Constructor used to invoke the Uploading Handler
     *
     * @param Array   $allowedExtensions
     * @param Integer $sizeLimit
     */
    public function __construct($allowedExtensions, $sizeLimit)
    {
        $this->setAllowedFileExtension($allowedExtensions);

        $this->setMaxUploadSize($sizeLimit);

        if (isset($_GET['qqfile'])) {
            $this->file = new MailManager_UploadFileXHR();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new MailManager_UploadFileForm();
        } else {
            $this->file = false;
        }
    }

    /**
     * Function used to handle the upload
     *
     * @param String  $uploadDirectory
     * @param Boolean $replaceOldFile
     *
     * @return Array
     */
    public function handleUpload($uploadDirectory, $replaceOldFile = false)
    {
        if (!isPermitted('Documents', 'CreateView')) {
            return ['error' => "Permission not available"];
        }

        if (!is_writable($uploadDirectory)) {
            return ['error' => "Server error. Upload directory isn't writable."];
        }

        if (!$this->file) {
            return ['error' => 'No files were uploaded.'];
        }

        $size = $this->file->getSize();
        if ($size == 0) {
            return ['error' => 'File is empty'];
        }

        if ($size > $this->sizeLimit) {
            return ['error' => 'File is too large'];
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);

            return ['error' => 'File has an invalid extension, it should be one of ' . $these . '.'];
        }

        $response = $this->file->process();
        if ($response['success'] == true) {
            return $response;
        } else {
            return ['error' => 'Could not save uploaded file. The upload was cancelled, or server error encountered'];
        }
    }

    /*
     * get the max file upload sizr
    */
    public function getMaxUploadSize()
    {
        return $this->sizeLimit;
    }

    /*
     * Sets the max file upload size
    */
    public function setMaxUploadSize($value)
    {
        $this->sizeLimit = $value;
    }

    /*
     * gets the allowed file extension
    */
    public function getAllowedFileExtension()
    {
        return $this->allowedExtensions;
    }

    /*
     * sets the allowed file extension
    */
    public function setAllowedFileExtension($values)
    {
        if (!empty($values)) {
            $this->allowedExtensions = $values;
        }
    }
}
