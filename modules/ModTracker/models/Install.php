<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ModTracker_Install_Model extends Vtiger_Install_Model
{
    /**
     * @var array
     * [events, file, class, condition, dependOn, modules]
     */
    public array $registerEventHandler = [
        [['vtiger.entity.aftersave.final', 'vtiger.entity.beforedelete', 'vtiger.entity.afterrestore'], 'modules/ModTracker/ModTrackerHandler.php', 'ModTrackerHandler'],
    ];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = [
        ['ModTracker', 'HEADERSCRIPT', 'ModTrackerCommon_JS', 'modules/ModTracker/ModTrackerCommon.js']
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateEventHandler();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateEventHandler(false);
        $this->updateCustomLinks(false);
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
            'vtiger_modtracker_basic',
            'vtiger_modtracker_tabs',
            'vtiger_modtracker_detail',
            'vtiger_modtracker_relations',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_modtracker_basic', null)
            ->createTable('id')
            ->createColumn('crmid', 'int(20) default NULL')
            ->createColumn('module', 'varchar(50) default NULL')
            ->createColumn('whodid', 'int(20) default NULL')
            ->createColumn('changedon', 'datetime default NULL')
            ->createColumn('status', 'int(1) default \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS  (`id`)')
            ->createKey('INDEX crmidx (crmid)')
            ->createKey('INDEX idx (id)')
        ;

        $this->getTable('vtiger_modtracker_tabs', null)
            ->createTable('tabid')
            ->createColumn('visible', 'int(11) default \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS  (`tabid`)');

        $this->getTable('vtiger_modtracker_detail', null)
            ->createTable('id')
            ->createColumn('fieldname', 'varchar(100) default NULL')
            ->createColumn('prevalue', 'text')
            ->createColumn('postvalue', 'text')
            ->createKey('INDEX idx (id)')
            ;

        $this->getTable('vtiger_modtracker_relations', null)
            ->createTable('id', 'INT(19)')
            ->createColumn('targetmodule', 'VARCHAR(100) NOT NULL')
            ->createColumn('targetid', 'INT(19) NOT NULL')
            ->createColumn('changedon', 'DATETIME')
            ->createKey('PRIMARY KEY IF NOT EXISTS  (`id`)');
    }
}