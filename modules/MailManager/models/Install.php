<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MailManager_Install_Model extends Core_Install_Model {

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getTables(): array
    {
        return [
            'vtiger_mailmanager_mailrel',
            'vtiger_mailmanager_mailrecord',
            'vtiger_mailmanager_mailattachments',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_mailmanager_mailrel', null)
            ->createTable('mailuid', 'varchar(999) default NULL')
            ->createColumn('crmid', 'int(11) default NULL')
            ->createColumn('emailid', 'int(11) default NULL')
        ;

        $this->getTable('vtiger_mailmanager_mailrecord', null)
            ->createTable('userid', 'int(11) default NULL')
            ->createColumn('mfrom','varchar(255) default NULL')
            ->createColumn('mto','varchar(255) default NULL')
            ->createColumn('mcc','varchar(500) default NULL')
            ->createColumn('mbcc','varchar(500) default NULL')
            ->createColumn('mdate','varchar(20) default NULL')
            ->createColumn('msubject','varchar(500) default NULL')
            ->createColumn('mbody','text')
            ->createColumn('mcharset','varchar(10) default NULL')
            ->createColumn('misbodyhtml','int(1) default NULL')
            ->createColumn('mplainmessage','int(1) default NULL')
            ->createColumn('mhtmlmessage','int(1) default NULL')
            ->createColumn('muniqueid','varchar(500) default NULL')
            ->createColumn('mbodyparsed','int(1) default NULL')
            ->createColumn('muid','int(11) default NULL')
            ->createColumn('lastsavedtime','int(11) default NULL')
            ->createColumn('folder','varchar(250)')
            ->createColumn('mfolder','VARCHAR(250)')
            ->createKey('KEY IF NOT EXISTS `userid_lastsavedtime_idx` (`userid`,`lastsavedtime`)')
            ->createKey('KEY IF NOT EXISTS `userid_muid_idx` (`userid`,`muid`)')
        ;

        $this->getTable('vtiger_mailmanager_mailattachments', null)
            ->createTable('userid', 'int(11) default NULL')
            ->createColumn('muid','int(11) default NULL')
            ->createColumn('aname','varchar(100) default NULL')
            ->createColumn('lastsavedtime','int(11) default NULL')
            ->createColumn('attachid','int(19) NOT NULL')
            ->createColumn('path','varchar(200) NOT NULL')
            ->createColumn('cid','varchar(100) default NULL')
            ->createKey('KEY IF NOT EXISTS `userid_muid_idx` (`userid`,`muid`)')
        ;

        $this->getTable('vtiger_mailscanner_entitymethod', 'mailscanner_entitymethod_id')
            ->createTable()
            ->createColumn('module_name', 'varchar(100)')
            ->createColumn('method_name', 'varchar(100)')
            ->createColumn('function_path', 'varchar(400)')
            ->createColumn('function_name', 'varchar(100)')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`mailscanner_entitymethod_id`)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `mailscanner_entitymethod_idx` (`mailscanner_entitymethod_id`)');

        $this->getTable('vtiger_mailscanner_actions', 'actionid')
            ->createTable()
            ->createColumn('scannerid', 'int(11) DEFAULT NULL')
            ->createColumn('actiontype', 'varchar(100) DEFAULT NULL')
            ->createColumn('module', 'varchar(100) DEFAULT NULL')
            ->createColumn('lookup', 'varchar(100) DEFAULT NULL')
            ->createColumn('sequence', 'int(11) DEFAULT NULL')
            ->createkey('PRIMARY KEY IF NOT EXISTS (`actionid`)');

        $this->getTable('vtiger_mailscanner', 'scannerid')
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
            ->createColumn('client_access_token', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`scannerid`)')
            ;

        $this->getTable('vtiger_mailscanner_folders', 'folderid')
            ->createTable()
            ->createColumn('scannerid','int(19) DEFAULT NULL')
            ->createColumn('foldername','varchar(255) DEFAULT NULL')
            ->createColumn('lastscan','varchar(30) DEFAULT NULL')
            ->createColumn('rescan','int(1) DEFAULT NULL')
            ->createColumn('enabled','int(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`folderid`)')
            ->createKey('KEY IF NOT EXISTS `folderid_idx` (`folderid`)')
            ;

        $this->getTable('vtiger_mailscanner_ids', 'scannerid')
            ->createTable('scannerid', 'int(19) DEFAULT NULL')
            ->createColumn('messageid','varchar(512) DEFAULT NULL')
            ->createColumn('crmid','int(19) DEFAULT NULL')
            ->createColumn('refids','text DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `scanner_message_ids_idx` (`scannerid`,`messageid`)')
            ->createKey('KEY IF NOT EXISTS `messageids_crmid_idx` (`crmid`)')
        ;

        $this->getTable('vtiger_mailscanner_ruleactions', 'ruleid')
            ->createTable('ruleid', 'int(19) DEFAULT NULL')
            ->createColumn('actionid', 'int(19) DEFAULT NULL')
        ;

        $this->getTable('vtiger_mailscanner_rules', 'ruleid')
            ->createTable()
            ->createColumn('scannerid','int(11) DEFAULT NULL')
            ->createColumn('fromaddress','varchar(255) DEFAULT NULL')
            ->createColumn('toaddress','varchar(255) DEFAULT NULL')
            ->createColumn('subjectop','varchar(20) DEFAULT NULL')
            ->createColumn('subject','varchar(255) DEFAULT NULL')
            ->createColumn('bodyop','varchar(20) DEFAULT NULL')
            ->createColumn('body','varchar(255) DEFAULT NULL')
            ->createColumn('matchusing','varchar(5) DEFAULT NULL')
            ->createColumn('sequence','int(11) DEFAULT NULL')
            ->createColumn('assigned_to','int(10) DEFAULT NULL')
            ->createColumn('cc','varchar(255) DEFAULT NULL')
            ->createColumn('bcc','varchar(255) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`ruleid`)')
        ;
    }
}