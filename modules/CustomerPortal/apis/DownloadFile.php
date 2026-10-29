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

class CustomerPortal_DownloadFile extends CustomerPortal_API_Abstract
{
    protected function isWebserviceRecordId($recordId): bool
    {
        return is_string($recordId) && preg_match('/^[1-9][0-9]*x[1-9][0-9]*$/', $recordId) === 1;
    }

    protected function getRecordModule(string $recordId): string
    {
        global $adb;
        $idComponents = vtws_getIdComponents($recordId);

        return VtigerWebserviceObject::fromId($adb, (int)$idComponents[0])->getEntityName();
    }

    protected function validateDocumentAccess(string $recordId, string $parentId, string $parentModule): void
    {
        if (empty($parentId)) {
            if (!$this->isRecordAccessible($recordId, 'Documents')) {
                throw new Exception('Record not accessible', 1412);
            }

            return;
        }

        if ($parentModule === 'Faq') {
            if (!$this->isFaqPublished($parentId)) {
                throw new Exception('This Faq is not published', 1412);
            }
        } elseif (!$this->isRecordAccessible($parentId)) {
            throw new Exception('Parent record not accessible', 1412);
        }

        $relatedRecordIds = $this->relatedRecordIds(
            'Documents',
            CustomerPortal_Utils::getRelatedModuleLabel('Documents', $parentModule),
            $parentId
        );

        if (!in_array($recordId, $relatedRecordIds, true)) {
            throw new Exception('Record not accessible', 1412);
        }
    }

    protected function retrieveAccessibleComment(int $commentId, string $parentId): ModComments_Record_Model
    {
        $commentModel = Vtiger_Record_Model::getInstanceById($commentId, 'ModComments');

        if ((int)$commentModel->get('is_private') !== 0) {
            throw new Exception('Record not accessible', 1412);
        }

        $relatedRecordId = (int)$commentModel->get('related_to');
        $relatedModule = getSalesEntityType($relatedRecordId);

        if (empty($relatedRecordId) || empty($relatedModule) || !CustomerPortal_Utils::isModuleActive($relatedModule)) {
            throw new Exception('Record not accessible', 1412);
        }

        $relatedRecordWebserviceId = vtws_getWebserviceEntityId($relatedModule, $relatedRecordId);

        if (!empty($parentId) && $parentId !== $relatedRecordWebserviceId) {
            throw new Exception('Record not accessible', 1412);
        }

        if ($relatedModule === 'Faq') {
            if (!$this->isFaqPublished($relatedRecordWebserviceId)) {
                throw new Exception('This Faq is not published', 1412);
            }
        } elseif (!$this->isRecordAccessible($relatedRecordWebserviceId, $relatedModule)) {
            throw new Exception('Record not accessible', 1412);
        }

        return $commentModel;
    }

