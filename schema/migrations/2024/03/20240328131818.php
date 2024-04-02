<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'vtlib/Vtiger/Cron.php';

if (!class_exists('Migration_20240328131818')) {
    class Migration_20240328131818 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $moduleName = 'Reports';
            $this->db->pquery('DELETE FROM vtiger_profile2tab WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name = ?)', [$moduleName]);
            $this->db->pquery('DELETE FROM vtiger_tab WHERE name = ?', [$moduleName]);

            Vtiger_Cron::deregister('ScheduleReports');
            $this->db->pquery('DELETE FROM vtiger_module_dashboard_widgets WHERE reportid IS NOT NULL AND reportid <> 0');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_homereportchart');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_schedulereports');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_scheduled_reports');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_report_sharers');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_report_shareusers');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_report_sharerole');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_report_sharegroups');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reporttype');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportsummary');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportsortcol');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportsharing');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportmodules');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportgroupbycolumn');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportfilters');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportdatefilter');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_selectcolumn');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_relcriteria');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_relcriteria_grouping');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_selectquery_seq');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_report');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_reportfolder');
            $this->db->pquery('DROP TABLE IF EXISTS vtiger_selectquery');

            // Regenerate tabdata.php and parent_tabdata.php
            require_once('vtlib/Vtiger/Deprecated.php');
            Vtiger_Deprecated::createModuleMetaFile();
            Vtiger_Deprecated::createModuleGroupMetaFile();
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}