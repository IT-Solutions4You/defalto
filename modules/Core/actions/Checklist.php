<?php
/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
class Core_Checklist_Action extends Core_Controller_Action
{
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    public function checkPermission(Vtiger_Request $request): bool
    {
        Core_Checklist_Model::getInstance()->getContext((int)$request->get('record'), (int)$request->get('field'), true);

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $result = Core_Checklist_Model::getInstance()->updateChecklist(
            (int)$request->get('record'), (int)$request->get('field'), (string)$request->get('operation'),
            (int)$request->get('item'), (string)$request->get('revision'), $request->getRaw('text'), $request->get('checked')
        );
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
