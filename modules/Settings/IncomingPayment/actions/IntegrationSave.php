<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_IncomingPayment_IntegrationSave_Action extends Settings_Vtiger_IndexAjax_View
{
    /**
     * Write action: POST with a CSRF token only.
     *
     * @param Vtiger_Request $request
     * @return bool
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Stores the submitted integration configuration and re-registers the document module integrations.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $integration = $request->get('integration');
        $installModel = Core_Install_Model::getInstance('module.postupdate', IncomingPayment_Config_Helper::MODULE);
        // The old configuration is removed first, so a changed paid field of a document becomes editable again.
        $installModel->updateIntegrations(false);
        IncomingPayment_Integration_Model::getInstance()->saveFromRequest(is_array($integration) ? $integration : []);
        $installModel->updateIntegrations();

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
