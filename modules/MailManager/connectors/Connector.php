<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

vimport ('~modules/MailManager/models/Message.php');

class MailManager_Connector_Connector {

	/*
	 * Cache interval time
	*/
	static $DB_CACHE_CLEAR_INTERVAL = "-1 day"; // strtotime

	/*
	 * Mail Box URL
	*/
	public $mBoxUrl;

	/*
	 * Mail Box connection instance
	*/
	public $mBox;
	public $mBoxModel;

	/*
	 * Last imap error
	*/
	protected $mError;

	/*
	 * Mail Box folders
	*/
	protected $mFolders = false;

	/**
	 * Modified Time of the mail
	 */
	protected $mModified = false;

	/*
	 * Base URL of the Mail Box excluding folder name
	*/
	protected $mBoxBaseUrl;


    /**
     * Connects to the Imap server with the given parameters
     * @param $model MailManager_Mailbox_Model Instance
     * $param $folder String optional - mail box folder name
     * @returns MailManager_Connector_Connector Object
     * @throws AppException
     */
    public static function connectorWithModel(MailManager_Mailbox_Model $model): self
    {
        $model->retrieveClientAccessToken();

        return new self($model);
    }


    /**
     * Opens up imap connection to the specified url
     * @param MailManager_Mailbox_Model $model
     */
    public function __construct(MailManager_Mailbox_Model $model)
    {
        $this->mBoxModel = $model;
        $this->connect();
    }


    /**
	 * Closes the connection
	 */
	public function __destruct() {
		$this->close();
	}

    public function connect()
    {
        /** @var MailManager_Mailbox_Model $boxModel */
        $boxModel = $this->mBoxModel;

        if (empty($boxModel)) {
            return;
        }

        $server = $boxModel->server();
        $password = $boxModel->password();
        $authentication = '';

        if (str_contains($server, 'gmail.com')) {
            $authentication = 'oauth';
            $password = $boxModel->getClientAccessToken();
        }

        if (empty($server) || empty($password)) {
            return;
        }

        $options = [];
        $config = [
            'host' => $server,
            'port' => $boxModel->getPort(),
            'encryption' => $boxModel->ssltype(),
            'validate_cert' => true,
            'protocol' => $boxModel->protocol(),
            'username' => $boxModel->username(),
            'password' => $password,
            'authentication' => $authentication,
        ];

        $clientManager = new ClientManager($options);

        $this->mBox = $clientManager->account($config['host']);
        $this->mBox = $clientManager->make($config);
        $this->mBox->connect();
    }

    /**
	 * Closes the imap connection
	 */
    public function close()
    {
        if (empty($this->mBox)) {
            return;
        }

        $this->getBox()->disconnect();
        $this->mBox = null;
    }


    /**
	 * Checks for the connection
	 */
	public function isConnected() {
		return !empty($this->mBox);
	}


	/**
	 * Returns the last imap error
	 */
	public function isError() {
		$errors = imap_errors();
		if($errors !== false) {
			$this->mError = implode(', ',$errors);
		} else {
			$this->mError = imap_last_error();
		}

		return $this->hasError();
	}


	/**
	 * Checks if the error exists
	 */
	public function hasError() {
		return !empty($this->mError);
	}


	/**
	 * Returns the error
	 */
	public function lastError() {
		return $this->mError;
	}


    /**
     * Reads mail box folders
     * @return array|bool|mixed
     */
    public function getFolders()
    {
        if ($this->mFolders) {
            return $this->mFolders;
        }

        $result = $this->getBox()->getFolders();
        $folders = [];

        foreach ($result as $row) {
            if ($row->hasChildren()) {
                foreach ($row->getChildren()->all() as $childRow) {
                    $folderInstance = $this->getFolder($childRow->name, $childRow->path);
                    $folderInstance->setBoxFolder($childRow);

                    $folders[] = $folderInstance;
                }
            } else {
                $folderInstance = $this->getFolder($row->name, $row->path);
                $folderInstance->setBoxFolder($row);

                $folders[] = $folderInstance;
            }
        }

        $this->mFolders = $folders;

        return $folders;
    }

    public function getBox()
    {
        return $this->mBox;
    }


