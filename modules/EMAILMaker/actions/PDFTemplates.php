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
 * The PDF Maker templates the template edit offers after its module was changed.
 */
class EMAILMaker_PDFTemplates_Action extends Core_Controller_Action
{
    /**
     * Only a user who may edit EMAIL Maker templates.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        if (!EMAILMaker_EMAILMaker_Model::getInstance()->checkPermissions('EDIT')) {
            throw new Exception(vtranslate('LBL_PERMISSION', 'EMAILMaker'));
        }

        return true;
    }

    /**
     * @param Vtiger_Request $request source_module
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $options = [];

        foreach (EMAILMaker_PDFTemplate_Model::getAvailableTemplates((string)$request->get('source_module')) as $templateId => $templateName) {
            $options[] = ['id' => $templateId, 'name' => $templateName];
        }

        $response = new Vtiger_Response();
        $response->setResult(['templates' => $options]);
        $response->emit();
    }
}
