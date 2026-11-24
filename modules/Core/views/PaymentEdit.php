<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Core_PaymentEdit_View extends Vtiger_Edit_View
{
    /**
     * Returns the configuration of the payment module this class belongs to.
     *
     * @return Core_PaymentConfig_Helper
     * @throws Exception
     */
    protected function getConfig(): Core_PaymentConfig_Helper
    {
        return Core_PaymentConfig_Helper::getForClass(static::class);
    }

    /**
     * Shows Payments as the module name in the page header while the Payments list is available,
     * then renders the standard edit header.
     *
     * @param Vtiger_Request $request
     * @param bool           $display Whether to render the header template
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $breadcrumbLabel = Vtiger_Module_Model::getInstance($request->getModule())->getBreadcrumbLabel();

        if ($breadcrumbLabel !== '') {
            $this->getViewer($request)->assign('MODULE_BREADCRUMB_LABEL', $breadcrumbLabel);
        }

        parent::preProcess($request, $display);
    }

    /**
     * Prefills a new payment created from a document (open amount, currency, due date, organisation, contact)
     * and renders the standard edit view.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        if ($request->isEmpty('record') && !$request->isEmpty('sourceModule') && !$request->isEmpty('sourceRecord')) {
            Core_PaymentPrefill_Helper::setRequestValues($this->getConfig(), $request, (string)$request->get('sourceModule'), (int)$request->get('sourceRecord'));
        }

        parent::process($request);
    }

    /**
     * Loads the buy window of the Installer while the paid edition is locked, so choosing a second document in the
     * Reference field offers the paid edition (Core_PaymentMultireference_UIType::isMaximumRecordsPremium()).
     *
     * @param Vtiger_Request $request
     * @return array
     * @throws Exception
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScripts = parent::getHeaderScripts($request);

        if (Core_PaymentLicense_Helper::isLimitPremium() && vtlib_isModuleActive('Installer')) {
            $headerScripts += $this->checkAndConvertJsScripts(['modules.Installer.resources.PremiumModal']);
        }

        return $headerScripts;
    }
}
