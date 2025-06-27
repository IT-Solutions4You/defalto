<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Install_Index_view extends Vtiger_View_Controller {

	protected $debug = false;
	protected $viewer = null;

	function loginRequired() {
		return false;
	}

	public function __construct() {
		$this->exposeMethod('Step1');
		$this->exposeMethod('Step2');
		$this->exposeMethod('Step3');
		$this->exposeMethod('Step4');
		$this->exposeMethod('Step5');
		$this->exposeMethod('Step6');
		$this->exposeMethod('Step7');
	}

	protected function applyInstallFriendlyEnv() {
		// config.inc.php - will not be ready to control this yet.
		// error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);  // Production
		set_time_limit(0); // override limits on execution time to allow installation process to finish
	}

	public function preProcess(Vtiger_Request $request, $display = true) {
		$this->applyInstallFriendlyEnv();

		date_default_timezone_set('Europe/London'); // to overcome the pre configuration settings

		parent::preProcess($request, false);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

        if (!$request->isEmpty('lang')) {
            $_SESSION['config_file_info']['default_language'] = $request->get('lang');
        } elseif (empty($_SESSION['config_file_info']['default_language'])) {
            $_SESSION['config_file_info']['default_language'] = 'en_us';
        }

        vglobal('default_language', $_SESSION['config_file_info']['default_language']);

		define('INSTALLATION_MODE', true);
		define('INSTALLATION_MODE_DEBUG', $this->debug);
		$viewer->view('InstallPreProcess.tpl', $moduleName);
	}

	public function process(Vtiger_Request $request) {
		global $default_charset;$default_charset='UTF-8';
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->$mode($request);
		}
		$this->Step1($request);
	}

	public function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->view('InstallPostProcess.tpl', $moduleName);
	}

	public function Step1(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('CURRENT_LANGUAGE', vglobal('default_language'));
		$viewer->assign('LANGUAGES', Install_Utils_model::getLanguageList());
		$viewer->view('Step1.tpl', $moduleName);
	}

	public function Step2(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->view('Step2.tpl', $moduleName);
	}

	public function Step3(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('FAILED_FILE_PERMISSIONS', Install_Utils_Model::getFailedPermissionsFiles());
		$viewer->assign('PHP_INI_CURRENT_SETTINGS', Install_Utils_Model::getCurrentDirectiveValue());
		$viewer->assign('PHP_INI_RECOMMENDED_SETTINGS', Install_Utils_Model::getRecommendedDirectives());
		$viewer->assign('SYSTEM_PREINSTALL_PARAMS', Install_Utils_Model::getSystemPreInstallParameters());
		$viewer->view('Step3.tpl', $moduleName);
	}

	public function Step4(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$viewer->assign('CURRENCIES', Install_Utils_Model::getCurrencyList());
		$viewer->assign('DECIMAL_SEPARATORS', Install_Utils_Model::getDecimalList());
		$viewer->assign('GROUPING_SEPARATORS', Install_Utils_Model::getGroupingList());
		$viewer->assign('DATE_FORMATS', Install_Utils_Model::getDateFormats());
		$viewer->assign('TIMEZONES', Install_Utils_Model::getTimeZones());

		$defaultParameters = Install_Utils_Model::getDefaultPreInstallParameters();

		$viewer->assign('DEFAULT_PARAMETERS', $defaultParameters);
		$viewer->assign('DB_HOSTNAME', $defaultParameters['db_hostname']);
		$viewer->assign('DB_USERNAME', $defaultParameters['db_username']);
		$viewer->assign('DB_PASSWORD', $defaultParameters['db_password']);
		$viewer->assign('DB_NAME', $defaultParameters['db_name']);
		$viewer->assign('ADMIN_NAME', $defaultParameters['admin_name']);
		$viewer->assign('ADMIN_LASTNAME', $defaultParameters['admin_lastname']);
		$viewer->assign('ADMIN_FIRSTNAME', $defaultParameters['admin_firstname']);
		$viewer->assign('ADMIN_PASSWORD', $defaultParameters['admin_password']);
		$viewer->assign('ADMIN_EMAIL', $defaultParameters['admin_email']);
		$viewer->assign('DATE_FORMAT', $defaultParameters['date_format']);
		$viewer->assign('TIMEZONE', $defaultParameters['timezone']);
		$viewer->assign('DECIMAL_SEPARATOR', $defaultParameters['currency_decimal_separator']);
		$viewer->assign('GROUPING_SEPARATOR', $defaultParameters['currency_grouping_separator']);

		$runtime_configs = Vtiger_Runtime_Configs::getInstance();
		$password_regex = $runtime_configs->getValidationRegex('password_regex');
		$viewer->assign('PWD_REGEX', $password_regex);

		$viewer->view('Step4.tpl', $moduleName);
	}

	public function Step5(Vtiger_Request $request) {
		set_time_limit(0); // Override default limit to let install complete.
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$requestData = $request->getAll();

		foreach($requestData as $name => $value) {
			$_SESSION['config_file_info'][$name] = $value;
		}

		$rootUser = '';
		$rootPassword = '';

		$createDataBase = false;
		$createDB = $request->get('create_db');
		if($createDB == 'on') {
			$rootUser = $request->get('db_username');
			$rootPassword = $request->get('db_password');
			$createDataBase = true;
		}
		$authKey = $_SESSION['config_file_info']['authentication_key'] = md5(microtime());

		$dbConnection = Install_Utils_Model::checkDbConnection('mysqli', $request->get('db_hostname'),
			$request->get('db_username'), $request->get('db_password'), $request->get('db_name'),
			$createDataBase, true, $rootUser, $rootPassword);

		$webRoot = ($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"]:$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
		$webRoot .= $_SERVER["REQUEST_URI"];

		$webRoot = str_replace( "index.php", "", $webRoot);
		$webRoot = (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? "https://":"http://").$webRoot;

		$_SESSION['config_file_info']['site_URL'] = $webRoot;
		$viewer->assign('SITE_URL', $webRoot);

		$_SESSION['config_file_info']['root_directory'] = getcwd().'/';

		$currencies = Install_Utils_Model::getCurrencyList();
		$currencyName = $request->get('currency_name');
		if(isset($currencyName)) {
			$_SESSION['config_file_info']['currency_code'] = $currencies[$currencyName][0];
			$_SESSION['config_file_info']['currency_symbol'] = $currencies[$currencyName][1];
		}

        $_SESSION['config_file_info']['currency_decimal_separator'] = $request->get('currency_decimal_separator');
        $_SESSION['config_file_info']['currency_grouping_separator'] = $request->get('currency_grouping_separator');

		$viewer->assign('DB_CONNECTION_INFO', $dbConnection);
		$viewer->assign('INFORMATION', $requestData);
		$viewer->assign('AUTH_KEY', $authKey);
		$viewer->view('Step5.tpl', $moduleName);
	}

	public function Step6(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$viewer->assign('DEFAULT_PARAMETERS', $_SESSION['config_file_info']);
		$viewer->assign('AUTH_KEY', $_SESSION['config_file_info']['authentication_key']);
		$viewer->view('Step6.tpl', $moduleName);
	}

    /**
     * @throws AppException
     * @throws Exception
     */
    public function Step7(Vtiger_Request $request) {
        $moduleName = $request->getModule();
		$webuiInstance = new Vtiger_WebUI();
		$isInstalled = $webuiInstance->isInstalled();
		if(!$isInstalled){
			if($_SESSION['config_file_info']['authentication_key'] != $request->get('auth_key')) {
				die(vtranslate('ERR_NOT_AUTHORIZED_TO_PERFORM_THE_OPERATION', $moduleName));
			}

            Install_InitSchema_Model::$request = $request;

			$viewer = $this->getViewer($request);
			$viewer->assign('PASSWORD', $_SESSION['config_file_info']['password']);
			$viewer->assign('APPUNIQUEKEY', $this->retrieveConfiguredAppUniqueKey());
			$viewer->assign('CURRENT_VERSION', $_SESSION['vtiger_version']);
			$viewer->assign('INDUSTRY', $request->get('industry'));
			$viewer->view('Step7.tpl', $moduleName);
		}else{
			$response = new Vtiger_Response();
			$response->setResult(vtranslate('THIS_INSTANCE_IS_ALREADY_INSTALLED', $moduleName));
			return $response;
		}
	}

	// Helper function as configuration file is still not loaded.
	protected function retrieveConfiguredAppUniqueKey() {
		include 'config.inc.php';

        return $application_unique_key ?? '';
	}

	public function getHeaderCss(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$parentCSSScripts = parent::getHeaderCss($request);
		$styleFileNames = array(
			"~/layouts/v7/modules/$moduleName/resources/css/style.css",
		);
		$cssScriptInstances = $this->checkAndConvertCssStyles($styleFileNames);
		$headerCSSScriptInstances = array_merge($parentCSSScripts, $cssScriptInstances);
		return $headerCSSScriptInstances;
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$parentScripts = parent::getHeaderScripts($request);
		$jsFileNames = array("modules.Vtiger.resources.List",
							 "modules.Vtiger.resources.Popup",
							 "modules.$moduleName.resources.Index");
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($parentScripts, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function validateRequest(Vtiger_Request $request) { 
		return $request->validateWriteAccess(true); 
	}
}
