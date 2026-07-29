<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_Module_Model extends Vtiger_Module_Model
{
    public function getDatabaseTables(): array
    {
        return [
            'df_two_factor_config',
            'df_two_factor_user',
            'df_two_factor_backup_code',
            'df_two_factor_email_template',
        ];
    }

    public function getDefaultUrl(): string
    {
        return 'index.php?module=TwoFactorAuthentication&parent=Settings&view=Index';
    }

    public function getSettingLinks(): array
    {
        return [];
    }

    public function getPicklistFields(): array
    {
        return [];
    }

    public static function isActiveLogin(): bool
    {
        try {
            return vtlib_isModuleActive('TwoFactorAuthentication')
                && TwoFactorAuthentication_Service_Helper::isActive();
        } catch (Throwable $e) {
            return false;
        }
    }
}
