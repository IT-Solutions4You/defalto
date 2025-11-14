<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomerPortal_Install_Model extends Core_Install_Model
{
    /**
     * @return void
     */
    public function addCustomLinks(): void
    {
        $this->updateToStandardModule();
        $this->addModuleToCustomerPortal();
        $this->updateCustomerPortalModules();
    }

    /**
     * @throws Exception
     */
    public function updateCustomerPortalModules(): void
    {
        $portalModules = [
            'HelpDesk',
            'Faq',
            'Invoice',
            'Quotes',
            'Products',
            'Services',
            'Documents',
            'Contacts',
            'Accounts',
            'Project',
            'ProjectTask',
            'ProjectMilestone',
            'Assets',
        ];
        $adb = $this->getDB();
        $result = $adb->pquery('SELECT max(sequence) AS max_tabseq FROM vtiger_customerportal_tabs', []);
        $i = (int)$adb->query_result($result, 0, 'max_tabseq') + 1;

        foreach ($portalModules as $module) {
            $tabId = getTabid($module);
            $tabsResult = $adb->pquery('SELECT tabid FROM vtiger_customerportal_tabs WHERE tabid=?', [$tabId]);

            if ($tabId && !$adb->num_rows($tabsResult)) {
                ++$i;
                $adb->pquery('INSERT INTO vtiger_customerportal_tabs(tabid,visible,sequence) VALUES (?, ?, ?)', [$tabId, 1, $i]);
                $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [$tabId, 'showrelatedinfo', 1]);
            }
        }

        if(!$this->isPrefExists(0, 'userid')) {
            $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [0, 'userid', 1]);
        }

        if(!$this->isPrefExists(0, 'defaultassignee')) {
            $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [0, 'defaultassignee', 1]);
        }
    }

    /**
     * @param int $tabId
     * @param string $key
     * @return bool
     */
    public function isPrefExists(int $tabId, string $key): bool
    {
        $adb = $this->getDB();
        $result = $adb->pquery('SELECT tabid FROM vtiger_customerportal_prefs WHERE tabid=? AND prefkey=?', [$tabId, $key]);

        return (bool)$adb->num_rows($result);
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
     * @throws Exception
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