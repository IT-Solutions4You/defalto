<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Folder_Model {

	protected $mName;
	protected $mPath;
	protected $mCount;
	protected $mUnreadCount;
	protected $mBoxFolder = null;
	protected $mMails;
	protected $mPageCurrent;
	protected $mPageStart;
	protected $mPageEnd;
	protected $mPageLimit;
	protected $mMailIds;
	protected $startCount;
	protected $endCount;

    public function __construct($name, $path = null, $mBoxFolder = null)
    {
        $this->setName($name);
        $this->setPath($path);
        $this->setBoxFolder($mBoxFolder);
    }

    public function getLabel()
    {
        return $this->mName;
    }

    public function getName()
    {
        return $this->mName;
    }

    public function getPath()
    {
        return $this->mPath;
    }

    public function setPath($value)
    {
        $this->mPath = $value;
    }

    public function isSentFolder() {
		$mailBoxModel = MailManager_Mailbox_Model::getActiveInstance();
		$folderName = $mailBoxModel->getFolder();
		if($this->mName == $folderName) {
			return true;
		}
		return false;
	}
	
	public function setName($name) {
		$this->mName = $name;
	}

	public function getMails() {
		return $this->mMails;
	}

	public function getMailIds(){
		return $this->mMailIds;
	}

	public function setMailIds($ids){
		$this->mMailIds = $ids;
	}

	public function setMails($mails) {
		$this->mMails = $mails;
	}

    public function setPaging($limit, $total, $page)
    {
        $this->mPageLimit = intval($limit);
        $this->mCount = intval($total);
        $this->mPageCurrent = intval($page);
        $this->mPageStart = ceil($total / $limit);
        $this->mPageEnd = 1;
    }

    public function pageStart() {
		return $this->mPageStart;
	}

	public function pageEnd() {
		return $this->mPageEnd;
	}

    public function pageInfo()
    {
        $this->startCount = max(1, ($this->mPageCurrent - 1) * $this->mPageLimit);
        $this->endCount = min($this->mPageCurrent * $this->mPageLimit, $this->mCount);

        return sprintf("%s - %s of %s", $this->startCount, $this->endCount, $this->mCount);
    }

    public function pageCurrent($offset=0) {
		return $this->mPageCurrent + $offset;
	}

    public function hasNextPage()
    {
        return $this->endCount !== $this->mCount;
    }

    public function hasPrevPage()
    {
        return $this->startCount !== 1;
    }

    public function count() {
		return $this->mCount;
	}

	public function setCount($count) {
		$this->mCount = $count;
	}

	public function unreadCount() {
		return $this->mUnreadCount;
	}

	public function setUnreadCount($unreadCount) {
		$this->mUnreadCount = $unreadCount;
	}

	public function getStartCount() {
		return $this->startCount;
	}

	public function getEndCount() {
		return $this->endCount;
	}

    /**
     * @param $mBox
     * @return \Webklex\PHPIMAP\Folder|null
     */
    public function getBoxFolder($mBox): Webklex\PHPIMAP\Folder|null
    {
        if (empty($this->mBoxFolder)) {
            $this->setBoxFolder($mBox->getFolder($this->getName()));
        }

        return $this->mBoxFolder;
    }

    public function setBoxFolder(Webklex\PHPIMAP\Folder|null $value): void
    {
        $this->mBoxFolder = $value;
    }

    /**
     * Returns the List of search string on the MailBox
     * @return string
     */
    public static function getSearchOptions()
    {
        return ['SUBJECT' => 'SUBJECT', 'TO' => 'TO', 'BODY' => 'BODY', 'BCC' => 'BCC', 'CC' => 'CC', 'FROM' => 'FROM', 'DATE' => 'ON'];
    }
}