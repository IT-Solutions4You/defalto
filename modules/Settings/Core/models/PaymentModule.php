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
 * Settings module model of a payment module: its integration settings page. Each payment module extends it as
 * Settings_<Module>_Module_Model.
 */
abstract class Settings_Core_PaymentModule_Model extends Settings_Vtiger_Module_Model
{
    /**
     * Sets the payment module name, also when the core creates the model (Settings_Vtiger_Module_Model::getInstance()).
     *
     * @param array $values Model values
     * @throws Exception
     */
    public function __construct($values = [])
    {
        parent::__construct($values);
        $this->name = Core_PaymentConfig_Helper::getForClass(static::class)::MODULE;
    }

    /**
     * Returns the settings module model of the payment module.
     *
     * @param string $moduleName Module name
     * @return static
     */
    public static function getInstance($moduleName = ''): static
    {
        return new static();
    }

    /**
     * Returns the URL of the integration settings page.
     *
     * @return string
     * @throws Exception
     */
    public function getDefaultUrl(): string
    {
        return Core_PaymentConfig_Helper::getForClass(static::class)->getSettingsUrl();
    }

    /**
     * The settings page has no additional setting links.
     *
     * @return array
     */
    public function getSettingLinks(): array
    {
        return [];
    }
}
