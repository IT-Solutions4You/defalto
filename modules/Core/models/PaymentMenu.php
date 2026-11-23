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
 * Visibility of the payment modules in the menu. While the Payments list is installed, the menu shows only Payments;
 * the payment modules are hidden in their application like in the menu editor and keep everything else (parent app,
 * quick create, lists, related lists). Each module is hidden once; a module the administrator shows again stays.
 */
class Core_PaymentMenu_Model
{
    /**
     * Returns a new instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * Hides the given payment modules in the menu, when Payments is installed and active and the module was not
     * hidden before.
     *
     * @param string[] $paymentModules Payment module names; empty for all installed payment modules
     * @return void
     * @throws Exception
     */
    public function hideModules(array $paymentModules = []): void
    {
        if (!vtlib_isModuleActive(Core_PaymentConfig_Helper::PAYMENTS_LIST_MODULE)) {
            return;
        }

        foreach ($this->getModules($paymentModules) as $paymentModule) {
            $settingModel = Core_PaymentConfig_Helper::getInstance($paymentModule)->getSettingModel();

            if ($settingModel->isMenuHidden()) {
                continue;
            }

            Settings_MenuEditor_Module_Model::setVisible($paymentModule, Core_PaymentConfig_Helper::MENU_APP, 0);
            $settingModel->saveMenuHidden(true);
        }
    }

    /**
     * Shows all installed payment modules in the menu again, when Payments is disabled or uninstalled.
     * The flags are cleared, so the modules are hidden again when Payments comes back.
     *
     * @return void
     * @throws Exception
     */
    public function showModules(): void
    {
        foreach ($this->getModules() as $paymentModule) {
            Settings_MenuEditor_Module_Model::setVisible($paymentModule, Core_PaymentConfig_Helper::MENU_APP, 1);
            Core_PaymentConfig_Helper::getInstance($paymentModule)->getSettingModel()->saveMenuHidden(false);
        }
    }

    /**
     * Returns the installed payment modules among the given ones, or all installed payment modules.
     *
     * @param string[] $paymentModules Payment module names; empty for all
     * @return string[]
     */
    protected function getModules(array $paymentModules = []): array
    {
        $modules = Core_PaymentConfig_Helper::getInstalledModules();

        return $paymentModules ? array_values(array_intersect($modules, $paymentModules)) : $modules;
    }
}
