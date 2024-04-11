<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MailManager_Install_Model extends Vtiger_Install_Model {

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
    }
}