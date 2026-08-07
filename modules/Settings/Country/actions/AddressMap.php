<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * AJAX endpoint for the per-module address field mapping UI (Settings ▸ Country).
 *  - getModuleConfig: address fields + saved groups for the chosen module,
 *  - save: persist the groups for the chosen module.
 */
class Settings_Country_AddressMap_Action extends Settings_Vtiger_Index_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('getModuleConfig');
        $this->exposeMethod('save');
    }

    public function getModuleConfig(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        try {
            $config = Settings_Country_AddressMap_Model::getInstance()->getModuleConfig((string)$request->get('target_module'));
            $response->setResult($config);
        } catch (Throwable $e) {
            $response->setError((int)$e->getCode(), $e->getMessage());
        }

        $response->emit();
    }

    public function save(Vtiger_Request $request)
    {
        $targetModule = (string)$request->get('target_module');
        $groups = $request->get('groups');

        // The groups arrive JSON-encoded from the client.
        if (is_string($groups)) {
            $groups = json_decode($groups, true);
        }

        if (!is_array($groups)) {
            $groups = [];
        }

        $response = new Vtiger_Response();

        try {
            $saved = Settings_Country_AddressMap_Model::getInstance()->saveMapping($targetModule, $groups);

            $response->setResult([
                'success' => true,
                'message' => vtranslate('LBL_ADDRESS_MAPPING_SAVED', $request->getModule(false)),
                'groups' => count($saved),
            ]);
        } catch (Throwable $e) {
            // Truthful failure: success=false + the real reason, no stray output.
            $response->setError((int)$e->getCode(), $e->getMessage());
        }

        $response->emit();
    }
}
