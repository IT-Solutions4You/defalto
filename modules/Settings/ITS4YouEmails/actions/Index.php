<?php

class Settings_ITS4YouEmails_Index_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $referenceModuleName = $request->get('reference_module');
        $referenceModule = ITS4YouEmails_Integration_Model::getInstance($referenceModuleName);

        if ('true' === $request->get('reference_activate')) {
            $referenceModule->setReferenceModule();
            $referenceModule->updateRelation();

            $message = 'LBL_MODULE_ACTIVATED';
        } else {
            $referenceModule->unsetReferenceModule();
            $referenceModule->updateRelation(false);

            $message = 'LBL_MODULE_DEACTIVATED';
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'message' => vtranslate($message, $request->getModule(false)),
        ]);
        $response->emit();
    }
}