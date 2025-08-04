<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_DownloadFile_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        return parent::checkPermission($request);
    }

    public function process(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();

        $attachmentId = $request->get('attachment_id');
        $name = $request->get('name');
        $query = "SELECT * FROM vtiger_attachments WHERE attachmentsid = ? AND name = ?";
        $result = $db->pquery($query, [$attachmentId, $name]);

        if ($db->num_rows($result) == 1) {
            $row = $db->fetchByAssoc($result, 0);
            $fileType = $row["type"];
            $name = $row["name"];
            $filepath = $row["path"];
            $name = decode_html($name);
            $storedFileName = $row['storedname'];
            if (!empty($name)) {
                if (!empty($storedFileName)) {
                    $saved_filename = $attachmentId . "_" . $storedFileName;
                } elseif (is_null($storedFileName)) {
                    $saved_filename = $attachmentId . "_" . $name;
                }

                $disk_file_size = filesize($filepath . $saved_filename);
                $filesize = $disk_file_size + ($disk_file_size % 1024);
                $fileContent = fread(fopen($filepath . $saved_filename, "r"), $filesize);

                header("Content-type: $fileType");
                header("Pragma: public");
                header("Cache-Control: private");
                header("Content-Disposition: attachment; filename=$name");
                header("Content-Description: PHP Generated Data");
                echo $fileContent;
            }
        }
    }

    public function requiresPermission(\Vtiger_Request $request)
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }
}