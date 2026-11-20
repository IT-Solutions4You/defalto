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
 * Integration settings page of a payment module (index.php?module=<Module>&parent=Settings&view=Index).
 * Each payment module extends it as Settings_<Module>_Index_View.
 */
abstract class Settings_Core_PaymentIntegration_View extends Settings_Vtiger_Index_View
{
    /**
     * Returns the configuration of the payment module this page belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Renders the integration settings of the payment module for every available document module.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $config = $this->getConfig();

        $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName)
            ->assign('INTEGRATION_LABEL', $config::INTEGRATION_LABEL)
            ->assign('INTEGRATION_ICON', $config->getIcon())
            ->assign('INTEGRATION_ROWS', Core_PaymentIntegrationFields_Helper::getRows($config))
            ->assign('INTEGRATION_COLUMNS', Core_PaymentConfig_Helper::INTEGRATION_COLUMNS)
            ->assign('UNDERPAYMENT_THRESHOLD', $config->getSettingModel()->getUnderpaymentThreshold())
            ->view('PaymentIntegration.tpl', 'Settings:Core');
    }

    /**
     * Loads the shared integration script before the module script (Settings_<Module>_Index_Js extends it).
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScripts = parent::getHeaderScripts($request);
        $moduleScript = 'modules.Settings.' . $request->getModule() . '.resources.Index';
        $moduleScriptInstance = $headerScripts[$moduleScript] ?? null;
        unset($headerScripts[$moduleScript]);
        $headerScripts += $this->checkAndConvertJsScripts(['modules.Settings.Core.resources.PaymentIntegration']);

        if ($moduleScriptInstance) {
            $headerScripts[$moduleScript] = $moduleScriptInstance;
        }

        return $headerScripts;
    }
}
