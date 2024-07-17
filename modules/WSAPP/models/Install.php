<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class WSAPP_Install_Model extends Core_Install_Model
{

    public function addCustomLinks(): void
    {
    }

    public function deleteCustomLinks(): void
    {
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [
            'vtiger_wsapp',
            'vtiger_wsapp_recordmapping',
            'vtiger_wsapp_handlerdetails',
            'vtiger_wsapp_queuerecords',
            'vtiger_wsapp_sync_state',
        ];
    }

    /**
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_wsapp', 'appid')
            ->createTable()
            ->createColumn('name','varchar(200) NOT NULL')
            ->createColumn('appkey','varchar(255) default NULL')
            ->createColumn('type','varchar(100) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`appid`)')
            ;

        $this->getTable('vtiger_wsapp_recordmapping', 'id')
            ->createTable()
            ->createColumn('serverid','varchar(10) default NULL')
            ->createColumn('clientid','varchar(255) default NULL')
            ->createColumn('clientmodifiedtime','datetime default NULL')
            ->createColumn('appid','int(11) default NULL')
            ->createColumn('servermodifiedtime','datetime default NULL')
            ->createColumn('serverappid','int(11) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ;

        $this->getTable('vtiger_wsapp_handlerdetails', null)
            ->createTable('type', 'varchar(200)')
            ->createColumn('handlerclass', 'varchar(100) default NULL')
            ->createColumn('handlerpath', 'varchar(300) default NULL')
            ;

        $this->getTable('vtiger_wsapp_queuerecords', null)
            ->createTable('syncserverid', 'int(19)')
            ->createColumn('details','varchar(300) default NULL')
            ->createColumn('flag','varchar(100) default NULL')
            ->createColumn('appid','int(19) default NULL')
        ;

        $this->getTable('vtiger_wsapp_sync_state', 'id')
            ->createTable()
            ->createColumn('name','varchar(200) default NULL')
            ->createColumn('stateencodedvalues','varchar(300) NOT NULL')
            ->createColumn('userid','int(19) default NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
        ;
    }
}