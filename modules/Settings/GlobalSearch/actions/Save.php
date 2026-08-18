<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_GlobalSearch_Save_Action extends Settings_Vtiger_Index_Action
{
    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $configuration = $request->get('configuration', []);

        if (is_string($configuration)) {
            $configuration = json_decode($configuration, true, 512, JSON_THROW_ON_ERROR);
        }

        if (!is_array($configuration)) {
            throw new InvalidArgumentException(vtranslate('LBL_GLOBAL_SEARCH_INVALID_CONFIGURATION', $request->getModule(false)));
        }

        GlobalSearch_Configuration_Model::getInstance()->saveConfiguration($configuration);

        $response = new Vtiger_Response();
        $response->setResult([
            'success' => true,
            'message' => vtranslate('LBL_GLOBAL_SEARCH_SETTINGS_SAVED', $request->getModule(false)),
        ]);
        $response->emit();
    }
}
