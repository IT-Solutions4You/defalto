<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Body_View extends Vtiger_Basic_View
{
    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $field = $request->get('field', 'body');
        $recordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $request->get('record_module'));
        $body = decode_html($recordModel->get($field));

        echo ITS4YouEmails_Utils_Helper::updateShorUrlData($body);
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $request->set('record_module', getSalesEntityType($request->get('record')));

        $permissions = [];
        $permissions[] = ['module_parameter' => 'record_module', 'action' => 'DetailView', 'record_parameter' => 'record'];

        return $permissions;
    }
}