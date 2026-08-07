<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Country_Import_Action extends Settings_Vtiger_Index_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('run');
    }

    /**
     * Download + import the GeoNames postal dataset (optionally a single country,
     * passed as a 2-letter "country_code").
     */
    public function run(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $result = Settings_Country_Update_Model::getInstance()->run((string)$request->get('country_code'));

            $response->setResult([
                'success'        => true,
                'message'        => vtranslate('LBL_POSTAL_CODES_UPDATED', $request->getModule(false)),
                'version'        => $result['version'],
                'rows'           => $result['rows'],
                'countriesAdded' => count($result['countriesAdded']),
            ]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }
}
