<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Install_Model extends Core_Install_Model
{
    /**
     * @var array
     * [name, handler, frequency, module, sequence, description]
     */
    public array $registerCron = array(
        array('EMAILMaker - Birthday email', 'modules/EMAILMaker/cron/BirthdayEmail.php', 86400, 'EMAILMaker', 0, ''),
    );
    /**
     * [module, type, label, url, icon, sequence, handlerInfo]
     * @return array
     */
    public array $registerCustomLinks = array(
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerJS', 'layouts/$LAYOUT$/modules/EMAILMaker/resources/EMAILMakerActions.js'],
        ['EMAILMaker', 'HEADERSCRIPT', 'EMAILMakerMassEditJS', ''],
    );
    /**
     * @var array|array[]
     */
    public array $registerWorkflows = array(
        ['EMAILMaker', 'VTEMAILMakerMailTask', 'Send Email from EMAIL Maker'],
    );

    /**
     * @var string
     */
    protected string $moduleName = 'EMAILMaker';
    protected string $parentName = 'Tools';

    /**
     * @return void
     */
    public function actualizeSequenceTables(): void
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

    /**
     * @return void
     * @throws AppException
     */
    public function addCustomLinks(): void
    {
        $this->insertProductBlocks();
        $this->insertEmailTemplates();

        $this->retrieveCustomLinks();
        $this->updateCustomLinks();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);
    }

    /**
     * @return void
     */
    public function deleteCustomLinks(): void
    {
        $this->retrieveCustomLinks();
        $this->updateCustomLinks(false);
        $this->updateWorkflows(false);
    }

    public function insertProductBlocks()
    {
        $result = $this->db->pquery('SELECT id FROM vtiger_emakertemplates_productbloc_tpl');

        if (!$this->db->num_rows($result)) {
            $data = "INSERT INTO `vtiger_emakertemplates_productbloc_tpl` (`id`, `name`, `body`) VALUES
                (1, 'product block for individual tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_NET_PRICE%<br />\r\n				without TAX<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax% (%)</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%G_Tax%</strong> (<strong>$" . "CURRENCYCODE$</strong>)</span></td>\r\n			<td style=\"text-align: center;\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATPERCENT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTVATSUM$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTALSUM$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"11\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Subtotals</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "TOTALWITHOUTVAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				&nbsp;</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				<span style=\"text-align: right; \">$" . "VAT$</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SUBTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				Total with TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"10\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n'),
                (2, 'product block for group tax', '<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" style=\"font-size:10px;\" width=\"100%\">\r\n	<thead>\r\n		<tr bgcolor=\"#c0c0c0\">\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>Pos</strong></span></td>\r\n			<td colspan=\"2\" style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Qty%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><span style=\"font-weight: bold;\">Text</span></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_LBL_LIST_PRICE%<br />\r\n				</strong></span></td>\r\n			<td style=\"text-align: center;\">\r\n				<strong>%G_Subtotal%</strong></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%G_Discount%</strong></span></td>\r\n			<td style=\"TEXT-ALIGN: center\">\r\n				<span><strong>%M_Total%</strong></span></td>\r\n		</tr>\r\n	</thead>\r\n	<tbody>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_START#</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"text-align: center; vertical-align: top;\">\r\n				$" . "PRODUCTPOSITION$</td>\r\n			<td align=\"right\" valign=\"top\">\r\n				$" . "PRODUCTQUANTITY$</td>\r\n			<td align=\"left\" style=\"TEXT-ALIGN: center\" valign=\"top\">\r\n				$" . "PRODUCTUSAGEUNIT$</td>\r\n			<td align=\"left\" valign=\"top\">\r\n				$" . "PRODUCTNAME$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTLISTPRICE$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTTOTAL$</td>\r\n			<td align=\"right\" style=\"TEXT-ALIGN: right\" valign=\"top\">\r\n				$" . "PRODUCTDISCOUNT$</td>\r\n			<td align=\"right\" style=\"text-align: right;\" valign=\"top\">\r\n				$" . "PRODUCTSTOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"8\">\r\n				#PRODUCTBLOC_END#</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span>%G_LBL_NET_PRICE% without TAX</span></td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALWITHOUTVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Discount%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				Total without TAX</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "TOTALAFTERDISCOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_Tax% $" . "VATPERCENT$ % %G_LBL_LIST_OF% $" . "TOTALAFTERDISCOUNT$</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "VAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				Total with TAX</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "TOTALWITHVAT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"text-align: left;\">\r\n				%G_LBL_SHIPPING_AND_HANDLING_CHARGES%</td>\r\n			<td style=\"text-align: right;\">\r\n				$" . "SHTAXAMOUNT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_LBL_TAX_FOR_SHIPPING_AND_HANDLING%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "SHTAXTOTAL$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				%G_Adjustment%</td>\r\n			<td style=\"TEXT-ALIGN: right\">\r\n				$" . "ADJUSTMENT$</td>\r\n		</tr>\r\n		<tr>\r\n			<td colspan=\"7\" style=\"TEXT-ALIGN: left\">\r\n				<span style=\"font-weight: bold;\">%G_LBL_GRAND_TOTAL% </span><strong>($" . "CURRENCYCODE$)</strong></td>\r\n			<td nowrap=\"nowrap\" style=\"TEXT-ALIGN: right\">\r\n				<strong>$" . "TOTAL$</strong></td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n');";

            $this->db->pquery($data);
        }
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
            'vtiger_emakertemplates',
            'vtiger_emakertemplates_attch',
            'vtiger_emakertemplates_emails',
            'vtiger_emakertemplates_sent',
            'vtiger_emakertemplates_settings',
            'vtiger_emakertemplates_relblocks',
            'vtiger_emakertemplates_relblockcol',
            'vtiger_emakertemplates_relblockcriteria',
            'vtiger_emakertemplates_relblockcriteria_g',
            'vtiger_emakertemplates_relblockdatefilter',
            'vtiger_emakertemplates_productbloc_tpl',
            'vtiger_emakertemplates_ignorepicklistvalues',
            'vtiger_emakertemplates_profilespermissions',
            'vtiger_emakertemplates_picklists',
            'vtiger_emakertemplates_sharing',
            'vtiger_emakertemplates_default_from',
            'vtiger_emakertemplates_drips',
            'vtiger_emakertemplates_drip_groups',
            'vtiger_emakertemplates_delay',
            'vtiger_emakertemplates_drip_tpls',
            'vtiger_emakertemplates_sharing_drip',
            'vtiger_emakertemplates_documents',
            'vtiger_emakertemplates_userstatus',
            'vtiger_emakertemplates_label_keys',
            'vtiger_emakertemplates_label_vals',
            'vtiger_emakertemplates_images',
            'vtiger_emakertemplates_relblocksortcol',
            'vtiger_emakertemplates_me',
            'vtiger_emakertemplates_contents',
            'vtiger_emakertemplates_displayed',
        ];
    }

    /**
     * @return void
     * @throws AppException
     */
    public function install(): void
    {
        switch ($this->eventType) {
            case 'module.postinstall':
                $this->updateCron();
                $this->addCustomLinks();
                break;
            case 'module.enabled':
                $this->updateProfilePermissions();
                $this->addCustomLinks();
                $this->updateCron();
                break;
            case 'module.postupdate':
                $this->updateProfilePermissions();
                $this->addCustomLinks();
                break;
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
            case 'module.disabled':
                $this->deleteCustomLinks();
                $this->updateCron(false);
                break;
            case 'module.preuninstall':
                $this->updateCron(false);
                $this->updateWorkflows(false);
                $this->deleteCustomLinks();
                break;
        }
    }

    /**
     * @return void
     * @throws AppException
     */
    public function installTables(): void
    {
        $this->getTable('vtiger_emakertemplates', 'templateid')
            ->createTable()
            ->createColumn('foldername', 'varchar(100) DEFAULT NULL')
            ->createColumn('templatename', 'varchar(100) DEFAULT NULL')
            ->createColumn('subject', 'varchar(255) DEFAULT NULL')
            ->createColumn('description', 'longtext DEFAULT NULL')
            ->createColumn('body', 'longtext DEFAULT NULL')
            ->createColumn('deleted', 'int(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('module', 'varchar(255) DEFAULT NULL')
            ->createColumn('owner', 'int(11) NOT NULL DEFAULT \'1\'')
            ->createColumn('sharingtype', 'char(7) NOT NULL DEFAULT \'public\'')
            ->createColumn('category', 'varchar(255) DEFAULT NULL')
            ->createColumn('is_listview', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('is_theme', 'int(1) DEFAULT \'0\'')
            ->createColumn('load_related_documents', 'TINYINT(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('folders_related_documents', 'VARCHAR(255) NULL')
            ->createKey('KEY IF NOT EXISTS `emakertemplates_foldernamd_templatename_subject_idx` (`foldername`,`templatename`,`subject`)')
            ->createKey('KEY IF NOT EXISTS `deleted` (`deleted`)')
            ->createKey('KEY IF NOT EXISTS `is_listview` (`is_listview`)')
            ->createKey('KEY IF NOT EXISTS `is_theme` (`is_theme`)')
        ;
        $this->getTable('vtiger_emakertemplates_attch', 'attid')
            ->createTable()
            ->createColumn('esentid', 'int(11) NOT NULL')
            ->createColumn('filename', 'varchar(255) NOT NULL')
            ->createColumn('file_desc', 'varchar(255) NOT NULL')
            ->createColumn('type', 'varchar(255) NOT NULL')
            ->createKey('KEY IF NOT EXISTS `attid` (`attid`)');

        $this->getTable('vtiger_emakertemplates_emails', 'emailid')
            ->createTable()
            ->createColumn('esentid', 'int(11) NOT NULL')
            ->createColumn('pid', 'int(11) DEFAULT NULL')
            ->createColumn('email', 'varchar(255) NOT NULL')
            ->createColumn('email_address', 'varchar(250) DEFAULT NULL')
            ->createColumn('cc', 'longtext DEFAULT NULL')
            ->createColumn('bcc', 'longtext DEFAULT NULL')
            ->createColumn('status', 'int(2) NOT NULL DEFAULT \'0\'')
            ->createColumn('parent_id', 'int(11) DEFAULT NULL')
            ->createColumn('error', 'text DEFAULT NULL')
            ->createColumn('cc_ids', 'text DEFAULT NULL')
            ->createColumn('bcc_ids', 'text DEFAULT NULL')
            ->createColumn('email_send_date', 'datetime DEFAULT NULL')
            ->createColumn('deleted', 'int(11) DEFAULT \'0\'')
            ->createKey('KEY IF NOT EXISTS`esentid` (`esentid`)')
            ->createKey('KEY IF NOT EXISTS`pid` (`pid`)')
            ->createKey('KEY IF NOT EXISTS`status` (`status`)')
            ->createKey('KEY IF NOT EXISTS`deleted` (`deleted`)')
            ->createKey('KEY IF NOT EXISTS`parent_id` (`parent_id`)')
            ->createKey('KEY IF NOT EXISTS`esentid_2` (`esentid`,`status`,`deleted`)');

        $this->getTable('vtiger_emakertemplates_sent', 'esentid')
            ->createTable()
            ->createColumn('from_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('from_email', 'varchar(255) DEFAULT NULL')
            ->createColumn('subject', 'varchar(255) DEFAULT NULL')
            ->createColumn('body', 'longtext DEFAULT NULL')
            ->createColumn('type', 'int(5) NOT NULL')
            ->createColumn('pdf_template_ids', 'varchar(255) DEFAULT NULL')
            ->createColumn('pdf_language', 'varchar(255) DEFAULT NULL')
            ->createColumn('total_emails', 'int(11) DEFAULT \'0\'')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('attachments', 'int(2) DEFAULT \'0\'')
            ->createColumn('att_documents', 'text DEFAULT NULL')
            ->createColumn('send_date', 'datetime DEFAULT NULL')
            ->createColumn('drip_group', 'int(11) DEFAULT NULL')
            ->createColumn('drip_delay', 'decimal(11,0) DEFAULT NULL')
            ->createColumn('total_sent_emails', 'decimal(11,0) DEFAULT \'0\'')
            ->createColumn('saved_drip_delay', 'decimal(11,0) DEFAULT NULL')
            ->createColumn('related_to', 'int(11) DEFAULT NULL')
            ->createColumn('ids_for_pdf', 'text DEFAULT NULL')
            ->createColumn('pmodule', 'varchar(255) DEFAULT NULL')
            ->createColumn('language', 'varchar(255) DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS`userid` (`userid`)')
            ->createKey('KEY IF NOT EXISTS`related_to` (`related_to`)');

        $this->getTable('vtiger_emakertemplates_settings', null)
            ->createTable('decimals', 'tinyint(2) NOT NULL')
            ->createColumn('decimal_point', 'char(2) NOT NULL')
            ->createColumn('thousands_separator', 'char(2) NOT NULL')
            ->createColumn('phpmailer_version', 'varchar(50) DEFAULT \'emailmaker\'');

        $this->getTable('vtiger_emakertemplates_relblocks', null)
            ->createTable('relblockid', 'int(11) NOT NULL')
            ->createColumn('name', 'varchar(255) NOT NULL')
            ->createColumn('module', 'varchar(255) NOT NULL')
            ->createColumn('secmodule', 'varchar(255) NOT NULL')
            ->createColumn('block', 'longtext NOT NULL')
            ->createColumn('deleted', 'tinyint(4) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`relblockid`)');

        $this->getTable('vtiger_emakertemplates_relblockcol', null)
            ->createTable('colid', 'int(19) NOT NULL')
            ->createColumn('relblockid', 'int(19) NOT NULL')
            ->createColumn('columnname', 'varchar(250) NOT NULL')
            ->createColumn('sortorder', 'varchar(250) NOT NULL')
            ->createColumn('sortsequence', 'tinyint(4) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`relblockid`,`colid`)');

        $this->getTable('vtiger_emakertemplates_relblockcriteria', null)
            ->createTable('relblockid', 'int(11) NOT NULL')
            ->createColumn('colid', 'int(11) NOT NULL')
            ->createColumn('columnname', 'varchar(250) NOT NULL')
            ->createColumn('comparator', 'varchar(250) NOT NULL')
            ->createColumn('value', 'varchar(250) DEFAULT NULL')
            ->createColumn('groupid', 'int(11) NOT NULL')
            ->createColumn('column_condition', 'varchar(250) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`relblockid`,`colid`)');

        $this->getTable('vtiger_emakertemplates_relblockcriteria_g', null)
            ->createTable('groupid', 'int(11) NOT NULL')
            ->createColumn('relblockid', 'int(11) NOT NULL')
            ->createColumn('group_condition', 'varchar(250) DEFAULT NULL')
            ->createColumn('condition_expression', 'text DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`groupid`,`relblockid`)');

        $this->getTable('vtiger_emakertemplates_relblockdatefilter', null)
            ->createTable('datefilterid', 'int(11) NOT NULL')
            ->createColumn('datecolumnname', 'varchar(250) NOT NULL')
            ->createColumn('datefilter', 'varchar(250) NOT NULL')
            ->createColumn('startdate', 'date NOT NULL')
            ->createColumn('enddate', 'date NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`datefilterid`)');

        $this->getTable('vtiger_emakertemplates_productbloc_tpl', 'id')
            ->createTable()
            ->createColumn('name', 'varchar(255) NOT NULL')
            ->createColumn('body', 'longtext NOT NULL');

        $this->getTable('vtiger_emakertemplates_ignorepicklistvalues', null)
            ->createTable('value', 'varchar(100) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`value`)');

        $this->getTable('vtiger_emakertemplates_profilespermissions', null)
            ->createTable('profileid', 'int(11) NOT NULL')
            ->createColumn('operation', 'int(11) NOT NULL')
            ->createColumn('permissions', 'int(1) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`profileid`,`operation`)');

        $this->getTable('vtiger_emakertemplates_picklists', null)
            ->createTable('tabid', 'int(11) NOT NULL')
            ->createColumn('count', 'decimal(10,0) DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `tabid` (`tabid`)')
            ->createKey('KEY IF NOT EXISTS `count` (`count`)');

        $this->getTable('vtiger_emakertemplates_sharing', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('shareid', 'varchar(10) NOT NULL')
            ->createColumn('setype', 'varchar(200) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS(`templateid`,`shareid`,`setype`)');

        $this->getTable('vtiger_emakertemplates_default_from', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('fieldname', 'varchar(255) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `templateid` (`templateid`,`userid`)');

        $this->getTable('vtiger_emakertemplates_drips', null)
            ->createTable('dripid', 'int(11) NOT NULL')
            ->createColumn('dripname', 'varchar(255) NOT NULL')
            ->createColumn('description', 'text DEFAULT NULL')
            ->createColumn('module', 'varchar(255) NOT NULL')
            ->createColumn('owner', 'int(11) NOT NULL')
            ->createColumn('sharingtype', 'varchar(7) NOT NULL')
            ->createColumn('deleted', 'int(5) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`dripid`)');

        $this->getTable('vtiger_emakertemplates_drip_groups', null)
            ->createTable('drip_group_id', 'int(11) NOT NULL')
            ->createColumn('drip_group_name', 'varchar(255) NOT NULL')
            ->createColumn('drip_group_save_date', 'datetime NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`drip_group_id`)');

        $this->getTable('vtiger_emakertemplates_delay', null)
            ->createTable('delay_active', 'int(2) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`delay_active`)');

        $this->getTable('vtiger_emakertemplates_drip_tpls', null)
            ->createTable('driptplid', 'int(11) NOT NULL')
            ->createColumn('dripid', 'int(11) NOT NULL')
            ->createColumn('templateid', 'int(11) NOT NULL')
            ->createColumn('delay', 'int(11) NOT NULL')
            ->createColumn('deleted', 'int(2) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`driptplid`)')
            ->createKey('KEY IF NOT EXISTS `templateid` (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `dripid` (`dripid`)');

        $this->getTable('vtiger_emakertemplates_sharing_drip', null)
            ->createTable('dripid', 'int(11) NOT NULL')
            ->createColumn('shareid', 'varchar(10) NOT NULL')
            ->createColumn('setype', 'varchar(200) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`dripid`,`shareid`,`setype`)');

        $this->getTable('vtiger_emakertemplates_documents', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('documentid', 'int(11) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `templateid_2` (`templateid`,`documentid`)')
            ->createKey('KEY IF NOT EXISTS `templateid` (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `documentid` (`documentid`)');

        $this->getTable('vtiger_emakertemplates_userstatus', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('is_active', 'tinyint(1) NOT NULL')
            ->createColumn('is_default', 'tinyint(1) NOT NULL')
            ->createColumn('sequence', 'int(6) NOT NULL DEFAULT \'1\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`,`userid`)');

        $this->getTable('vtiger_emakertemplates_label_keys', 'label_id')
            ->createTable()
            ->createColumn('label_key', 'varchar(128) NOT NULL')
            ->createKey('UNIQUE KEY IF NOT EXISTS `label_key` (`label_key`)');

        $this->getTable('vtiger_emakertemplates_label_vals', null)
            ->createTable('label_id', 'int(11) NOT NULL')
            ->createColumn('lang_id', 'int(11) NOT NULL')
            ->createColumn('label_value', 'varchar(1024) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`label_id`,`lang_id`)');

        $this->getTable('vtiger_emakertemplates_images', null)
            ->createTable('crmid', 'int(11) NOT NULL')
            ->createColumn('productid', 'int(11) NOT NULL')
            ->createColumn('sequence', 'int(11) NOT NULL')
            ->createColumn('attachmentid', 'int(11) NOT NULL')
            ->createColumn('width', 'int(11) DEFAULT NULL')
            ->createColumn('height', 'int(11) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`crmid`,`productid`,`sequence`)');

        $this->getTable('vtiger_emakertemplates_relblocksortcol', null)
            ->createTable('sortcolid', 'int(19) NOT NULL')
            ->createColumn('relblockid', 'int(19) NOT NULL')
            ->createColumn('columnname', 'varchar(250) DEFAULT \'\'')
            ->createColumn('sortorder', 'varchar(250) DEFAULT \'Asc\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`sortcolid`,`relblockid`)')
            ->createKey('KEY IF NOT EXISTS `fk_1_vtiger_emakertemplates_relblocksortcol` (`relblockid`)');

        $this->getTable('vtiger_emakertemplates_me', 'meid')
            ->createTable()
            ->createColumn('meid', 'int(11) NOT NULL')
            ->createColumn('description', 'longtext DEFAULT NULL')
            ->createColumn('templateid', 'int(11) NOT NULL')
            ->createColumn('listid', 'int(11) NOT NULL')
            ->createColumn('start_of', 'datetime NOT NULL')
            ->createColumn('status', 'varchar(200) NOT NULL')
            ->createColumn('userid', 'int(11) NOT NULL')
            ->createColumn('from_name', 'varchar(255) NOT NULL')
            ->createColumn('from_email', 'varchar(255) NOT NULL')
            ->createColumn('deleted', 'int(2) NOT NULL')
            ->createColumn('esentid', 'int(11) DEFAULT NULL')
            ->createColumn('unsubscribes', 'int(11) DEFAULT \'0\'')
            ->createColumn('total_entries', 'int(11) DEFAULT \'0\'')
            ->createColumn('max_limit', 'int(11) DEFAULT NULL')
            ->createColumn('me_subject', 'varchar(255) NOT NULL')
            ->createColumn('language', 'varchar(50) DEFAULT NULL')
            ->createColumn('email_fieldname', 'varchar(255) DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `templateid` (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `listid` (`listid`)')
            ->createKey('KEY IF NOT EXISTS `start_of` (`start_of`)')
            ->createKey('KEY IF NOT EXISTS `status` (`status`)')
            ->createKey('KEY IF NOT EXISTS `userid` (`userid`)')
            ->createKey('KEY IF NOT EXISTS `deleted` (`deleted`)')
            ->createKey('KEY IF NOT EXISTS `esentid` (`esentid`)')
            ->createKey('KEY IF NOT EXISTS `unsubscribes` (`unsubscribes`)')
            ->createKey('KEY IF NOT EXISTS `total_entries` (`total_entries`)')
            ->createKey('KEY IF NOT EXISTS `max_limit` (`max_limit`)');

        $this->getTable('vtiger_emakertemplates_contents', 'contentid')
            ->createTable()
            ->createColumn('activityid', 'int(11) NOT NULL')
            ->createColumn('emailid', 'int(11) NOT NULL')
            ->createColumn('content', 'longtext NOT NULL')
            ->createKey('KEY IF NOT EXISTS `activityid` (`activityid`)')
            ->createKey('KEY IF NOT EXISTS `emailid` (`emailid`)');

        $this->getTable('vtiger_emakertemplates_displayed', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('displayed', 'int(11) NOT NULL')
            ->createColumn('conditions', 'longtext NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`)')
            ->createKey('KEY IF NOT EXISTS `displayed` (`displayed`)');
    }

    public function getTemplateModules(): array
    {
        $emptyModuleName = false;
        $modules = [];
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

                if ($entityModule->isEntityModule() && $entityModule->isActive()) {
                    $modules[] = $module;
                }
            }
        }

        return $modules;
    }

    /**
     * @return void
     */
    public function retrieveCustomLinks(): void
    {
        $entityModules = Vtiger_Module_Model::getEntityModules();
        $modules = [];

        foreach ($entityModules as $entityModule) {
            $module = $entityModule->getName();

            if ($entityModule->isEntityModule() && $entityModule->isActive()) {
                $modules[] = $module;
            }
        }

        foreach ($modules as $module) {
            $this->registerCustomLinks[] = array(
                $module,
                'DETAILVIEWBASIC',
                'Send Email',
                'javascript:EMAILMaker_Actions_Js.getDetailViewPopup(this,\'$MODULE$\');',
                '<i class="fa fa-paper-plane" aria-hidden="true"></i>',
            );

            $this->registerCustomLinks[] = array(
                $module,
                'LISTVIEWMASSACTION',
                'Send Email',
                'javascript:EMAILMaker_Actions_Js.getListViewPopup(this,\'$MODULE$\');',
                '<i class="fa fa-paper-plane" aria-hidden="true"></i>',
            );
        }
    }

    /**
     * @return void
     */
    public function updateProfilePermissions(): void
    {
        $vtigerResult = $this->db->pquery(
            'SELECT tabid FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = ?)',
            array('EMAILMaker')
        );

        if ($this->db->num_rows($vtigerResult)) {
            $emailMakerResult = $this->db->query('SELECT * FROM vtiger_emakertemplates_profilespermissions');

            if (!$this->db->num_rows($emailMakerResult)) {
                $this->db->pquery(
                    'INSERT INTO vtiger_emakertemplates_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                    array('EMAILMaker')
                );
            }

            $this->db->pquery(
                'DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                array('EMAILMaker')
            );
        }
    }

    public function insertEmailTemplates()
    {
        $userId = Users::getActiveAdminId();
        $templates = [
            'Reminder' => [
                'templatename' => 'Reminder',
                'module' => 'Appointments',
                'description' => 'Reminder',
                'subject' => 'Reminder',
                'body' => 'This is a reminder notification for the Activity',
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Invitation' => [
                'templatename' => 'Invitation',
                'module' => 'Appointments',
                'description' => 'Invitation',
                'subject' => 'Invitation',
                'body' => 'Invitation',
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Customer Login Details' => [
                'templatename' => 'Customer Login Details',
                'module' => 'Contacts',
                'description' => 'Customer Portal Login Details',
                'subject' => 'Customer Portal Login Details',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/CustomerLoginDetails.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Support end notification before a month' => [
                'templatename' => 'Support end notification before a month',
                'module' => 'Contacts',
                'description' => 'Send Notification mail to customer before a month of support end date',
                'subject' => 'VtigerCRM Support Notification',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/SupportNotificationMonth.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
            'Support end notification before a week' => [
                'templatename' => 'Support end notification before a week',
                'module' => 'Contacts',
                'description' => 'Send Notification mail to customer before a week of support end date',
                'subject' => 'VtigerCRM Support Notification',
                'body' => file_get_contents('modules/EMAILMaker/resources/templates/SupportNotificationWeek.html'),
                'owner' => $userId,
                'sharingtype' => 'private',
                'category' => 'system',
                'is_listview' => 0,
            ],
        ];

        if (!empty($this->db->getColumnNames('vtiger_emailtemplates'))) {
            $result = $this->db->pquery('SELECT * FROM vtiger_emailtemplates');

            while ($row = $this->db->fetchByAssoc($result)) {
                $templateName = trim($row['templatename']);

                if (empty($templates[$templateName])) {
                    $templates[$templateName] = [
                        'templatename' => $templateName,
                        'module' => $row['module'],
                        'description' => $templateName,
                        'subject' => $row['subject'],
                        'body' => decode_html($row['body']),
                        'owner' => $userId,
                        'sharingtype' => 'private',
                        'category' => 'email templates',
                        'is_listview' => 0,
                    ];
                }
            }
        }

        foreach ($templates as $template) {
            $sql = 'SELECT templatename FROM vtiger_emakertemplates WHERE templatename=? AND deleted=?';
            $params = [$template['templatename'], '0'];

            if (!empty($template['module'])) {
                $sql .= ' AND module=?';
                $params[] = $template['module'];
            }

            $result = $this->db->pquery($sql, $params);

            if (!$this->db->num_rows($result)) {
                EMAILMaker_Record_Model::saveTemplate($template, 0);
            }
        }
    }
}