    function process(CustomerPortal_API_Request $request)
    {
        global $adb;
        $current_user = $this->getActiveUser();
        $response = new CustomerPortal_API_Response();

        if ($current_user) {
            $parentId = $request->get('parentId');
            $recordId = $request->get('recordId');
            $module = $request->get('module');
            $parentModule = $request->get('parentModule');

            if (
                !is_string($module)
                || !is_string($parentId)
                || !is_string($parentModule)
                || !$this->isWebserviceRecordId($recordId)
            ) {
                throw new Exception('Invalid download request', 1412);
            }

            if (!empty($parentId)) {
                if (!$this->isWebserviceRecordId($parentId)) {
                    throw new Exception('Invalid download request', 1412);
                }

                $recordParentModule = $this->getRecordModule($parentId);

                if (!empty($parentModule) && $parentModule !== $recordParentModule) {
                    throw new Exception('Invalid download request', 1412);
                }

                $parentModule = $recordParentModule;
            }

            if (!CustomerPortal_Utils::isModuleActive($module)) {
                $response->setError(1404, 'Module is disabled.');

                return $response;
            }

            if ($this->getRecordModule($recordId) !== $module) {
                throw new Exception('Invalid download request', 1412);
            }

            $idComponents = vtws_getIdComponents($recordId);
            $id = $idComponents[1];

            if ($module === 'Documents') {
                $this->validateDocumentAccess($recordId, $parentId, $parentModule);
                $query = "SELECT filetype FROM vtiger_notes INNER JOIN vtiger_crmentity ON vtiger_notes.notesid= vtiger_crmentity.crmid 
                          WHERE notesid =? AND vtiger_crmentity.deleted=?";
                $res = $adb->pquery($query, [$id, '0']);
                $filetype = $adb->query_result($res, 0, "filetype");
                $this->updateDownloadCount($id);

                $fileidQuery = 'SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid = ?';
                $fileres = $adb->pquery($fileidQuery, [$id]);
                $fileid = $adb->query_result($fileres, 0, 'attachmentsid');

                $filepathQuery = 'SELECT path,name,storedname FROM vtiger_attachments WHERE attachmentsid = ?';
                $fileres = $adb->pquery($filepathQuery, [$fileid]);
                $filepath = $adb->query_result($fileres, 0, 'path');
                if ($adb->query_result($fileres, 0, 'storedname')) {
                    $filename = $adb->query_result($fileres, 0, 'storedname');
                } else {
                    $filename = $adb->query_result($fileres, 0, 'name');
                }
                $realfilename = decode_html($adb->query_result($fileres, 0, 'name'));

                $filenamewithpath = Core_Storage_Model::getInstance()->getAttachmentFilePath([
                    'attachmentsid' => $fileid,
                    'path' => $filepath,
                    'name' => $realfilename,
                    'storedname' => $filename,
                ]);
                $filesize = filesize($filenamewithpath);
                $fileDetails = [];
                $fileDetails['fileid'] = $fileid;
                $fileDetails['filename'] = $realfilename;
                $fileDetails['filetype'] = $filetype;
                $fileDetails['filesize'] = $filesize;
                $fileDetails['filecontents'] = base64_encode(file_get_contents($filenamewithpath));
                $response->setResult($fileDetails);
            } elseif ($module === 'ModComments') {
                $attachmentId = $request->get('attachmentId');

                if (!is_string($attachmentId) || preg_match('/^[1-9][0-9]*$/', $attachmentId) !== 1) {
                    throw new Exception('Invalid download request', 1412);
                }

                $modCommentsRecordModel = $this->retrieveAccessibleComment((int)$id, $parentId);
                $rawAttachmentDetails = $modCommentsRecordModel->getFileDetails($attachmentId);

                if (empty($rawAttachmentDetails)) {
                    throw new Exception('Attachment not accessible', 1412);
                }

                //construct path for attachment and get file size and type details
                $attachmentDetails = $rawAttachmentDetails[0];
                $fileid = $attachmentDetails['attachmentsid'];
                $filename = $attachmentDetails['storedname'];
                if (empty($filename)) {
                    $filename = $attachmentDetails['name'];
                }
                $realfilename = decode_html($attachmentDetails['name']);

                $filenamewithpath = Core_Storage_Model::getInstance()->getAttachmentFilePath($attachmentDetails);
                $filesize = filesize($filenamewithpath);
                $filetype = $attachmentDetails['type'];

                //Construct array with all attachment details
                $fileDetails = [];
                $fileDetails['fileid'] = $fileid;
                $fileDetails['filename'] = $realfilename;
                $fileDetails['filetype'] = $filetype;
                $fileDetails['filesize'] = $filesize;
                $fileDetails['filecontents'] = base64_encode(file_get_contents($filenamewithpath));
                $response->setResult($fileDetails);
            } else {
                throw new Exception('Download not supported.', 1412);
            }

            return $response;
        }
    }

    /**
     * Function to update the download count of a file
     */
    function updateDownloadCount($id)
    {
        global $adb, $log;
        $log->debug("Entering customer portal function updateDownloadCount");
        $updateDownloadCount = "UPDATE vtiger_notes SET filedownloadcount = filedownloadcount+1 WHERE notesid = ?";
        $countres = $adb->pquery($updateDownloadCount, [$id]);
        $log->debug("Entering customer portal function updateDownloadCount");

        return true;
    }
}
