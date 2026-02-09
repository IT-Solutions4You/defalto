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
        if ('Install' !== $_REQUEST['module'] && Installer_License_Model::isMembershipActive()) {
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

    /**
     * @param string $moduleName
     * @return bool
     */
    public static function isModuleActive(string $moduleName): bool
    {
        return getTabid($moduleName) && vtlib_isModuleActive($moduleName);
    }

    public static function getRootDirectory(): string
    {
        global $root_directory;

        return rtrim($root_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public static function retrieveKCFinderConfig(): void
    {
        if (empty(vglobal('site_URL'))) {
            return;
        }

        $_SESSION['KCFINDER']['uploadURL'] = vglobal('site_URL') . 'test/upload';
        $_SESSION['KCFINDER']['uploadDir'] = vglobal('root_directory') . 'test/upload';
        $_SESSION['KCFINDER']['deniedExts'] = implode(' ', (array)vglobal('upload_badext'));
    }

    public static function markdownToHtml($text): string
    {
        $text = decode_html($text);
        $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);
        $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^---$/m', '<hr>', $text);
        $text = preg_replace('/\[!\[(.*?)\]\((.*?)\)\]\((.*?)\)/', '<a href="$3"><img src="$2" alt="$1"></a>', $text);
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a class="text-primary" href="$2">$1</a>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);

        return nl2br($text);
    }

    public static function getReadmeFileContents(): string
    {
        $value = file_get_contents('README.md');

        if (empty($value)) {
            return '';
        }

        return self::markdownToHtml($value);
    }

    public static function getLicenseFileContents(): string
    {
        $value = file_get_contents('LICENSE.md');

        if (empty($value)) {
            return '';
        }

        return self::markdownToHtml($value);
    }
}