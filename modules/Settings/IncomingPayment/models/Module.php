<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_IncomingPayment_Module_Model extends Settings_Vtiger_Module_Model
{
    public $name = 'IncomingPayment';

    /**
     * Returns the settings module model of incoming payments.
     *
     * @param string $moduleName Module name
     * @return self
     */
    public static function getInstance($moduleName = 'IncomingPayment'): self
    {
        return new self();
    }

    /**
     * Returns the URL of the integration settings page.
     *
     * @return string
     */
    public function getDefaultUrl(): string
    {
        return 'index.php?module=IncomingPayment&parent=Settings&view=Index';
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
