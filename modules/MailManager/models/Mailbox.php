<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Mailbox_Model {

	protected $mServer;
	public $mUsername;
	protected $mPassword;
	protected $mProtocol = 'IMAP4';
	protected $mSSLType  = 'ssl';
	protected $mCertValidate = 'novalidate-cert';
	protected $mRefreshTimeOut;
	protected $mId;
	protected $mServerName;
    protected $mFolder;
    protected string $mClientId = '';
    protected string $mClientSecret = '';
    protected string $mClientAccessToken = '';
    protected string $mClientToken = '';
    protected string $mProxy = '';

	public function exists() {
		return !empty($this->mId);
	}

	public function decrypt($value) {
		require_once('include/utils/encryption.php');
		$e = new Encryption();
		return $e->decrypt($value);
	}

	public function encrypt($value) {
		require_once('include/utils/encryption.php');
		$e = new Encryption();
		return $e->encrypt($value);
	}

    /**
     * @return mixed
     */
    public function getClientId(): string
    {
        return $this->mClientId;
    }

    /**
     * @return mixed
     */
    public function getClientSecret(): string
    {
        return $this->mClientSecret;
    }

    /**
     * @return mixed
     */
    public function getClientAccessToken(): string
    {
        return $this->mClientAccessToken;
    }

    /**
     * @return mixed
     */
    public function getClientToken(): string
    {
        return $this->mClientToken;
    }

    public function server() {
		return $this->mServer;
	}

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->mClientId = $clientId;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->mClientSecret = $clientSecret;
    }

    /**
     * @param string $clientAccessToken
     */
    public function setClientAccessToken(string $clientAccessToken): void
    {
        $this->mClientAccessToken = $clientAccessToken;
    }

    /**
     * @param string $clientToken
     */
    public function setClientToken(string $clientToken): void
    {
        $this->mClientToken = $clientToken;
    }

    /**
     * @param string $proxy
     */
    public function setProxy(string $proxy): void
    {
        $this->mProxy = $proxy;
    }

    public function setServer($server) {
		$this->mServer = trim($server);
	}

	public function serverName() {
		return $this->mServerName;
	}

	public function username() {
		return $this->mUsername;
	}

	public function setUsername($username) {
		$this->mUsername = trim($username);
	}

	public function password($decrypt=true) {
		if ($decrypt) return $this->decrypt($this->mPassword);
		return $this->mPassword;
	}

	public function setPassword($password) {
		$this->mPassword = $this->encrypt(trim($password));
	}

	public function protocol() {
		return $this->mProtocol;
	}

	public function setProtocol($protocol) {
		$this->mProtocol = trim($protocol);
	}

	public function ssltype() {
		if (strcasecmp($this->mSSLType, 'ssl') === 0) {
			return $this->mSSLType;
		}
		return $this->mSSLType;
	}

	public function setSSLType($ssltype) {
		$this->mSSLType = trim($ssltype);
	}

	public function certvalidate() {
		return $this->mCertValidate;
	}

	public function setCertValidate($certvalidate) {
		$this->mCertValidate = trim($certvalidate);
	}

	public function setRefreshTimeOut($value) {
		$this->mRefreshTimeOut = $value;
	}

	public function refreshTimeOut() {
		return $this->mRefreshTimeOut;
	}

    public function setFolder($value) {
		$this->mFolder = $value;
	}

	public function getFolder() {
		return decode_html($this->mFolder);
	}

	public function delete() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$db->pquery("DELETE FROM vtiger_mail_accounts WHERE user_id = ? AND account_id = ?", array($currentUserModel->getId(), $this->mId));
	}

    /**
     * @throws AppException
     */
    public function save()
    {
        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $account_id = 1;
        $maxresult = $db->pquery("SELECT max(account_id) as max_account_id FROM vtiger_mail_accounts", []);

        if ($db->num_rows($maxresult)) {
            $account_id += intval($db->query_result($maxresult, 0, 'max_account_id'));
        }

        $isUpdate = !empty($this->mId);
        $data = [
            'display_name' => $this->username(),
            'mail_servername' => $this->server(),
            'mail_username' => $this->username(),
            'mail_password' => $this->password(false),
            'mail_protocol' => $this->protocol(),
            'ssltype' => $this->ssltype(),
            'sslmeth' => $this->certvalidate(),
            'box_refresh' => $this->refreshTimeOut(),
            'sent_folder' => $this->getFolder(),
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'client_token' => $this->getClientToken(),
            'client_access_token' => $this->getClientAccessToken(),
        ];
        $userId = $currentUserModel->getId();
        $table = $this->getMailAccountTable();

        if ($isUpdate) {
            $table->updateData($data, ['user_id' => $userId, 'account_id' => $this->mId]);
        } else {
            $data['user_id'] = $userId;
            $data['mails_per_page'] = vglobal('list_max_entries_per_page');
            $data['account_name'] = $this->username();
            $data['status'] = 1;
            $data['set_default'] = '0';
            $data['account_id'] = $account_id;
            $table->insertData($data);
        }

        if (!$isUpdate) {
            $this->mId = $account_id;
        }
    }

    /**
     * @param object|bool $currentUserModel
     * @return MailManager_Mailbox_Model
     * @throws AppException
     */
    public static function getActiveInstance(object|bool $currentUserModel = false): object
    {
        $db = PearDatabase::getInstance();

        if (!$currentUserModel) {
            $currentUserModel = Users_Record_Model::getCurrentUserModel();
        }

        $instance = new MailManager_Mailbox_Model();

        $result = $db->pquery("SELECT * FROM vtiger_mail_accounts WHERE user_id=? AND status=1 AND set_default=0", [$currentUserModel->getId()]);

        if ($db->num_rows($result)) {
            $row = $db->fetchByAssoc($result, 0);
            $instance->mServer = trim($row['mail_servername']);
            $instance->mUsername = trim($row['mail_username']);
            $instance->mPassword = trim($row['mail_password']);
            $instance->mProtocol = trim($row['mail_protocol']);
            $instance->mSSLType = trim($row['ssltype']);
            $instance->mCertValidate = trim($row['sslmeth']);
            $instance->mId = trim($row['account_id']);
            $instance->mRefreshTimeOut = trim($row['box_refresh']);
            $instance->mFolder = trim($row['sent_folder']);
            $instance->mServerName = self::setServerName($instance->mServer);
            $instance->mClientId = decode_html(trim($row['client_id']));
            $instance->mClientSecret = decode_html(trim($row['client_secret']));
            $instance->mClientToken = decode_html(trim($row['client_token']));
            $instance->mClientAccessToken = decode_html(trim($row['client_access_token']));
            $instance->retrieveClientAccessToken();
        }

        return $instance;
    }

    public static function setServerName($mServer) {
		if($mServer == 'imap.gmail.com') {
			$mServerName = 'gmail';
		} else if($mServer == 'imap.mail.yahoo.com') {
			$mServerName = 'yahoo';
		} else if($mServer == 'mail.messagingengine.com') {
			$mServerName = 'fastmail';
		} else {
			$mServerName = 'other';
		}
		return $mServerName;
	}

    public function getMailAccountTable()
    {
        return (new Core_DatabaseData_Model())->getTable('vtiger_mail_accounts', 'account_id');
    }

    public function createTables()
    {
        $this->getMailAccountTable()
            ->createTable('account_id', 'int(11) NOT NULL')
            ->createColumn('user_id', 'int(11) NOT NULL')
            ->createColumn('display_name', 'varchar(50) DEFAULT NULL')
            ->createColumn('mail_id', 'varchar(50) DEFAULT NULL')
            ->createColumn('account_name', 'varchar(50) DEFAULT NULL')
            ->createColumn('mail_protocol', 'varchar(20) DEFAULT NULL')
            ->createColumn('mail_username', 'varchar(50) NOT NULL')
            ->createColumn('mail_password', 'text DEFAULT NULL')
            ->createColumn('mail_servername', 'varchar(50) DEFAULT NULL')
            ->createColumn('box_refresh', 'int(10) DEFAULT NULL')
            ->createColumn('mails_per_page', 'int(10) DEFAULT NULL')
            ->createColumn('ssltype', 'varchar(50) DEFAULT NULL')
            ->createColumn('sslmeth', 'varchar(50) DEFAULT NULL')
            ->createColumn('int_mailer', 'int(1) DEFAULT 0')
            ->createColumn('status', 'varchar(10) DEFAULT NULL')
            ->createColumn('set_default', 'int(2) DEFAULT NULL')
            ->createColumn('sent_folder', 'varchar(50) DEFAULT NULL')
            ->createColumn('client_id', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_secret', 'varchar(255) DEFAULT NULL')
            ->createColumn('client_token', 'text DEFAULT NULL')
            ->createColumn('client_access_token', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`account_id`)')
        ;
    }

    public function isOAuth(): bool
    {
        return !empty($this->getClientId());
    }

    /**
     * @throws AppException
     */
    public function retrieveClientAccessToken(): void
    {
        if (empty($this->getClientAccessToken())) {
            return;
        }

        try {
            $authModel = Core_Auth_Model::getInstance($this->getClientId(), $this->getClientSecret(), $this->getClientToken());
            $authModel->setProviderByServer($this->getServer());
            $authModel->updateAccessToken($this);
        } catch (Exception $e) {

        }
    }

    /**
     * @param Core_Auth_Model $authModel
     * @return void
     */
    public function updateAccessToken(Core_Auth_Model $authModel): void
    {
        if (!empty($this->getClientAccessToken())) {
            $this->setClientAccessToken($authModel->getAccessToken());
        }
    }

    public function getPort()
    {
        if (strcasecmp($this->protocol(), 'pop') === 0) {
            $port = 110; // NOT IMPLEMENTED
        } elseif (strcasecmp($this->ssltype(), 'ssl') === 0) {
            $port = 993; // IMAP SSL
        } else {
            $port = 143; // IMAP
        }

        return $port;
    }

    public function getServer()
    {
        return $this->mServer;
    }
}