    /**
	 * Used to update the folders optionus
	 * @param imap_stats flag $options
	 */
    public function updateFolders($options = SA_UNSEEN)
    {
        $folders = $this->getFolders(); // Initializes the folder Instance

        foreach ($folders as $folder) {
            $this->updateFolder($folder, $options);
        }
    }


    /**
	 * Updates the mail box's folder
	 * @param MailManager_Folder_Model $folder - folder instance
	 * @param int $options imap_status flags like SA_UNSEEN, SA_MESSAGES etc
	 */
    public function updateFolder(MailManager_Folder_Model $folder, int $options = 0): void
    {
        $mBoxFolder = $folder->getBoxFolder($this->getBox());

        if ($mBoxFolder) {
            $allMessages = $mBoxFolder->query()->all()->setFetchBody(false);

            $folder->setCount($allMessages->count());
            $folder->setUnreadCount($allMessages->unseen()->count());
        }
    }


    /**
	 * Returns MailManager_Model_Folder Instance
	 * @param String $name - folder name
	 */
    public function getFolder(string $name, string $path = null, $mBoxFolder = null): MailManager_Folder_Model
    {
        return new MailManager_Folder_Model($name, $path, $mBoxFolder);
    }


    /**
	 * Sets a list of mails with paging
	 * @param MailManager_Folder_Model $folder - MailManager_Model_Folder Instance
	 * @param Integer $page  - Page number
	 * @param Integer $limit - Number of mails
	 */
    public function retrieveFolderMails($folder, int $page, int $limit)
    {
        $mBoxFolder = $folder->getBoxFolder($this->getBox());

        if ($mBoxFolder) {
            $query = $mBoxFolder->query()->all()->setFetchBody(false);
            $count = $query->count();

            [$mailIds, $mails] = $this->getMails($query, $folder, $page, $limit);

            $folder->setMails($mails);
            $folder->setMailIds($mailIds);
            $folder->setPaging($limit, $count, $page);
        }
    }

    public function getMails($query, $folder, $page, $limit)
    {
        $count = $query->count();
        $messageNo = $count - (($page - 1) * $limit);
        $lastMessageNo = max($count - ($page * $limit) + 1, 1);

        $mailIds = [];
        $mails = [];

        while ($messageNo >= $lastMessageNo) {
            if (empty($messageNo)) {
                break;
            }

            $mBoxMessage = $query->getMessageByMsgn($messageNo);
            $message = MailManager_Message_Model::parseOverview($mBoxMessage, $folder, $this->getBox());

            $mailIds[] = $message->getUid();
            $mails[] = $message;
            $messageNo--;
        }

        return [$mailIds, $mails];
    }


    /**
     * Return the cache interval
	 */
	public function clearDBCacheInterval() {
		// TODO Provide configuration option.
		if (self::$DB_CACHE_CLEAR_INTERVAL) {
			return strtotime(self::$DB_CACHE_CLEAR_INTERVAL);
		}
		return false;
	}


	/**
	 * Clears the cache data
	 */
	public function clearDBCache() {
		// Trigger purne any older mail saved in DB first
		$interval = $this->clearDBCacheInterval();

		$timenow = strtotime("now");

		// Optimization to avoid trigger for ever mail open (with interval specified)
		$lastClearTimeFromSession = false;
		if ($interval && isset($_SESSION) && isset($_SESSION['mailmanager_clearDBCacheIntervalLast'])) {
			$lastClearTimeFromSession = intval($_SESSION['mailmanager_clearDBCacheIntervalLast']);
			if (($timenow - $lastClearTimeFromSession) < ($timenow - $interval)) {
				$interval = false; 
			}
		}
		if ($interval) {
			MailManager_Message_Model::pruneOlderInDB($interval);
			$_SESSION['mailmanager_clearDBCacheIntervalLast'] = $timenow;
		}
	}


	/**
	 * Function which deletes the mails
     * @params object $folder
	 * @param String $mUId - List of message number seperated by commas.
	 */
    public function deleteMail(object $folder, string $mUId): void
    {
        $mUIds = explode(',', trim($mUId, ','));

        foreach ($mUIds as $mUId) {
            $message = $this->getMessageByMUid($folder, $mUId);

            if ($message) {
                $message->delete(true);
            }
        }
    }


