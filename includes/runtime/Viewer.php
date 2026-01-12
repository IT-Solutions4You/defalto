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

class Vtiger_Viewer extends Smarty
{
    const DEFAULTLAYOUT = 'd1';
    const DEFAULTTHEME = 'softed';
    static $currentLayout;

    // Turn-it on to analyze the data pushed to templates for the request.
    protected static $debugViewer = false;

    /**
     * log message into the file if in debug mode.
     *
     * @param type $message
     * @param type $delimiter
     */
    protected function log($message, $delimiter = "\n")
    {
        static $file = null;
        if ($file == null) {
            $file = dirname(__FILE__) . '/../../logs/viewer-debug.log';
        }
        if (self::$debugViewer) {
            file_put_contents($file, $message . $delimiter, FILE_APPEND);
        }
    }

    /**
     * Constructor - Sets the templateDir and compileDir for the Smarty files
     *
     * @param <String> - $media Layout/Media name
     */
    function __construct($media = '')
    {
        parent::__construct();

        $THISDIR = dirname(__FILE__);

        $templatesDir = '';
        $compileDir = '';
        if (!empty($media)) {
            self::$currentLayout = $media;
            $templatesDir = $THISDIR . '/../../layouts/' . $media;
            $compileDir = $THISDIR . '/../../test/templates_c/' . $media;
        }
        if (!$templatesDir || !file_exists($templatesDir)) {
            self::$currentLayout = self::getDefaultLayoutName();
            $templatesDir = $THISDIR . '/../../layouts/' . self::getDefaultLayoutName();
            $compileDir = $THISDIR . '/../../test/templates_c/' . self::getDefaultLayoutName();
        }

        if (!file_exists($compileDir)) {
            mkdir($compileDir, 0777, true);
        }
        $this->setTemplateDir([$templatesDir]);
        $this->setCompileDir($compileDir);

        // FOR DEBUGGING: We need to have this only once.
        static $debugViewerURI = false;
        if (self::$debugViewer && $debugViewerURI === false) {
            $debugViewerURI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (!empty($_POST)) {
                $debugViewerURI .= '?' . http_build_query($_POST);
            } else {
                $debugViewerURI = $_SERVER['REQUEST_URI'];
            }

            $this->log("URI: $debugViewerURI, TYPE: " . $_SERVER['REQUEST_METHOD']);
        }

        $classes = [
            'Vtiger_MenuStructure_Model',
            'Users_Privileges_Model',
            'Vtiger_Module_Model',
            'Settings_MenuEditor_Module_Model',
            'Vtiger_Util_Helper',
            'ZEND_JSON',
            'Zend_Json',
            'Zend_JSON',
            'ZEND_json',
            'Vtiger_Theme',
            'Users_Record_Model',
            'Vtiger_Module_Model',
            'Vtiger_Field_Model',
            'Settings_Picklist_Module_Model',
            'CustomView_Record_Model',
            'Vtiger_Extension_View',
            'Vtiger_Tag_Model',
            'Vtiger_Functions',
            'Users',
            'CurrencyField'
        ];

        foreach ($classes as $clazz) {
            if (class_exists($clazz)) {
                $this->registerClass($clazz, $clazz);
            }
        }

        $modifiers = [
            'vtranslate',
            'vtlib_isModuleActive',
            'vimage_path',
            'strstr',
            'stripos',
            'strpos',
            'date',
            'vtemplate_path',
            'vresource_url',
            'decode_html',
            'vtlib_purify',
            'php7_count',
            'getUserFullName',
            'array_flip',
            'explode',
            'trim',
            'array_push',
            'array_map',
            'array_key_exists',
            'get_class',
            'vtlib_array',
            'getDuplicatesPreventionMessage',
            'htmlentities',
            'getCurrencySymbolandCRate',
            'mb_substr',
            'isPermitted',
            'getEntityName',
            'function_exists',
            'php7_trim',
            'php7_htmlentities',
            'strtolower',
            'strtoupper',
            'str_replace',
            'urlencode',
            'getTranslatedCurrencyString',
            'getTranslatedString',
            'is_object',
            'is_numeric'
        ];

        foreach ($modifiers as $modifier) {
            if (function_exists($modifier)) {
                $this->registerPlugin('modifier', $modifier, $modifier);
            }
        }
    }

    // Backward compatible to SmartyBC
    function get_template_vars($name = null)
    {
        return $this->getTemplateVars($name);
    }

    function safeHtmlFilter($content, $smarty)
    {
        //return htmlspecialchars($content,ENT_QUOTES,UTF-8);
        // NOTE: to_html is being used as data-extraction depends on this
        // We shall improve this as it plays role across the product.
        return to_html($content);
    }

    /**
     * Function to get the current layout name
     * @return <String> - Current layout name if not empty, otherwise Default layout name
     */
    public static function getLayoutName()
    {
        if (!empty(self::$currentLayout)) {
            return self::$currentLayout;
        }

        return self::getDefaultLayoutName();
    }

