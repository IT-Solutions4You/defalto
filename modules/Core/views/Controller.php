<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Core_Controller_View extends Core_Controller_Action
{
    protected ?Vtiger_Viewer $viewer = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Viewer
     */
    public function getViewer(Vtiger_Request $request): Vtiger_Viewer
    {
        if ($this->viewer) {
            return $this->viewer;
        }

        global $defalto_current_version, $defalto_display_version, $current_user, $maxListFieldsSelectionSize;
        $viewer = new Vtiger_Viewer();
        $viewer->assign('REQUEST_INSTANCE', $request);
        $viewer->assign('APPTITLE', getTranslatedString('APPTITLE'));
        $viewer->assign('VTIGER_VERSION', $defalto_current_version);
        $viewer->assign('VTIGER_DISPLAY_VERSION', $defalto_display_version);
        $viewer->assign('MAX_LISTFIELDS_SELECTION_SIZE', isset($maxListFieldsSelectionSize) ? max(3, $maxListFieldsSelectionSize) : 15);

        // Defaults to avoid warning
        // General
        $viewer->assign('MODULE_NAME', $request->getModule());
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('VIEW', '');
        $viewer->assign('PARENT_MODULE', '');
        $viewer->assign('EXTENSION_MODULE', '');
        $viewer->assign('moduleName', '');
        $viewer->assign('CURRENT_USER_ID', $current_user ? $current_user->id : '');

        $viewer->assign('NOTIFIER_URL', '');
        $viewer->assign('GLOBAL_SEARCH_VALUE', '');
        $_REQUEST['view'] = $_REQUEST['view'] ?? '';

        // Listview
        $viewer->assign('SEARCH_MODE_RESULTS', null);
        $viewer->assign('ACTIVE', false);   // Tag
        $viewer->assign('BUTTON_NAME', ''); // footer Buttom (for custom action)
        $viewer->assign('BUTTON_ID', '');
        $viewer->assign('NO_EDIT', '');
        $viewer->assign('SOURCE_MODULE', '');
        $viewer->assign('OPERATOR', '');
        $viewer->assign('LISTVIEW_COUNT', 0);
        $viewer->assign('FOLDER_ID', 0);
        $viewer->assign('FOLDER_VALUE', '');
        $viewer->assign('VIEWTYPE', '');
        $viewer->assign('PRINT_TEMPLATE', '');
        $viewer->assign('CLASS_VIEW_ACTION', '');
        $viewer->assign('RELATED_MODULE_NAME', '');

        // Editview
        $viewer->assign('RECORD_ID', '');
        $viewer->assign('RETURN_VIEW', '');
        $viewer->assign('MASS_EDITION_MODE', false);
        $viewer->assign('OCCUPY_COMPLETE_WIDTH', true);
        $viewer->assign('VIEW_SOURCE', false);
        $viewer->assign('IGNOREUIREGISTRATION', false);
        $viewer->assign('IMAGE_DETAILS', null);

        // DetailView
        $viewer->assign('NO_DELETE', false);
        $viewer->assign('IS_EXTERNAL_LOCATION_TYPE', false);

        // RelatedLists
        $viewer->assign('TOTAL_ENTRIES', 0);

        // Popupview
        $viewer->assign('IS_MODULE_DISABLED', false);

        // Widgets
        $viewer->assign('SCRIPTS', []);
        $viewer->assign('STYLES', []);
        $viewer->assign('SETTING_EXIST', false);
        $viewer->assign('SELECTED_MENU_CATEGORY', Settings_MenuEditor_Module_Model::getActiveApp($request->get('app')));

        $this->viewer = $viewer;

        return $this->viewer;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return string
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        $recordName = null;

        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if ($recordId && $moduleName) {
            $module = Vtiger_Module_Model::getInstance($moduleName);

            if ($module && $module->isEntityModule()) {
                $recordName = Vtiger_Util_Helper::getRecordName($recordId);
            }
        }

        if ($recordName) {
            return vtranslate($moduleName, $moduleName) . ' - ' . $recordName;
        }

        $currentLang = Vtiger_Language_Handler::getLanguage();
        $customWebTitle = Vtiger_Language_Handler::getLanguageTranslatedString($currentLang, 'LBL_' . $moduleName . '_WEBTITLE', $request->getModule(false));

        if ($customWebTitle) {
            return $customWebTitle;
        }

        return vtranslate($moduleName, $moduleName);
    }

    /**
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        $viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
        $viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
        $viewer->assign('LANGUAGE', $currentUser->get('language'));

        if ($request->getModule() != 'Install') {
            $userCurrencyInfo = getCurrencySymbolandCRate($currentUser->get('currency_id'));
            $viewer->assign('USER_CURRENCY_SYMBOL', $userCurrencyInfo['symbol']);
        }

        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'preProcess', $request->getModule(), $viewer, $request);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return string
     */
    protected function preProcessTplName(Vtiger_Request $request): string
    {
        return 'Header.tpl';
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return string
     */
    protected function postProcessTplName(Vtiger_Request $request): string
    {
        return 'Footer.tpl';
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function preProcessDisplay(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->view($this->preProcessTplName($request), $request->getModule(false));
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    protected function postProcessDisplay(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->view($this->postProcessTplName($request), $request->getModule(false));
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $viewer = $this->getViewer($request);
        $viewer->assign('ACTIVITY_REMINDER', $currentUser->getCurrentUserActivityReminderInSeconds());

        Core_Modifiers_Model::modifyForClass(get_class($this), 'postProcess', $request->getModule(), $viewer, $request);

        $this->postProcessDisplay($request);
    }

    /**
     * Retrieves header JS scripts that need to load in the page
     *
     * @param Vtiger_Request $request - request model
     *
     * @return array of Vtiger_JsScript_Model
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = [];
        $languageHandlerShortName = Vtiger_Language_Handler::getShortLanguageName();
        $fileName = 'libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-' . $languageHandlerShortName . '.js';

        if (!file_exists($fileName)) {
            $fileName = '~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js';
        } else {
            $fileName = '~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/languages/jquery.validationEngine-' . $languageHandlerShortName . '.js';
        }

        $jsFileNames = [
            '~libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js',
            $fileName,
        ];

        Core_Modifiers_Model::modifyVariableForClass(get_class($this), 'getHeaderScripts', $request->getModule(), $jsFileNames, $request);

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($jsScriptInstances, $headerScriptInstances);
    }

    /**
     * @param array $jsFileNames
     *
     * @return array
     */
    public function checkAndConvertJsScripts(array $jsFileNames): array
    {
        $fileExtension = 'js';

        $jsScriptInstances = [];

        foreach ($jsFileNames as $jsFileName) {
            // TODO Handle absolute inclusions (~/...) like in checkAndConvertCssStyles
            $jsScript = new Vtiger_JsScript_Model();

            // external JavaScript source file handling
            if (str_starts_with($jsFileName, 'http://') || str_starts_with($jsFileName, 'https://')) {
                $jsScriptInstances[$jsFileName] = $jsScript->set('src', $jsFileName);
                continue;
            }

            $completeFilePath = Vtiger_Loader::resolveNameToPath($jsFileName, $fileExtension);

            if (file_exists($completeFilePath)) {
                if (str_starts_with($jsFileName, '~')) {
                    $filePath = ltrim(ltrim($jsFileName, '~'), '/');
                    // if ~~ (reference is outside vtiger folder)
                    if (substr_count($jsFileName, '~') == 2) {
                        $filePath = '../' . $filePath;
                    }
                } else {
                    $filePath = str_replace('.', '/', $jsFileName) . '.' . $fileExtension;
                }

                $jsScriptInstances[$jsFileName] = $jsScript->set('src', $filePath);
            } else {
                $fallBackFilePath = Vtiger_Loader::resolveNameToPath(Vtiger_JavaScript::getBaseJavaScriptPath() . '/' . $jsFileName, 'js');
                if (file_exists($fallBackFilePath)) {
                    $filePath = str_replace('.', '/', $jsFileName) . '.js';
                    $jsScriptInstances[$jsFileName] = $jsScript->set('src', Vtiger_JavaScript::getFilePath($filePath));
                }
            }
        }

        return $jsScriptInstances;
    }

    /**
     * Function returns the CSS files
     *
     * @param array  $cssFileNames
     * @param string $fileExtension
     *
     * @return array of Vtiger_CssScript_Model
     *
     * First check if $cssFileName exists
     * If not, check under the layout folder $cssFileName e.g.:layouts/vlayout/$cssFileName
     */
    public function checkAndConvertCssStyles(array $cssFileNames, string $fileExtension = 'css'): array
    {
        $cssStyleInstances = [];

        foreach ($cssFileNames as $cssFileName) {
            $cssScriptModel = new Vtiger_CssScript_Model();

            if (str_starts_with($cssFileName, 'http://') || str_starts_with($cssFileName, 'https://')) {
                $cssStyleInstances[] = $cssScriptModel->set('href', $cssFileName);
                continue;
            }

            $completeFilePath = Vtiger_Loader::resolveNameToPath($cssFileName, $fileExtension);

            if (file_exists($completeFilePath)) {
                if (str_starts_with($cssFileName, '~')) {
                    $filePath = ltrim(ltrim($cssFileName, '~'), '/');
                    // if ~~ (reference is outside vtiger6 folder)
                    if (substr_count($cssFileName, '~') == 2) {
                        $filePath = '../' . $filePath;
                    }
                } else {
                    $filePath = str_replace('.', '/', $cssFileName) . '.' . $fileExtension;
                    $filePath = Vtiger_Theme::getStylePath($filePath);
                }

                $cssStyleInstances[] = $cssScriptModel->set('href', $filePath);
            }
        }

        return $cssStyleInstances;
    }

    /**
     * Retrieves CSS styles that need to load in the page
     *
     * @param Vtiger_Request $request - request model
     *
     * @return array of Vtiger_CssScript_Model
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $cssFileNames = [];

        Core_Modifiers_Model::modifyVariableForClass(get_class($this), 'getHeaderCss', $request->getModule(), $cssFileNames, $request);

        return $this->checkAndConvertCssStyles($cssFileNames);
    }

    /**
     * Function returns the Client side language string
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getJSLanguageStrings(Vtiger_Request $request): array
    {
        $moduleName = $request->getModule(false);

        if ($moduleName === 'Settings:Users') {
            $moduleName = 'Users';
        }

        return Vtiger_Language_Handler::export($moduleName, 'jsLanguageStrings');
    }
}