    /**
     * Function which moves mail to another folder
     * @param string $mUIds
     * @param object $folderFrom
     * @param object $folderTo
     */
    public function moveMail(string $mUIds, object $folderFrom, object $folderTo): void
    {
        $mUIds = explode(',', trim($mUIds, ','));

        foreach ($mUIds as $mUid) {
            $message = $this->getMessageByMUid($folderFrom, $mUid);

            if ($message) {
                $message->move($folderTo->getName());
            }
        }
    }


    /**
     * Creates an instance of Message
     * @param MailManager_Folder_Model $folder
     * @param int $mUId
     * @param bool $fetchBody
     * @return MailManager_Message_Model
     * @throws AppException
     */
    public function getMail(MailManager_Folder_Model $folder, int $mUId, bool $fetchBody = true): MailManager_Message_Model
    {
        $this->clearDBCache();
        $mBox = $this->getBox();
        $message = MailManager_Message_Model::getInstanceByBoxMessage($this->getMessageByMUid($folder, $mUId), $folder, $mBox);

        if ($fetchBody) {
            $message->retrieveBody();
            $message->retrieveAttachments();
        }

        return $message;
    }

    /**
     * Marks the mail as Unread
     * @param object $folder
     * @param int $mUid
     * @throws AppException
     */
    public function markMailUnread(object $folder, int $mUid): void
    {
        if (empty($mUid)) {
            throw new AppException('Empty mUid for action markMailUnread');
        }

        $message = $this->getMessageByMUid($folder, $mUid);

        if ($message) {
            $message->unsetFlag('Seen');
            $this->mModified = true;
        }
    }


    /**
     * Marks the mail as Read
     * @param int $mUid - Message Number
     * @throws AppException
     */
    public function markMailRead(object $folder, int $mUid): void
    {
        if (empty($mUid)) {
            throw new AppException('Empty mUid for action markMailRead');
        }

        $message = $this->getMessageByMUid($folder, $mUid);

        if ($message) {
            $message->setFlag('Seen');
            $this->mModified = true;
        }
    }

    public function getMessageByMUid($folder, int $mUid)
    {
        $box = $this->getBox();

        return $box ? $folder->getBoxFolder($box)->query()->getMessageByUid($mUid) : null;
    }


    /**
     * Searches the Mail Box with the query
     * @param array $query - imap search format
     * @param MailManager_Folder_Model $folder - folder instance
     * @param int $page
     * @param int $limit
     */
    public function retrieveSearchMails(array $query, MailManager_Folder_Model $folder, int $page, int $limit): void
    {
        $mBoxFolder = $folder->getBoxFolder($this->getBox());

        if ($mBoxFolder) {
            $query = $mBoxFolder->query()->where($query)->all();
            $count = $query->count();

            [$mailIds, $mails] = $this->getMails($query, $folder, $page, $limit);

            $folder->setMails($mails);
            $folder->setMailIds($mailIds);
            $folder->setPaging($limit, $count, $page);  //-1 as it starts from 0
        }
    }

    /**
     * @param string $query
     * @param string $type
     * @return array
     */
    public function formatQueryFromRequest(string $query, string $type)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (empty($type)) {
            $type = 'ALL';
        }

        if ($type == 'ON') {
            $dateFormat = $currentUserModel->get('date_format');

            if ($dateFormat == 'mm-dd-yyyy') {
                $dateArray = explode('-', $query);
                $temp = $dateArray[0];
                $dateArray[0] = $dateArray[1];
                $dateArray[1] = $temp;
                $query = implode('-', $dateArray);
            } elseif ($dateFormat == 'dd/mm/yyyy') {
                $dateArray = explode('/', $query);
                $temp = $dateArray[0];
                $dateArray[0] = $dateArray[1];
                $dateArray[1] = $temp;
                $query = implode('/', $dateArray);
            }

            $query = date('d-M-Y', strtotime($query));

            $where = [$type => vtlib_purify($query)];
        } else {
            $where = [$type => vtlib_purify($query)];
        }

        return $where;
    }

    /**
	 * Returns list of Folder for the Mail Box
	 * @return Array folder list
	 */
    public function getFolderList()
    {
        $folders = $this->getFolders();
        $folderList = [];

        foreach ($folders as $folder) {
            $folderList[] = $folder->getName();
        }

        return $folderList;
    }

    public function convertCharacterEncoding($value, $toCharset, $fromCharset) {
		if (function_exists('mb_convert_encoding')) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($toCharset, $fromCharset, $value);
		}
		return $value;
	}
}