<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

/**
 * Mail Scanner information manager.
 */
class Settings_MailConverter_MailScannerInfo_Handler {
	// id of this scanner record
    public int $scannerid = 0;
    // name of this scanner
    public $scannername = false;
    // mail server to connect to
    public $server = false;
    // mail protocol to use
    public $protocol = false;
    // username to use
    public $username = false;
    // password to use
    public $password = false;
    // notls/tls/ssl
    public $ssltype = false;
    // validate-certificate or novalidate-certificate
    public $sslmethod = false;
    // last successful connection url to use
    public $connecturl = false;
    // search for type
    public $searchfor = false;
    // post scan mark record as
    public $markas = false;
    // server time_zone
    public $time_zone = false;

    // is the scannered enabled?
    public $isvalid = false;

    // Last scan on the folders.
    public $lastscan = false;

    // Need rescan on the folders?
    public $rescan = false;

    // Rules associated with this mail scanner
    public $rules = false;
    /**
     * @var array|int|mixed|string|string[]|null
     */
    public string $mail_proxy = '';
    public string $client_id = '';
    public string $client_secret = '';
    public string $client_token = '';
    public string $client_access_token = '';

    /**
     * @throws AppException
     */
    public static function getInstance($scannername, $initialize = true): self
    {
        $instance = new self();

        if ($initialize && $scannername) {
            $instance->initialize($scannername);
        }

        return $instance;
    }

    /**
	 * Encrypt/Decrypt input.
	 * @access private
	 */
	function __crypt($password, $encrypt=true) {
		require_once('include/utils/encryption.php');
		$cryptobj = new Encryption();
		if($encrypt) return $cryptobj->encrypt(trim($password));
		else return $cryptobj->decrypt(trim($password));
	}

    /**
     * Initialize this instance.
     * @throws AppException
     */
	public function initialize($scannername) {
		global $adb;
		$result = $adb->pquery('SELECT * FROM vtiger_mailscanner WHERE scannername=?', Array($scannername));
        $row = $adb->fetchByAssoc($result);

		if($row) {
            $this->scannerid  = $row['scannerid'];
            $this->scannername= $row['scannername'];
            $this->server     = $row['server'];
            $this->protocol   = $row['protocol'];
            $this->username   = $row['username'];
            $this->password   = $row['password'];
            $this->password   = $this->__crypt($this->password, false);
            $this->ssltype    = $row['ssltype'];
            $this->sslmethod  = $row['sslmethod'];
            $this->connecturl = $row['connecturl'];
            $this->searchfor  = $row['searchfor'];
            $this->markas     = $row['markas'];
            $this->isvalid    = $row['isvalid'];
            $this->time_zone   = $row['time_zone'];
            $this->client_id   = decode_html($row['client_id']);
            $this->client_secret   = decode_html($row['client_secret']);
            $this->client_token   = decode_html($row['client_token']);
            $this->client_access_token   = decode_html($row['client_access_token']);

			$this->initializeFolderInfo();
			$this->initializeRules();
            $this->retrieveClientAccessToken();
		}
	}

	/**
	 * Initialize the folder details
	 */
	function initializeFolderInfo() {
		global $adb;
		if($this->scannerid) {
			$this->lastscan = Array();
			$this->rescan   = Array();
			$lastscanres = $adb->pquery("SELECT * FROM vtiger_mailscanner_folders WHERE scannerid=?",Array($this->scannerid));
			$lastscancount = $adb->num_rows($lastscanres);
			if($lastscancount) {
				for($lsindex = 0; $lsindex < $lastscancount; ++$lsindex) {
					$folder = $adb->query_result($lastscanres, $lsindex, 'foldername');
					$scannedon =$adb->query_result($lastscanres, $lsindex, 'lastscan');
					$nextrescan =$adb->query_result($lastscanres, $lsindex, 'rescan');
					$this->lastscan[$folder] = $scannedon;
					$this->rescan[$folder]   = ($nextrescan == 0)? false : true;
				}
			}
		}
	}

	/**
	 * Delete lastscan details with this scanner
	 */
	function clearLastscan() {
		global $adb;
		$adb->pquery("DELETE FROM vtiger_mailscanner_folders WHERE scannerid=?", Array($this->scannerid));
		$this->lastscan = false;
	}

