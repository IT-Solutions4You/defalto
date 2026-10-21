<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_Folder_Model
{
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

    /**
     * Returns the display label of the folder: its full path, so nested folders with
     * the same name (Archives/2026, Projects/2026) stay distinguishable.
     *
     * @return string
     */
    public function getLabel()
    {
        return (string)$this->mName;
    }

    /**
     * Returns true for the INBOX folder itself (not for its subfolders).
     *
     * @return bool
     */
    public function isInboxFolder(): bool
    {
        return 0 === strcasecmp((string)$this->mName, 'INBOX');
    }

    /**
     * Returns true for a top-level trash folder (name contains "trash").
     *
     * @return bool
     */
    public function isTrashFolder(): bool
    {
        return false !== stripos((string)$this->mName, 'trash') && !$this->isNestedFolder();
    }

    /**
     * Returns true when the folder is a subfolder of another folder.
     *
     * @return bool
     */
    public function isNestedFolder(): bool
    {
        $boxFolder = $this->mBoxFolder;

        return $boxFolder && $boxFolder->name !== $boxFolder->full_name;
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

    /**
     * Returns true when this folder is the sent folder configured in the mailbox settings.
     * The setting may hold the folder path or, for older settings, only the folder name.
     *
     * @return bool
     */
    public function isSentFolder()
    {
        $folderName = (string)self::getSentFolderSetting();

        if ('' === $folderName) {
            return false;
        }

        return $this->mName === $folderName || ($this->mBoxFolder && $this->mBoxFolder->name === $folderName);
    }

    /**
     * Returns the sent folder configured for the current user's mailbox, loaded once per request.
     *
     * @return string
     */
    public static function getSentFolderSetting(): string
    {
        static $sentFolder = null;

        if (null === $sentFolder) {
            $sentFolder = (string)MailManager_Mailbox_Model::getActiveInstance()->getFolder();
        }

        return $sentFolder;
    }

    public function setName($name)
    {
        $this->mName = $name;
    }

    public function getMails()
    {
        return $this->mMails;
    }

    public function getMailIds()
    {
        return $this->mMailIds;
    }

    public function setMailIds($ids)
    {
        $this->mMailIds = $ids;
    }

    public function setMails($mails)
    {
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

    public function pageStart()
    {
        return $this->mPageStart;
    }

    public function pageEnd()
    {
        return $this->mPageEnd;
    }

    /**
     * Computes the first and last mail number of the current page and returns the
     * "start - end of total" text; the numbering starts at 1.
     *
     * @return string
     */
    public function pageInfo()
    {
        $this->startCount = min(($this->mPageCurrent - 1) * $this->mPageLimit + 1, $this->mCount);
        $this->endCount = min($this->mPageCurrent * $this->mPageLimit, $this->mCount);

        return sprintf("%s - %s of %s", $this->startCount, $this->endCount, $this->mCount);
    }

    public function pageCurrent($offset = 0)
    {
        return $this->mPageCurrent + $offset;
    }

    /**
     * Returns true when another page follows the current one.
     *
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->endCount < $this->mCount;
    }

    /**
     * Returns true when the current page is not the first one.
     *
     * @return bool
     */
    public function hasPrevPage()
    {
        return $this->mPageCurrent > 1;
    }

    public function count()
    {
        return $this->mCount;
    }

    public function setCount($count)
    {
        $this->mCount = $count;
    }

    public function unreadCount()
    {
        return $this->mUnreadCount;
    }

    public function setUnreadCount($unreadCount)
    {
        $this->mUnreadCount = $unreadCount;
    }

    public function getStartCount()
    {
        return $this->startCount;
    }

    public function getEndCount()
    {
        return $this->endCount;
    }

    /**
     * @param $mBox
     *
     * @return \Webklex\PHPIMAP\Folder|null
     */
    public function getBoxFolder($mBox): Webklex\PHPIMAP\Folder|null
    {
        if (empty($this->mBoxFolder)) {
            $this->setBoxFolder($this->retrieveBoxFolder($mBox));
        }

        return $this->mBoxFolder;
    }

    /**
     * Looks the folder up on the server by its full path first and falls back to the
     * plain folder name (older settings and links store only the name).
     *
     * @param \Webklex\PHPIMAP\Client $mBox
     *
     * @return \Webklex\PHPIMAP\Folder|null
     */
    public function retrieveBoxFolder($mBox): Webklex\PHPIMAP\Folder|null
    {
        $name = (string)$this->getName();

        foreach ($mBox->getFolders(false) as $folder) {
            if ($folder->full_name === $name) {
                return $folder;
            }
        }

        return $mBox->getFolderByName($name, true);
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