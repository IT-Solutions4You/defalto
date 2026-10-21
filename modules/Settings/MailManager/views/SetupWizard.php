<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_SetupWizard_View extends Settings_MailManager_ProAbstract_View
{
    /**
     * Renders the administrator setup wizard.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        foreach (Settings_MailManager_SetupWizard_Model::getWizardData() as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName)
            ->view('SetupWizard.tpl', $qualifiedModuleName);
    }

    /**
     * Adds the wizard script after the parent scripts.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts(['modules.Settings.MailManager.resources.SetupWizard'])
        );
    }
}
