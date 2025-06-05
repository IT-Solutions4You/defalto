<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Install_ConfigFileUtils_Model {

	private $rootDirectory;
	private $dbHostname;
	private $dbPort;
	private $dbUsername;
	private $dbPassword;
	private $dbName;
	private $dbType;
	private $siteUrl;
	private $cacheDir;
	private $vtCharset = 'UTF-8';
	private $vtDefaultLanguage = 'en_us';
	private $currencyName;
	private $adminEmail;
	private $currencyDecimalSeparator;
	private $currencyGroupingSeparator;

        function __construct($configFileParameters) {
            if (isset($configFileParameters['root_directory'])){
                $this->rootDirectory = $configFileParameters['root_directory'];
            }

        if (isset($configFileParameters['db_hostname'])) {
                    if(strpos($configFileParameters['db_hostname'], ":")) {
                            [$this->dbHostname,$this->dbPort] = explode(":",$configFileParameters['db_hostname']);
                    } else {
                            $this->dbHostname = $configFileParameters['db_hostname'];
                    }
            }

            if (isset($configFileParameters['db_username'])) $this->dbUsername = $configFileParameters['db_username'];
            if (isset($configFileParameters['db_password'])) $this->dbPassword = $configFileParameters['db_password'];
            if (isset($configFileParameters['db_name'])) $this->dbName = $configFileParameters['db_name'];
            if (isset($configFileParameters['db_type'])) $this->dbType = $configFileParameters['db_type'];
            if (isset($configFileParameters['site_URL'])) $this->siteUrl = $configFileParameters['site_URL'];
            if (isset($configFileParameters['admin_email'])) $this->adminEmail = $configFileParameters['admin_email'];
            if (isset($configFileParameters['currency_name'])) $this->currencyName = $configFileParameters['currency_name'];
            if (isset($configFileParameters['vt_charset'])) $this->vtCharset = $configFileParameters['vt_charset'];
            if (isset($configFileParameters['default_language'])) $this->vtDefaultLanguage = $configFileParameters['default_language'];
            if (isset($configFileParameters['currency_decimal_separator'])) $this->currencyDecimalSeparator = $configFileParameters['currency_decimal_separator'];
            if (isset($configFileParameters['currency_grouping_separator'])) $this->currencyGroupingSeparator = $configFileParameters['currency_grouping_separator'];

            // update default port
            if ($this->dbPort == '') $this->dbPort = self::getDbDefaultPort($this->dbType);

            $this->cacheDir = 'cache/';
        }   
	function Install_ConfigFileUtils_Model($configFileParameters) {
            self::__construct($configFileParameters);
	}

	static function getDbDefaultPort($dbType) {
		if(Install_Utils_Model::isMySQL($dbType)) {
			return "3306";
		}
	}

	function createConfigFile() {
		/* open template configuration file read only */
		$templateFilename = 'config.template.php';
		$templateHandle = fopen($templateFilename, "r");
		if($templateHandle) {
			/* open include configuration file write only */
			$includeFilename = 'config.inc.php';
	      	$includeHandle = fopen($includeFilename, "w");
			if($includeHandle) {
			   	while (!feof($templateHandle)) {
	  				$buffer = fgets($templateHandle);

		 			/* replace _DBC_ variable */
		  			$buffer = str_replace( "_DBC_SERVER_", $this->dbHostname, $buffer);
		  			$buffer = str_replace( "_DBC_PORT_", $this->dbPort, $buffer);
		  			$buffer = str_replace( "_DBC_USER_", $this->dbUsername, $buffer);
		  			$buffer = str_replace( "_DBC_PASS_", $this->dbPassword, $buffer);
		  			$buffer = str_replace( "_DBC_NAME_", $this->dbName, $buffer);
		  			$buffer = str_replace( "_DBC_TYPE_", $this->dbType, $buffer);

		  			$buffer = str_replace( "_SITE_URL_", $this->siteUrl, $buffer);

		  			/* replace dir variable */
		  			$buffer = str_replace( "_VT_ROOTDIR_", $this->rootDirectory, $buffer);
		  			$buffer = str_replace( "_VT_CACHEDIR_", $this->cacheDir, $buffer);
		  			$buffer = str_replace( "_VT_TMPDIR_", $this->cacheDir."images/", $buffer);
		  			$buffer = str_replace( "_VT_UPLOADDIR_", $this->cacheDir."upload/", $buffer);
			      	$buffer = str_replace( "_DB_STAT_", "true", $buffer);

					/* replace charset variable */
					$buffer = str_replace( "_VT_CHARSET_", $this->vtCharset, $buffer);

					/* replace default lanugage variable */
					$buffer = str_replace( "_VT_DEFAULT_LANGUAGE_", $this->vtDefaultLanguage, $buffer);

			      	/* replace master currency variable */
		  			$buffer = str_replace( "_MASTER_CURRENCY_", $this->currencyName, $buffer);

			      	/* replace the application unique key variable */
		      		$buffer = str_replace( "_VT_APP_UNIQKEY_", md5(sprintf("%d%s", (time() + rand(1,9999999)), md5($this->rootDirectory))) , $buffer);

					/* replace support email variable */
                    $buffer = str_replace( "_USER_SUPPORT_EMAIL_", $this->adminEmail, $buffer);

                    /* replace users config */
					$buffer = str_replace( "_CURRENCY_DECIMAL_SEPARATOR_", addslashes($this->currencyDecimalSeparator), $buffer);
					$buffer = str_replace( "_CURRENCY_GROUPING_SEPARATOR_", addslashes($this->currencyGroupingSeparator), $buffer);

		      		fwrite($includeHandle, $buffer);
	      		}
	  			fclose($includeHandle);
	  		}
	  		fclose($templateHandle);
	  	}

	  	if ($templateHandle && $includeHandle) {
	  		return true;
	  	}
	  	return false;
	}

    function getConfigFileContents()
    {
        return '';
    }
}
