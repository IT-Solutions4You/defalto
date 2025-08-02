<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Webforms_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [Name, Link, BlockLabel]
     */
    public array $registerSettingsLinks = [
        ['Webforms', 'index.php?module=Webforms&view=List&parent=Settings', 'LBL_AUTOMATION']
    ];

    /**
     * @return void
     * @throws Exception
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->updateSettingsLinks();
    }

    /**
     * @return void
     * @throws Exception
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
            'vtiger_webforms',
            'vtiger_webforms_field',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_webforms', 'id')
            ->createTable()
            ->createColumn('name','varchar(100) NOT NULL')
            ->createColumn('publicid','varchar(100) NOT NULL')
            ->createColumn('enabled','int(1) NOT NULL DEFAULT \'1\'')
            ->createColumn('targetmodule','varchar(50) NOT NULL')
            ->createColumn('description','TEXT DEFAULT NULL')
            ->createColumn('ownerid','int(19) NOT NULL')
            ->createColumn('returnurl','varchar(250) DEFAULT NULL')
            ->createColumn('captcha','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('roundrobin','int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('roundrobin_userid','varchar(256)DEFAULT NULL')
            ->createColumn('roundrobin_logic','int(11) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `webformname` (`name`)')
            ->createKey('UNIQUE KEY IF NOT EXISTS `publicid` (`id`)')
            ->createKey('KEY IF NOT EXISTS `webforms_webforms_id_idx` (`id`)')
            ;

        $this->getTable('vtiger_webforms_field', 'id')
            ->createTable()
            ->createColumn('webformid','int(19) NOT NULL')
            ->createColumn('fieldname','varchar(50) NOT NULL')
            ->createColumn('neutralizedfield','varchar(50) NOT NULL')
            ->createColumn('defaultvalue','TEXT DEFAULT NULL')
            ->createColumn('required','int(10) NOT NULL DEFAULT \'0\'')
            ->createColumn('sequence','int(10) DEFAULT NULL')
            ->createColumn('hidden','int(10) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)')
            ->createKey('KEY IF NOT EXISTS `webforms_webforms_field_idx` (`id`)')
            ->createKey('KEY IF NOT EXISTS `fk_1_vtiger_webforms_field` (`webformid`)')
            ->createKey('KEY IF NOT EXISTS `fk_2_vtiger_webforms_field` (`fieldname`)')
            ->createKey('CONSTRAINT `fk_1_vtiger_webforms_field` FOREIGN KEY IF NOT EXISTS (`webformid`) REFERENCES `vtiger_webforms` (`id`) ON DELETE CASCADE')
            ->createKey('CONSTRAINT `fk_3_vtiger_webforms_field` FOREIGN KEY IF NOT EXISTS (`fieldname`) REFERENCES `vtiger_field` (`fieldname`) ON DELETE CASCADE')
        ;
    }
}