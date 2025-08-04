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

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

/**
 * Class to work with server mailbox.
 */
class Settings_MailConverter_MailBox_Handler
{
    // Mailbox credential information
    public $_scannerinfo = false;
    // IMAP connection instance
    public $_imap = false;
    // IMAP url to use for connecting
    public $_imapurl = false;
    // IMAP folder currently opened
    public $_imapfolder = false;
    // Should we need to expunge while closing imap connection?
    public $_needExpunge = false;

    // Mailbox crendential information (as a map)
    public $_mailboxsettings = false;

    /** DEBUG functionality. */
    public $debug = false;

    function log($message, $force = false)
    {
        global $log;
        if ($log && ($force || $this->debug)) {
            $log->debug($message);
        } elseif (($force || $this->debug)) {
            echo "$message\n";
        }
    }

    /**
     * Constructor
     */
    function __construct($scannerinfo)
    {
        $this->_scannerinfo = $scannerinfo;
        $this->_mailboxsettings = $scannerinfo->getAsMap();

        if ($this->_mailboxsettings['ssltype'] == '') {
            $this->_mailboxsettings['ssltype'] = 'notls';
        }
        if ($this->_mailboxsettings['sslmethod'] == '') {
            $this->_mailboxsettings['sslmethod'] = 'novalidate-cert';
        }

        $server = $this->_mailboxsettings['server'];
        $serverPort = explode(':', $server);

        if (empty($serverPort[1])) {
            if ($this->_mailboxsettings['protocol'] == 'pop3') {
                $port = '110';
            } elseif ($this->_mailboxsettings['ssltype'] == 'tls' || $this->_mailboxsettings['ssltype'] == 'ssl') {
                $port = '993';
            } else {
                $port = '143';
            }
        } else {
            $port = $serverPort[1];
            $this->_mailboxsettings['server'] = $serverPort[0];
        }

        $this->_mailboxsettings['port'] = $port;

        if ($this->_scannerinfo->markas == "UNCHANGED") {
            $this->_mailboxsettings['readonly'] = "/readonly";
        }
    }

    /**
     * Connect to mail box folder.
     * @throws Exception
     */
    public function connect(): bool
    {
        $settings = $this->_mailboxsettings;

        $server = $settings['server'];
        $password = $settings['password'];
        $authentication = '';

        if (str_contains($server, 'gmail.com')) {
            $authentication = 'oauth';
            $password = $settings['client_access_token'];
        }

        if (empty($server) || empty($password)) {
            return false;
        }

        $options = [];
        $config = [
            'host'           => $server,
            'port'           => $settings['port'],
            'encryption'     => $settings['ssltype'],
            'validate_cert'  => true,
            'protocol'       => $settings['protocol'],
            'username'       => $settings['username'],
            'password'       => $password,
            'authentication' => $authentication,
        ];

        $clientManager = new ClientManager($options);

        $this->_imap = $clientManager->account($config['host']);
        $this->_imap = $clientManager->make($config);

        try {
            $this->_imap->connect();
        } catch (Exception $e) {
            throw new Exception('MailScanner Connection Error: ' . $e->getMessage());
        }

        return true;
    }

    public function getBox()
    {
        return $this->_imap;
    }

    /**
     * Open the mailbox folder.
     *
     * @param string $folder Folder name to open
     *
     * @return object if connected, false otherwise
     */
    public function getFolder(string $folder): object
    {
        return $this->_imap->getFolder($folder);
    }

    /**
     * Get the mails based on searchquery.
     *
     * @param object $mBoxFolder
     *
     * @return bool|object return query of emails records or false
     */
    public function getSearchMails(object $mBoxFolder): bool|object
    {
        $folder = $mBoxFolder->name;
        $lastScanOn = $this->_scannerinfo->getLastscan($mBoxFolder->name);

        if ($lastScanOn) {
            $searchQuery = 'SINCE ' . $lastScanOn;
        } else {
            $searchQuery = 'BEFORE ' . $this->_scannerinfo->dateBasedOnMailServerTimezone('d-M-Y');
        }

        if ($mBoxFolder) {
            $this->log("Searching mailbox[$folder] using query: $searchQuery");
            $searchFor = $this->_scannerinfo->searchfor;
            $query = $mBoxFolder->query();

            if ('UNSEEN' === $searchFor) {
                $query->where(['UNSEEN']);
            } elseif ('ALL' === $searchFor) {
                $query->all();
            }

            [$searchKey, $searchValue] = explode(' ', $searchQuery, 2);
            $query->where([$searchKey => $searchValue]);

            return $query->get();
        }

        return false;
    }

    /**
     * Get folder names (as list) for the given mailbox connection
     */
    public function getFolders()
    {
        $folders = [];

        if ($this->_imap) {
            $mBoxFolders = $this->_imap->getFolders();

            if ($mBoxFolders) {
                foreach ($mBoxFolders as $mBoxFolder) {
                    $folders[] = $mBoxFolder->name;
                }
            }
        }

        return $folders;
    }

    /**
     * Fetch the email based on the messageid.
     *
     * @param object $mBoxMessage
     * @param object $mBoxFolder
     * @param object $mBox
     *
     * @return Settings_MailConverter_MailRecord_Handler
     * @throws Exception
     */
    public function getMessage(object $mBoxMessage, object $mBoxFolder, object $mBox)
    {
        $message = new Settings_MailConverter_MailRecord_Handler();
        $message->setBox($mBox);
        $message->setBoxMessage($mBoxMessage);
        $message->setBoxFolder($mBoxFolder);
        $message->setUid($mBoxMessage->getUid());
        $message->retrieveRecord();
        $message->retrieveBody();
        $message->retrieveAttachments();

        return $message;
    }

    /**
     * Mark the message in the mailbox.
     */
    public function markMessage($mailRecord)
    {
        $mBoxMessage = $mailRecord->getBoxMessage();

        if (!$mBoxMessage) {
            return;
        }

        $markAs = $this->_scannerinfo->markas;

        switch (strtoupper($markAs)) {
            case 'SEEN':
                $mBoxMessage->setFlag(['Seen']);
                break;
            case 'UNSEEN':
                $mBoxMessage->unsetFlag(['Seen']);
                break;
        }
    }

    /**
     * Close the open IMAP connection.
     */
    public function close(): void
    {
        if ($this->_imap) {
            $this->_imap->disconnect();
            $this->_imap = false;
        }
    }
}