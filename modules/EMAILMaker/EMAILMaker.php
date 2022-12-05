<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker
{
    public $log;
    public $db;
    public $moduleModel;
    public $id;
    public $name;
    private $basicModules;
    private $pageFormats;
    private $profilesActions;
    private $profilesPermissions;
    public $moduleName = 'EMAILMaker';
    public $parentName = 'Tools';
    public $list_fields_name  = [];
    public $list_fields = [];
    public $related_tables = [];

    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public $registerCustomLinks = array(
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerJS', 'layouts/v7/modules/EMAILMaker/resources/EMAILMakerActions.js'],
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerMassEditJS', 'layouts/v7/modules/EMAILMaker/resources/MassEdit.js'],
    );

    public function __construct()
    {
        global $log;

        $this->log = $log;
        $this->db = PearDatabase::getInstance();
        $this->basicModules = array('20', '21', '22', '23');
        $this->profilesActions = array(
            'EDIT' => 'EditView',
            'DETAIL' => 'DetailView', // View
            'DELETE' => 'Delete', // Delete
            'EXPORT_RTF' => 'Export', // Export to RTF
        );
        $this->profilesPermissions = array();
        $this->name = 'EMAILMaker';
        $this->id = getTabId($this->name);
    }

    public function vtlib_handler($modulename, $event_type)
    {
        $this->moduleModel = Vtiger_Module_Model::getInstance($this->name);

        switch ($event_type) {
            case 'module.postinstall':
                $this->executeSql();
                $this->addCustomLinks();
                $this->installWorkflows();
                $this->updateCron();
                break;
            case 'module.enabled':
            case 'module.postupdate':
                $this->updateProfilePermissions();
                $this->addCustomLinks();
                $this->installWorkflows();
                break;
            case 'module.preupdate':
            case 'module.disabled':
                $this->deleteCustomLinks();
                break;
            case 'module.preuninstall':
                $this->deleteCustomLinks();
                $this->removeWorkflows();
                $this->updateCron(false);
                break;
        }
    }

    public function updateProfilePermissions()
    {
        $result1 = $this->db->pquery('SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = ?)', array('EMAILMaker'));

        if ($this->db->num_rows($result1) > 0) {
            $result2 = $this->db->query('SELECT * FROM vtiger_emakertemplates_profilespermissions');

            if ($this->db->num_rows($result2) == 0) {
                $this->db->pquery('INSERT INTO vtiger_emakertemplates_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)', array('EMAILMaker'));
            }

            $this->db->pquery('DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)', array('EMAILMaker'));
        }
    }

    public function executeSql()
    {

        $this->actualizeSeqTables();

        $productblocData = "INSERT INTO `vtiger_emakertemplates_productbloc_tpl` (`id`, `name`, `body`) VALUES
      (1, 'product block for individual tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_NET_PRICE%<br />\r\n				without TAX<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax% (%)</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax%</strong> (<strong>$" . "CURRENCYCODE$</strong>)</span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATPERCENT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATSUM$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTALSUM$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Subtotals</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "TOTALWITHOUTVAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				&nbsp;</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "VAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SUBTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				Total with TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n'),
(2, 'product block for group tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span>%G_LBL_NET_PRICE% without TAX</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHOUTVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Total without TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_Tax% $" . "VATPERCENT$ % %G_LBL_LIST_OF% $" . "TOTALAFTERDISCOUNT$</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "VAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				Total with TAX</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n');";

        $this->db->pquery($productblocData, array());
    }

    public function actualizeSeqTables()
    {
        $seqTables = array(
            'vtiger_emakertemplates_drips_seq' => 'id',
            'vtiger_emakertemplates_drip_groups_seq' => 'id',
            'vtiger_emakertemplates_drip_tpls_seq' => 'id',
            'vtiger_emakertemplates_seq' => 'id',
            'vtiger_emakertemplates_delay' => 'delay_active',
            'vtiger_emakertemplates_relblocks_seq' => 'id',
        );

        foreach ($seqTables as $seqTable => $seqTableId) {
            $result = $this->db->query(sprintf('SELECT %s FROM %s', $seqTableId, $seqTable));

            if ($this->db->num_rows($result) < 1) {
                $this->db->pquery(
                    sprintf('INSERT INTO %s VALUES (?)', $seqTable),
                    array('0')
                );
            }
        }
    }

    public function addCustomLinks()
    {
        $this->updateFields();

        $this->retrieveCustomLinks();
        $this->updateCustomLinks();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);
    }

    public function updateLinks()
    {
        require_once 'vtlib/Vtiger/Module.php';

        $Related_Modules = getEmailRelatedModules();
        $result1 = $this->db->pquery("SELECT module FROM vtiger_emakertemplates WHERE deleted = ? GROUP BY module", array('0'));
        $num_rows1 = $this->db->num_rows($result1);

        if ($num_rows1 > 0) {
            while ($row = $this->db->fetchByAssoc($result1)) {
                if (!in_array($row['module'], $Related_Modules)) {
                    $Related_Modules[] = $row['module'];
                }
            }
        }

        if (count($Related_Modules) > 0) {
            foreach ($Related_Modules as $module) {
                $this->moduleModel->AddLinks($module);
            }
        }
    }

    public function retrieveCustomLinks()
    {
        $modules = getEmailRelatedModules();
        $emptyModuleName = false;
        $result = $this->db->pquery('SELECT module FROM vtiger_emakertemplates WHERE deleted=? GROUP BY module', array('0'));

        while ($row = $this->db->fetchByAssoc($result)) {
            if (!empty($row['module'])) {
                $modules[] = $row['module'];
            } else {
                $emptyModuleName = true;
            }
        }

        if ($emptyModuleName) {
            $entityModules = Vtiger_Module_Model::getEntityModules();

            foreach ($entityModules as $entityModule) {
                $module = $entityModule->getName();

                if (!in_array($module, $modules) && $entityModule->isEntityModule() && $entityModule->isActive()) {
                    $modules[] = $module;
                }
            }
        }

        foreach ($modules as $module) {
            $this->registerCustomLinks[] = array(
                $module,
                'DETAILVIEWSIDEBARWIDGET',
                'EMAILMaker',
                'module=EMAILMaker&view=GetEMAILActions&record=$RECORD$'
            );

            $this->registerCustomLinks[] = array(
                $module,
                'LISTVIEWMASSACTION',
                'Send Emails with EMAILMaker',
                'javascript:EMAILMaker_Actions_Js.getListViewPopup(this,\'$MODULE$\');'
            );
        }
    }

    public function updateFields()
    {
        $this->db->query('ALTER TABLE `vtiger_emakertemplates_relblockcriteria` CHANGE `value` `value` VARCHAR(250) NULL');
    }

    public function installWorkflows()
    {
        $this->installWorkflow("VTEMAILMakerMailTask", "Send Email from EMAIL Maker");
    }

    public function installWorkflow($name, $info)
    {
        $file_exist = false;
        $dest1 = "modules/com_vtiger_workflow/tasks/" . $name . ".inc";
        $source1 = "modules/EMAILMaker/workflow/" . $name . ".inc";
        if (file_exists($dest1)) {
            $file_exist1 = true;
        } else {
            if (copy($source1, $dest1)) {
                $file_exist1 = true;
            }
        }
        $dest2 = "layouts/v7/modules/Settings/Workflows/Tasks/" . $name . ".tpl";
        $source2 = "layouts/v7/modules/EMAILMaker/taskforms/" . $name . ".tpl";
        if (file_exists($dest2)) {
            $file_exist2 = true;
        } else {
            if (copy($source2, $dest2)) {
                $file_exist2 = true;
            }
        }
        if ($file_exist1 && $file_exist2) {
            $sql1 = "SELECT * FROM com_vtiger_workflow_tasktypes WHERE tasktypename = ?";
            $result1 = $this->db->pquery($sql1, array($name));
            if ($this->db->num_rows($result1) == 0) {
                $workflow_id = $this->db->getUniqueID("com_vtiger_workflow_tasktypes");
                $sql2 = "INSERT INTO `com_vtiger_workflow_tasktypes` (`id`, `tasktypename`, `label`, `classname`, `classpath`, `templatepath`, `modules`, `sourcemodule`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $this->db->pquery($sql2, array($workflow_id, $name, $info, $name, $source1, 'modules/EMAILMaker/taskforms/' . $name . '.tpl', '{"include":[],"exclude":[]}', 'EMAILMaker'));
            }
        }
    }

    public function deleteCustomLinks()
    {
        $this->retrieveCustomLinks();
        $this->updateCustomLinks(false);
    }

    private function removeWorkflows()
    {
        $sql1 = "DELETE FROM com_vtiger_workflow_tasktypes WHERE sourcemodule = ?";
        $this->db->pquery($sql1, array('EMAILMaker'));

        $sql2 = "DELETE FROM com_vtiger_workflowtasks WHERE task LIKE ?";
        $this->db->pquery($sql2, array('%:"VTEMAILMakerMailTask":%'));

        @shell_exec('rm -f modules/com_vtiger_workflow/tasks/VTEMAILMakerMailTask.inc');
        @shell_exec('rm -f layouts/v7/modules/Settings/Workflows/Tasks/VTEMAILMakerMailTask.tpl');
    }

    public function GetPageFormats()
    {
        return $this->pageFormats;
    }

    public function GetBasicModules()
    {
        return $this->basicModules;
    }

    public function GetProfilesActions()
    {
        return $this->profilesActions;
    }

    /**
     * @param bool $register
     */
    public function updateCustomLinks($register = true)
    {
        foreach ($this->registerCustomLinks as $customLink) {
            list($moduleName, $type, $label, $url, $icon, $sequence, $handlerInfo) = $customLink;

            $module = Vtiger_Module::getInstance($moduleName);
            $url = str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url);

            if ($module) {
                $module->deleteLink($type, $label);

                if ($register) {
                    $module->addLink($type, $label, $url, $icon, $sequence, $handlerInfo);
                }
            }
        }
    }

    /**
     * name, handler, frequency, module, sequence, description
     */
    public $registerCron = array(
        array('EMAILMaker - Birthday email', 'modules/EMAILMaker/cron/BirthdayEmail.service', 86400, 'EMAILMaker', 0, ''),
    );

    public function updateCron($register = true)
    {
        foreach ($this->registerCron as $cronInfo) {
            list($name, $handler, $frequency, $module, $sequence, $description) = $cronInfo;

            Vtiger_Cron::deregister($name);

            if ($register) {
                Vtiger_Cron::register($name, $handler, $frequency, $module, 1, $sequence, $description);
            }
        }
    }
}