	/**
	 * Update rescan flag on all folders
	 */
	function updateAllFolderRescan($rescanFlag=false) {
		global $adb;
		$useRescanFlag = $rescanFlag? 1 : 0;
		$adb->pquery("UPDATE vtiger_mailscanner_folders set rescan=? WHERE scannerid=?",
			Array($rescanFlag, $this->scannerid));
		if($this->rescan) {
			foreach($this->rescan as $folderName=>$oldRescanFlag) {
				$this->rescan[$folderName] = $rescanFlag;
			}
		}
	}

	function dateBasedOnMailServerTimezone($format='d-M-Y') {
		$returnDate = NULL;
		##--Fix for trac : http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/8051-## 
                if ($this->timezone && trim($this->timezone)) { 
			$currentTZ = date_default_timezone_get();
			[$tzhours, $tzminutes] = explode(':', trim($this->time_zone));
			$returnDate = date($format, strtotime(sprintf("%s hours %s minutes", $tzhours, $tzminutes)));
			date_default_timezone_set($currentTZ);
		} else {
			// Search email one-day before to overcome timezone differences.
			$returnDate = date($format, strtotime("-1 day"));
		}
		return $returnDate;
	}

	/**
	 * Update lastscan information on folder (or set for rescan next)
	 */
	function updateLastscan($folderName, $rescanFolder=false, $enabledForScan=1) {
		global $adb;

		$scannedOn = $this->dateBasedOnMailServerTimezone('d-M-Y');

		$needRescan = $rescanFolder? 1 : 0;

		$folderInfo = $adb->pquery("SELECT folderid FROM vtiger_mailscanner_folders WHERE scannerid=? AND foldername=?",
			Array($this->scannerid, $folderName));
		if($adb->num_rows($folderInfo)) {
			$folderid = $adb->query_result($folderInfo, 0, 'folderid');
			$adb->pquery("UPDATE vtiger_mailscanner_folders SET lastscan=?, rescan=? WHERE folderid=?",
				Array($scannedOn, $needRescan, $folderid));
		} else {
			$adb->pquery("INSERT INTO vtiger_mailscanner_folders(scannerid, foldername, lastscan, rescan, enabled)
			   VALUES(?,?,?,?,?)", Array($this->scannerid, $folderName, $scannedOn, $needRescan, $enabledForScan));
		}
		if(!$this->lastscan) $this->lastscan = Array();
		$this->lastscan[$folderName] = $scannedOn;

		if(!$this->rescan) $this->rescan = Array();
		$this->rescan[$folderName] = $needRescan;
	}

	/**
	 * Get lastscan of the folder.
	 */
	function getLastscan($folderName) {
		if($this->lastscan) return $this->lastscan[$folderName];
		else return false;
	}

	/**
	 * Does the folder need message rescan?
	 */
	function needRescan($folderName) {
		if($this->rescan && isset($this->rescan[$folderName])) {
			return $this->rescan[$folderName];
		}
		// TODO Pick details of rescan flag of folder from database?
		return false;
	}

	/**
	 * Check if rescan is required atleast on a folder?
	 */
	function checkRescan() {
		$rescanRequired = false;
		if($this->rescan) {
			foreach($this->rescan as $folderName=>$rescan) {
				if($rescan) {
					$rescanRequired = $folderName;
					break;
				}
			}
		}
		return $rescanRequired;
	}

	/**
	 * Get the folder information that has been scanned
	 */
	function getFolderInfo() {
		$folderinfo = false;
		if($this->scannerid) {
			global $adb;
			$fldres = $adb->pquery("SELECT * FROM vtiger_mailscanner_folders WHERE scannerid=?", Array($this->scannerid));
			$fldcount = $adb->num_rows($fldres);
			if($fldcount) {
				$folderinfo = Array();
				for($index = 0; $index < $fldcount; ++$index) {
					$foldername = $adb->query_result($fldres, $index, 'foldername');
					$folderid   = $adb->query_result($fldres, $index, 'folderid');
					$lastscan   = $adb->query_result($fldres, $index, 'lastscan');
					$rescan     = $adb->query_result($fldres, $index, 'rescan');
					$enabled    = $adb->query_result($fldres, $index, 'enabled');
					$folderinfo[$foldername] = Array ('folderid'=>$folderid, 'lastscan'=>$lastscan, 'rescan'=> $rescan, 'enabled'=>$enabled);
				}
			}
		}
		return $folderinfo;
	}

	/**
	 * Update the folder information with given folder names
	 */
	function updateFolderInfo($foldernames, $rescanFolder=false) {
		if($this->scannerid && !empty($foldernames)) {
			global $adb;
			$qmarks = Array();
			foreach($foldernames as $foldername) {
				$qmarks[] = '?';
				$this->updateLastscan($foldername, $rescanFolder);
			}
			// Delete the folder that is no longer present
			$adb->pquery("DELETE FROM vtiger_mailscanner_folders WHERE scannerid=? AND foldername NOT IN
				(". implode(',', $qmarks) . ")", Array($this->scannerid, $foldernames));
		}
	}

	/**
	 * Enable only given folders for scanning
	 */
	function enableFoldersForScan($folderinfo) {
		if($this->scannerid) {
			global $adb;
			$adb->pquery("UPDATE vtiger_mailscanner_folders set enabled=0 WHERE scannerid=?", Array($this->scannerid));
			foreach($folderinfo as $foldername=>$foldervalue) {
				$folderid = $foldervalue["folderid"];
				$enabled  = $foldervalue["enabled"];
				$adb->pquery("UPDATE vtiger_mailscanner_folders set enabled=? WHERE folderid=? AND scannerid=?",
					Array($enabled,$folderid,$this->scannerid));
			}
		}
	}

	/**
	 * Initialize scanner rule information
	 */
	function initializeRules() {
		global $adb;
		if($this->scannerid) {
			$this->rules = Array();
			$rulesres = $adb->pquery("SELECT * FROM vtiger_mailscanner_rules WHERE scannerid=? ORDER BY sequence",Array($this->scannerid));
			$rulescount = $adb->num_rows($rulesres);
			if($rulescount) {
				for($index = 0; $index < $rulescount; ++$index) {
					$ruleid = $adb->query_result($rulesres, $index, 'ruleid');
					$scannerrule = new Settings_MailConverter_MailScannerRule_Handler($ruleid);
					$scannerrule->debug = $this->debug;
					$this->rules[] = $scannerrule;
				}
			}
		}
	}

	/**
	 * Get scanner information as map
	 */
	function getAsMap()
    {
        $infomap = [];
        $keys = [
            'scannerid',
            'scannername',
            'server',
            'protocol',
            'username',
            'password',
            'client_id',
            'client_secret',
            'client_token',
            'client_access_token',
            'ssltype',
            'sslmethod',
            'connecturl',
            'searchfor',
            'markas',
            'isvalid',
            'time_zone',
            'rules',
        ];
        foreach ($keys as $key) {
            $infomap[$key] = $this->$key;
        }
        $infomap['requireRescan'] = $this->checkRescan();

        return $infomap;
    }

    /**
     * Compare this instance with give instance
	 */
    function compare($otherInstance)
    {
        $checkkeys = ['server', 'scannername', 'protocol', 'username', 'password', 'client_id', 'client_token', 'client_secret', 'client_access_token', 'ssltype', 'sslmethod', 'searchfor', 'markas'];

        foreach ($checkkeys as $key) {
            if ($this->$key != $otherInstance->$key) {
                return false;
            }
        }

        return true;
    }

    /**
	 * Create/Update the scanner information in database
	 */
	function update($otherInstance) {
		$mailServerChanged = false;

		// Is there is change in server setup?
		if($this->server != $otherInstance->server || $this->username != $otherInstance->username) {
			$mailServerChanged = true;
			$this->clearLastscan();
			// TODO How to handle lastscan info if server settings switches back in future?
		}

		$this->server    = $otherInstance->server;
		$this->scannername= $otherInstance->scannername;
		$this->protocol  = $otherInstance->protocol;
		$this->username  = $otherInstance->username;
		$this->password  = $otherInstance->password;
		$this->ssltype   = $otherInstance->ssltype;
		$this->sslmethod = $otherInstance->sslmethod;
		$this->connecturl= $otherInstance->connecturl;
		$this->searchfor = $otherInstance->searchfor;
		$this->markas    = $otherInstance->markas;
		$this->isvalid   = $otherInstance->isvalid;
		$this->time_zone  = $otherInstance->time_zone;
        $this->mail_proxy = $otherInstance->mail_proxy;
		$this->client_id  = $otherInstance->client_id;
		$this->client_secret  = $otherInstance->client_secret;
		$this->client_token  = $otherInstance->client_token;
		$this->client_access_token  = $otherInstance->client_access_token;

        return $mailServerChanged;
	}

    public function save()
    {
        $params = [
            'scannername' => $this->scannername,
            'server' => $this->server,
            'protocol' => $this->protocol,
            'username' => $this->username,
            'password' => $this->__crypt($this->password),
            'ssltype' => $this->ssltype,
            'sslmethod' => $this->sslmethod,
            'connecturl' => $this->connecturl,
            'searchfor' => $this->searchfor,
            'markas' => $this->markas,
            'isvalid' => $this->isvalid ? 1 : 0,
            'time_zone' => $this->time_zone,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'client_token' => $this->client_token,
            'client_access_token' => $this->client_access_token,
        ];

        if (empty($this->scannerid)) {
            $this->scannerid = $this->getMailScannerTable()->insertData($params);
        } else { //this record is exist in the data
            $this->getMailScannerTable()->updateData($params, ['scannerid' => $this->scannerid]);
        }
    }

    public function getMailScannerTable() {
        return (new Core_DatabaseData_Model())->getTable('vtiger_mailscanner', 'scannerid');
    }

    public function createTables()
    {
        $this->getMailScannerTable()
            ->createTable()
            ->createColumn('scannername', 'varchar(30) DEFAULT NULL')
            ->createColumn('server', 'varchar(100) DEFAULT NULL')
            ->createColumn('protocol', 'varchar(10) DEFAULT NULL')
            ->createColumn('username', 'varchar(255) DEFAULT NULL')
            ->createColumn('password', 'varchar(255) DEFAULT NULL')
            ->createColumn('ssltype', 'varchar(10) DEFAULT NULL')
            ->createColumn('sslmethod', 'varchar(30) DEFAULT NULL')
            ->createColumn('connecturl', 'varchar(255) DEFAULT NULL')
            ->createColumn('searchfor', 'varchar(10) DEFAULT NULL')
            ->createColumn('markas', 'varchar(10) DEFAULT NULL')
            ->createColumn('isvalid', 'int(1) DEFAULT NULL')
            ->createColumn('scanfrom', 'varchar(10) DEFAULT \'ALL\'')
            ->createColumn('time_zone', 'varchar(10) DEFAULT NULL')
            ->createColumn('client_id', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_secret', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_token', 'text DEFAULT NULL')
            ->createColumn('client_access_token', 'text DEFAULT NULL');
    }

    /**
	 * Delete the scanner information from database
	 */
	function delete() {
		global $adb;

		// Delete dependencies
		if(!empty($this->rules)) {
			foreach($this->rules as $rule) {
				$rule->delete();
			}
		}

		if($this->scannerid) {
			$tables = Array(
				'vtiger_mailscanner',
				'vtiger_mailscanner_ids',
				'vtiger_mailscanner_folders'
			);
			foreach($tables as $table) {
				$adb->pquery("DELETE FROM $table WHERE scannerid=?", Array($this->scannerid));
			}
			$adb->pquery("DELETE FROM vtiger_mailscanner_ruleactions
				WHERE actionid in (SELECT actionid FROM vtiger_mailscanner_actions WHERE scannerid=?)", Array($this->scannerid));
			$adb->pquery("DELETE FROM vtiger_mailscanner_actions WHERE scannerid=?", Array($this->scannerid));
		}
	}

    /**
     * List all the mail-scanners configured.
     * @throws AppException
     */
    public static function getAll(): array
    {
        $scanners = [];
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT scannername FROM vtiger_mailscanner', []);

        while ($row = $adb->fetch_array($result)) {
            $scanners[] = self::getInstance(decode_html($row['scannername']));
        }

        return $scanners;
    }

    /**
     * @throws AppException
     */
    public function retrieveClientAccessToken(): void
    {
        if (empty($this->client_access_token)) {
            return;
        }

        try {
            $authModel = Core_Auth_Model::getInstance($this->client_id, $this->client_secret, $this->client_token);
            $authModel->setProviderByServer($this->server);
            $authModel->updateAccessToken($this);
        } catch (Exception $e) {
            throw new AppException('Update access token error: ' . ($e->getMessage()));
        }
    }

    /**
     * @param Core_Auth_Model $authModel
     * @return void
     * @throws AppException
     */
    public function updateAccessToken(Core_Auth_Model $authModel): void
    {
        if (!empty($this->client_access_token)) {
            $this->client_access_token = $authModel->getAccessToken();
        }
    }

    public function getId()
    {
        return $this->scannerid;
    }

    public function setId($value)
    {
        $this->scannerid = $value;
    }
}