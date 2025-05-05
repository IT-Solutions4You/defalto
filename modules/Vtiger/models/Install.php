<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_Install_Model extends Core_Install_Model {

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
        return [];
    }

    public function installTables(): void
    {
        $this->getTable('vtiger_blocks', '')
            ->createTable('blockid', 'int(19) NOT NULL')
            ->createColumn('tabid', 'int(19) NOT NULL')
            ->createColumn('blocklabel', 'varchar(100) NOT NULL')
            ->createColumn('sequence', 'int(10) DEFAULT NULL')
            ->createColumn('show_title', 'int(2) DEFAULT NULL')
            ->createColumn('visible', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('create_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('edit_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('detail_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('display_status', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('iscustom', 'int(1) NOT NULL DEFAULT "0"')
            ->createColumn('blockuitype', 'int(11) NOT NULL DEFAULT 1')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`blockid`)')
            ->createKey('KEY IF NOT EXISTS `block_tabid_idx` (`tabid`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_blocks` FOREIGN KEY IF NOT EXISTS (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE')
        ;
    }
}