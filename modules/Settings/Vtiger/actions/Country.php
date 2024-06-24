<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Settings_Vtiger_Country_Action extends Settings_Vtiger_Index_Action
{
    public function activateAll(Vtiger_Request $request)
    {
        $countryModel = Vtiger_Country_Model::getInstance();
        $countryModel->activateAll();

        $response = new Vtiger_Response();
        $response->setResult([
            'message' => vtranslate('LBL_ALL_COUNTRIES_HAVE_BEEN_ACTIVATED'),
            'success' => true,
        ]);
        $response->emit();
    }

    public function deactivateAll(Vtiger_Request $request)
    {
        $countryModel = Vtiger_Country_Model::getInstance();
        $countryModel->deactivateAll();

        $response = new Vtiger_Response();
        $response->setResult([
            'message' => vtranslate('LBL_ALL_COUNTRIES_HAVE_BEEN_DEACTIVATED'),
            'success' => true,
        ]);
        $response->emit();
    }

    public function update(Vtiger_Request $request)
    {
        $countryModel = Vtiger_Country_Model::getInstance();
        $countryModel->set('countries', [
            $request->get('value') => 'true' === $request->get('is_active') ? 1 : 0,
        ]);
        $countryModel->save();

        $response = new Vtiger_Response();
        $response->setResult([
            'message' => vtranslate('LBL_COUNTRY_UPDATED'),
            'success' => true,
        ]);
        $response->emit();
    }

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('update');
        $this->exposeMethod('activateAll');
        $this->exposeMethod('deactivateAll');
    }
}