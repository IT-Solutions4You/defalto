<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_TwoFactorAuthentication_Module_Model extends Settings_Vtiger_Module_Model
{
    public $name = 'TwoFactorAuthentication';

    public static function getInstance($moduleName = 'TwoFactorAuthentication'): self
    {
        return new self();
    }

    public function getDefaultUrl(): string
    {
        return 'index.php?module=TwoFactorAuthentication&parent=Settings&view=Index';
    }

    public function getSettingLinks(): array
    {
        return [];
    }
}
