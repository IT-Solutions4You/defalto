<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Appointments_Integration_Action extends Settings_Vtiger_Index_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('Widget');
        $this->exposeMethod('Field');
    }

    public function Widget(Vtiger_Request $request)
    {
    }

    public function Field(Vtiger_Request $request)
    {
        $integration = Settings_Appointments_Integration_Model::getInstance($request->get('field_module'));

        if ('true' === $request->get('field_checked')) {
            $integration->setField();
            $integration->setRelation();
        } else {
            $integration->unsetField();
            $integration->unsetRelation();
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => vtranslate('LBL_FIELD_UPDATE', $request->getModule(false))
        ]);
        $response->emit();
    }
}