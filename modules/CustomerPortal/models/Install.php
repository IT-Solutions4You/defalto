<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CustomerPortal_Install_Model extends Core_Install_Model {

    /**
     * @var array
     * [Name, Link, BlockLabel]
     */
    public array $registerSettingsLinks = [
        ['LBL_CUSTOMER_PORTAL', 'index.php?module=CustomerPortal&view=Index&parent=Settings', 'LBL_OTHER_SETTINGS']
    ];

    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks();
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
    }

    /**
     * @return array
     */
    public function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return [
            'vtiger_customerportal_fields',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_customerportal_fields', null)
            ->createTable('tabid')
            ->createColumn('fieldinfo', 'text DEFAULT NULL')
            ->createColumn('records_visible', 'int(1) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`)')
        ;
        
        $this->getTable('vtiger_customerportal_prefs', null)
            ->createTable('tabid')
            ->createColumn('prefkey', 'varchar(100) NOT NULL')
            ->createColumn('prefvalue', 'int(20) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`,`prefkey`)')
            ;

        $this->getTable('vtiger_customerportal_relatedmoduleinfo', null)
            ->createTable('tabid')
            ->createColumn('relatedmodules', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`tabid`)')
            ;

        $this->getTable('vtiger_customerportal_settings', null)
            ->createTable('id')
            ->createColumn('url','varchar(250) DEFAULT NULL')
            ->createColumn('default_assignee','int(11) DEFAULT NULL')
            ->createColumn('support_notification','int(11) DEFAULT NULL')
            ->createColumn('announcement','text DEFAULT NULL')
            ->createColumn('shortcuts','text DEFAULT NULL')
            ->createColumn('widgets','text DEFAULT NULL')
            ->createColumn('charts','text DEFAULT NULL')
            ;

        $this->getTable('vtiger_customerportal_tabs', null)
            ->createTable('tabid')
            ->createColumn('visible','int(1) DEFAULT \'1\'')
            ->createColumn('sequence','int(1) DEFAULT NULL')
            ->createColumn('createrecord','tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('editrecord','tinyint(1) NOT NULL DEFAULT \'0\'')
            ;
    }
}