    /**
     * Function to return for default layout name
     * @return <String> - Default Layout Name
     */
    public static function getDefaultLayoutName()
    {
        return self::DEFAULTLAYOUT;
    }

    /**
     * Function to get the module specific template path for a given template
     *
     * @param <String> $templateName
     * @param <String> $moduleName
     *
     * @return <String> - Module specific template path if exists, otherwise default template path for the given template name
     */
    public function getTemplatePath($templateName, $moduleName = '')
    {
        $moduleName = str_replace(':', '/', $moduleName);
        $templateDir = rtrim($this->getTemplateDir(0), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $completeFilePath = $templateDir . "modules/$moduleName/$templateName";

        if (!empty($moduleName) && file_exists($completeFilePath)) {
            return "modules/$moduleName/$templateName";
        } else {
            // Fall back lookup on actual module, in case where parent module doesn't contain actual module within in (directory structure)
            if (strpos($moduleName, '/') > 0) {
                $moduleHierarchyParts = explode('/', $moduleName);
                $actualModuleName = $moduleHierarchyParts[php7_count($moduleHierarchyParts) - 1];
                $baseModuleName = $moduleHierarchyParts[0];
                $fallBackOrder = [
                    "$actualModuleName",
                    "$baseModuleName/Vtiger",
                ];

                foreach ($fallBackOrder as $fallBackModuleName) {
                    $intermediateFallBackFileName = 'modules/' . $fallBackModuleName . '/' . $templateName;
                    $intermediateFallBackFilePath = $templateDir . $intermediateFallBackFileName;

                    if (file_exists($intermediateFallBackFilePath)) {
                        return $intermediateFallBackFileName;
                    }
                }
            }

            $coreTemplateName = 'modules/Core/' . $templateName;
            $coreFilePath = $templateDir . $coreTemplateName;

            if (file_exists($coreFilePath)) {
                return $coreTemplateName;
            }

            return "modules/Vtiger/$templateName";
        }
    }

    /** @Override */
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        // Reject unexpected value assignments.
        if ($tpl_var == 'SELECTED_MENU_CATEGORY') {
            if ($value && preg_match("/[^a-zA-Z0-9_-]/", $value, $m)) {
                return;
            }
        }

        return parent::assign($tpl_var, $value, $nocache);
    }

    /**
     * Function to display/fetch the smarty file contents
     *
     * @param <String>  $templateName
     * @param <String>  $moduleName
     * @param <Boolean> $fetch
     *
     * @return html data
     */
    public function view($templateName, $moduleName = '', $fetch = false)
    {
        $templatePath = $this->getTemplatePath($templateName, $moduleName);
        $templateFound = $this->templateExists($templatePath);

        // Logging
        if (self::$debugViewer) {
            $templatePathToLog = $templatePath;
            $qualifiedModuleName = str_replace(':', '/', $moduleName);
            // In case we found a fallback template, log both lookup and target template resolved to.
            if (!empty($moduleName) && strpos($templatePath, "modules/$qualifiedModuleName/") !== 0) {
                $templatePathToLog = "modules/$qualifiedModuleName/$templateName > $templatePath";
            }
            $this->log("VIEW: $templatePathToLog, FOUND: " . ($templateFound ? "1" : "0"));
            foreach ($this->tpl_vars as $key => $smarty_variable) {
                // Determine type of value being pased.
                $valueType = 'literal';
                if (is_object($smarty_variable->value)) {
                    $valueType = get_class($smarty_variable->value);
                } elseif (is_array($smarty_variable->value)) {
                    $valueType = 'array';
                }
                $this->log(sprintf("DATA: %s, TYPE: %s", $key, $valueType));
            }
        }
        // END

        if ($templateFound) {
            if ($fetch) {
                return $this->fetch($templatePath);
            } else {
                $this->display($templatePath);
            }

            return true;
        }

        return false;
    }

    /**
     * Static function to get the Instance of the Class Object
     *
     * @param <String> $media Layout/Media
     *
     * @return Vtiger_Viewer instance
     */
    static function getInstance($media = '')
    {
        $instance = new self($media);

        return $instance;
    }

}

function vtemplate_path($templateName, $moduleName = '')
{
    $viewerInstance = Vtiger_Viewer::getInstance();
    $args = func_get_args();

    return call_user_func_array([$viewerInstance, 'getTemplatePath'], $args);
}

/**
 * Generated cache friendly resource URL linked with version of Vtiger
 */
function vresource_url($url)
{
    global $defalto_current_version;

    if (stripos($url, '://') === false) {
        $url = $url . '?v=' . $defalto_current_version;
    }

    if (stripos($url, '$LAYOUT$') !== false) {
        $url = str_replace('$LAYOUT$', Vtiger_Viewer::getLayoutName(), $url);
    }

    return $url;
}

function getPurifiedSmartyParameters($param)
{
    return htmlentities($_REQUEST[$param]);
}