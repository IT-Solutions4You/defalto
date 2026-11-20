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
 * Module model shared by the payment modules: settings link, icon and the header link to the Payments list.
 * The payment module subclass (<Module>_Module_Model) sets the icon from its configuration.
 */
abstract class Core_PaymentModule_Model extends Vtiger_Module_Model
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
     * Adds the integration settings page to the list view settings menu.
     *
     * @return array
     */
    public function getSettingLinks(): array
    {
        $settingsLinks = parent::getSettingLinks();
        $settingsLinks[] = [
            'linktype' => 'LISTVIEWSETTING',
            'linklabel' => $this->getConfig()::INTEGRATION_LABEL,
            'linkurl' => $this->getConfig()->getSettingsUrl(),
            'linkicon' => '',
        ];

        return $settingsLinks;
    }

    /**
     * Returns the list the module title in the page header links to.
     * While the Payments list is available, it links there, because payments are browsed in Payments.
     *
     * @param string $category Menu app of the page
     * @return string
     */
    public function getCustomViewUrl(string $category = ''): string
    {
        $paymentsModule = Vtiger_Module_Model::getInstance(Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE);

        if ($this->isPaymentsListAvailable($paymentsModule)) {
            return $paymentsModule->getCustomViewUrl($category);
        }

        return parent::getCustomViewUrl($category);
    }

    /**
     * Returns the module name shown in the page header of a payment: Payments while that list is available,
     * otherwise an empty string, which keeps the module's own name.
     *
     * @return string
     */
    public function getBreadcrumbLabel(): string
    {
        $paymentsModule = Vtiger_Module_Model::getInstance(Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE);

        if (!$this->isPaymentsListAvailable($paymentsModule)) {
            return '';
        }

        return vtranslate(Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE, Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE);
    }

    /**
     * Tells whether the Payments list is installed, active and open to the current user.
     *
     * @param Vtiger_Module_Model|false $paymentsModule Payments module model, or false when it is not installed
     * @return bool
     */
    public function isPaymentsListAvailable($paymentsModule): bool
    {
        return $paymentsModule && $paymentsModule->isActive() && $paymentsModule->isPermitted('index');
    }
}
