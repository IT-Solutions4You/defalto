<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Utils_Helper
{
    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function count(mixed $value): int
    {
        if (is_array($value)) {
            return count($value);
        }

        return 0;
    }

    /**
     * @param string $module
     *
     * @return string
     * @throws Exception
     */
    public static function getTermsAndConditions(string $module): string
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT tandc FROM vtiger_inventory_tandc WHERE type = ?', [$module]);

        return (string)$adb->query_result($result, 0, 'tandc');
    }

    /**
     * @param string $string
     * @param string $search
     *
     * @return bool
     */
    public static function searchInString(string $string, string $search): bool
    {
        $string = self::simplifyString($string);
        $search = self::simplifyString($search);

        return str_contains($string, $search);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function simplifyString(string $string): string
    {
        $string = strtolower($string);

        return str_replace([' ', ',', '.'], ['', '', ''], $string);
    }

    /**
     * @throws Exception
     */
    public static function getBranding(): string
    {
        if (Installer_License_Model::isMembershipActive()) {
            return '';
        }

        return '<span class="ms-2">Developed by IT-Solutions4You</span>
            <a target="_blank" href="https://www.facebook.com/defalto.crm" class="bi bi-facebook ms-2 text-primary"></a>
            <a target="_blank" href="https://www.linkedin.com/company/defalto" class="bi bi-linkedin ms-2 text-primary-emphasis"></a>
            <a target="_blank" href="https://www.youtube.com/@DefaltoCRM" class="bi bi-youtube ms-2 text-danger"></a>';
    }

    /**
     * @throws Exception
     */
    public static function getLogo(): string
    {
        if (Installer_License_Model::isMembershipActive()) {
            $logo = Settings_Vtiger_CompanyDetails_Model::getInstance()->getLogoPath();
        }

        return !empty($logo) ? $logo : 'layouts/d1/resources/Images/login-logo.png';
    }
}