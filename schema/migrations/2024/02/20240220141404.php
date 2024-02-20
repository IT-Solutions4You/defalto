<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!class_exists('Migration_20240220141404')) {
    class Migration_20240220141404 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            // Delete old dashboard widgets
            $this->db->pquery('DELETE FROM vtiger_homestuff WHERE stufftitle IN (?, ?)', ['Upcoming Activities', 'Pending Activities']);
            $this->db->pquery(
                'DELETE FROM vtiger_module_dashboard_widgets WHERE linkid IN (SELECT linkid FROM vtiger_links WHERE linktype = ? AND linklabel IN (?, ?, ?))',
                ['DASHBOARDWIDGET', 'Upcoming Activities', 'Pending Activities', 'Overdue Activities']
            );
            $this->db->pquery(
                'DELETE FROM vtiger_links WHERE linktype = ? AND linklabel IN (?, ?, ?)',
                ['DASHBOARDWIDGET', 'Upcoming Activities', 'Pending Activities', 'Overdue Activities']
            );

            // Delete old workflows
            $this->db->pquery('DELETE FROM com_vtiger_workflow_tasktypes WHERE tasktypename IN (?, ?)', ['VTCreateTodoTask', 'VTCreateEventTask']);
            $this->db->pquery('DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ? OR task LIKE ?', ['%VTCreateTodoTask%', '%VTCreateEventTask%']);
            $this->db->pquery(
                'DELETE FROM com_vtiger_workflowtasks WHERE workflow_id IN (SELECT workflow_id FROM com_vtiger_workflows WHERE module_name IN (?, ?))',
                ['Calendar', 'Events']
            );
            $this->db->pquery('DELETE FROM com_vtiger_workflows WHERE module_name IN (?, ?)', ['Calendar', 'Events']);

            // Remove the module
            $this->db->pquery('DELETE FROM vtiger_profile2tab WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name IN (?, ?))', ['Calendar', 'Events']);
            $this->db->pquery('DELETE FROM vtiger_tab WHERE name IN (?, ?)', ['Calendar', 'Events']);

            // Delete the related lists
            $this->db->pquery('DELETE FROM vtiger_relatedlists WHERE label IN (?, ?)', ['Activities', 'Activity History']);

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