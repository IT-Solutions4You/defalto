<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class DragAndDrop_UploadModal_View extends Vtiger_IndexAjax_View
{
    /**
     * Upload creates Documents records and relates them to the source record.
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'targetModule', 'action' => 'CreateView'];
        $permissions[] = [
            'module_parameter' => 'sourceModule',
            'action' => 'DetailView',
            'record_parameter' => 'sourceRecord',
        ];

        return $permissions;
    }

    /**
     * Renders the upload modal with native Documents fields.
     *
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $recordModel = Vtiger_Record_Model::getCleanInstance('Documents');
        $moduleModel = $recordModel->getModule();
        $this->getViewer($request)
            ->assign('MODULE', 'Documents')
            ->assign('OUR_MODULE', 'DragAndDrop')
            ->assign('FIELD_MODELS', $moduleModel->getFields())
            ->assign('PARENT_MODULE', $request->get('sourceModule'))
            ->assign('PARENT_ID', $request->get('sourceRecord'))
            ->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel())
            ->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize())
            ->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes())
            ->view('UploadModal.tpl', 'DragAndDrop');
    }
}
