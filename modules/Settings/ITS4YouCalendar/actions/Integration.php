<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Settings_ITS4YouCalendar_Integration_Action extends Settings_Vtiger_Index_Action
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
        $integration = Settings_ITS4YouCalendar_Integration_Model::getInstance($request->get('field_module'));

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