<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Vtiger_Theme extends Vtiger_Viewer
{
    /**
     * Function to get the path of a given style sheet or default style sheet
     *
     * @param <String> $fileName
     *
     * @return <string / Boolean> - file path , false if not exists
     */
    public static function getStylePath($fileName = '', $theme = '')
    {
        // Default CSS for better performance, LESS format for development.
        if (empty($fileName)) {
            $fileName = 'style.css';
        }
        $filePath = self::getThemePath($theme) . '/' . $fileName;
        $lessFilePath = self::getThemePath($theme) . '/style.less';
        $fallbackPath = self::getBaseThemePath() . '/' . self::getDefaultThemeName() . '/' . 'style.less';

        $completeFilePath = Vtiger_Loader::resolveNameToPath('~' . $filePath);
        $completeLessFilePath = Vtiger_Loader::resolveNameToPath('~' . $lessFilePath);
        $completeFallBackPath = Vtiger_Loader::resolveNameToPath('~' . $fallbackPath);

        if (file_exists($completeFilePath)) {
            return $filePath;
        } elseif (file_exists($completeLessFilePath)) {
            return $lessFilePath;
        } elseif (file_exists($completeFallBackPath)) {
            return $fallbackPath;
        }

        // Exception should be thrown???
        return false;
    }

    /**
     * Function to get the image path
     * This checks image in selected theme if not in images folder if it doest nor exists either case will retutn false
     *
     * @param <string> $imageFileName - file name with extension
     *
     * @return <string/boolean> - returns file path if exists or false;
     */
    public static function getImagePath($imageFileName)
    {
        $imageFilePath = self::getThemePath() . '/' . 'images' . '/' . $imageFileName;
        $fallbackPath = self::getBaseThemePath() . '/' . 'images' . '/' . $imageFileName;
        $completeImageFilePath = Vtiger_Loader::resolveNameToPath('~' . $imageFilePath);
        $completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~' . $fallbackPath);

        if (file_exists($completeImageFilePath)) {
            return $imageFilePath;
        } elseif (file_exists($completeFallBackThemePath)) {
            return $fallbackPath;
        }

        return false;
    }

    /**
     * Function to get the Base Theme Path, until theme folder not selected theme folder
     * @return <string> - theme folder
     */
    public static function getBaseThemePath()
    {
        return 'layouts' . '/' . self::getLayoutName() . '/skins';
    }

    /**
     * Function to get the selected theme folder path
     * @return <string> -  selected theme path
     */
    public static function getThemePath($theme = '')
    {
        // Commented to get the default skins path for a layout
        if (empty($theme)) {
            $theme = self::getDefaultThemeName();
        }

        $selectedThemePath = self::getBaseThemePath() . '/' . $theme;
        $fallBackThemePath = self::getBaseThemePath() . '/' . self::getDefaultThemeName();

        $completeSelectedThemePath = Vtiger_Loader::resolveNameToPath('~' . $selectedThemePath);
        $completeFallBackThemePath = Vtiger_Loader::resolveNameToPath('~' . $fallBackThemePath);

        if (file_exists($completeSelectedThemePath)) {
            return $selectedThemePath;
        } elseif (file_exists($completeFallBackThemePath)) {
            return $fallBackThemePath;
        }

        return false;
    }

    /**
     * Function to get the default theme name
     * @return <String> - Default theme name
     */
    public static function getDefaultThemeName()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $theme = $currentUserModel->get('theme');

        return empty($theme) ? self::DEFAULTTHEME : $theme;
    }

    /**
     * Function to returns all skins(themes)
     * @return <Array>
     */
    public static function getAllSkins()
    {
        return Vtiger_Util_Helper::getAllSkins();
    }

    /**
     * Function returns the current users skin(theme) path
     */
    public static function getCurrentUserThemePath()
    {
        $themeName = self::getDefaultThemeName();
        $baseLayoutPath = self::getBaseThemePath();

        return $baseLayoutPath . '/' . $themeName;
    }

    public static function getv7AppStylePath($appTheme = false)
    {
        if (empty($appTheme)) {
            $appTheme = 'MARKETING';
        }

        return Vtiger_Theme::getStylePath('', strtolower($appTheme));
    }
}

function vimage_path($imageName)
{
    $args = func_get_args();

    return call_user_func_array(array('Vtiger_Theme', 'getImagePath'), $args);
}