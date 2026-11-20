<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class IncomingPayment_Module_Model extends Vtiger_Module_Model
{
    protected string $fontIcon = 'fa-solid fa-money-bill-trend-up';

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
            'linklabel' => 'LBL_INCOMING_PAYMENT_INTEGRATION',
            'linkurl' => 'index.php?module=IncomingPayment&parent=Settings&view=Index',
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
        $paymentsModule = Vtiger_Module_Model::getInstance(IncomingPayment_Config_Helper::PAYMENTS_LIST_MODULE);

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
        $paymentsModule = Vtiger_Module_Model::getInstance(IncomingPayment_Config_Helper::PAYMENTS_LIST_MODULE);

        if (!$this->isPaymentsListAvailable($paymentsModule)) {
            return '';
        }

        return vtranslate(IncomingPayment_Config_Helper::PAYMENTS_LIST_MODULE, IncomingPayment_Config_Helper::PAYMENTS_LIST_MODULE);
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
