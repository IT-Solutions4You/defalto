<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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