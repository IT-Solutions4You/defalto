<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_Install_Model extends Core_Install_Model
{
    public string $moduleName = 'PDFMaker';
    public string $parentName = 'Tools';

    public array $registerCustomLinks = [
        ['PDFMaker', 'HEADERSCRIPT', 'PDFMakerFreeActionsJS', 'layouts/$LAYOUT$/modules/PDFMaker/resources/PDFMakerFreeActions.js'],
        ['Quotes', 'DETAILVIEWBASIC', 'PDFMaker', 'template:GetPDFButtons.tpl:PDFMaker'],
        ['SalesOrder', 'DETAILVIEWBASIC', 'PDFMaker', 'template:GetPDFButtons.tpl:PDFMaker'],
        ['PurchaseOrder', 'DETAILVIEWBASIC', 'PDFMaker', 'template:GetPDFButtons.tpl:PDFMaker'],
        ['Invoice', 'DETAILVIEWBASIC', 'PDFMaker', 'template:GetPDFButtons.tpl:PDFMaker'],
    ];

    public function addCustomLinks(): void
    {
        $this->updateCustomLinks();
        $this->insertPDFTemplates();

        Settings_MenuEditor_Module_Model::addModuleToApp($this->moduleName, $this->parentName);
    }

    public function deleteCustomLinks(): void
    {
        $this->registerCustomLinks[] = ['Quotes', 'DETAILVIEWSIDEBARWIDGET', 'PDFMaker'];
        $this->registerCustomLinks[] = ['SalesOrder', 'DETAILVIEWSIDEBARWIDGET', 'PDFMaker'];
        $this->registerCustomLinks[] = ['PurchaseOrder', 'DETAILVIEWSIDEBARWIDGET', 'PDFMaker'];
        $this->registerCustomLinks[] = ['Invoice', 'DETAILVIEWSIDEBARWIDGET', 'PDFMaker'];
        $this->registerCustomLinks[] = ['PDFMaker', 'HEADERSCRIPT', 'PDFMakerFreeActionsJS'];
        $this->updateCustomLinks(false);
        $this->deleteLinks();
    }

    public function deleteLinks()
    {
        $this->db->pquery(
            'DELETE FROM vtiger_links WHERE linklabel = ? AND linktype = ? AND linkurl = ?',
            ['PDFMakerJS', 'HEADERSCRIPT', 'modules/PDFMaker/PDFMakerActions.js']
        );
        $this->db->pquery(
            'DELETE FROM vtiger_links WHERE linklabel = ? AND linktype = ? AND linkurl LIKE ?',
            ['PDF Export', 'LISTVIEWBASIC', '%getPDFListViewPopup2%']
        );
        $this->db->pquery(
            'DELETE FROM vtiger_links  WHERE linklabel = ? AND linktype = ? AND linkurl = ?',
            ['PDFMaker', 'DETAILVIEWWIDGET', 'module=PDFMaker&action=PDFMakerAjax&file=getPDFActions&record=$RECORD$']
        );
        $result = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE isentitytype = ?', [1]);

        while ($row = $this->db->fetchByAssoc($result)) {
            Vtiger_Link::deleteLink($row['tabid'], "DETAILVIEWWIDGET", 'PDFMaker');
            Vtiger_Link::deleteLink($row['tabid'], "DETAILVIEWSIDEBARWIDGET", 'PDFMaker');
            Vtiger_Link::deleteLink($row['tabid'], "LISTVIEWMASSACTION", 'PDF Export');
            Vtiger_Link::deleteLink($row['tabid'], "LISTVIEWMASSACTION", 'PDF Export');
        }

        Vtiger_Link::deleteAll(getTabId('PDFMaker'));
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [
            'vtiger_pdfmaker',
            'vtiger_pdfmaker_breakline',
            'vtiger_pdfmaker_ignorepicklistvalues',
            'vtiger_pdfmaker_images',
            'vtiger_pdfmaker_productbloc_tpl',
            'vtiger_pdfmaker_releases',
            'vtiger_pdfmaker_seq',
            'vtiger_pdfmaker_settings',
        ];
    }

    public function insertPDFTemplates()
    {
        /** @var PDFMaker_Record_Model $recordModel */
        $recordModel = Vtiger_Record_Model::getCleanInstance('PDFMaker');
        // Template 01
        $templateId = $recordModel->getTemplateIdByName('Invoice');
        $data = [
            'templateid' => $templateId,
            'filename' => 'Invoice',
            'module' => 'Invoice',
            'body' => file_get_contents('modules/PDFMaker/resources/templates/Invoice.html'),
            'description' => 'Template for Invoice',
            'deleted' => '0',
        ];
        $dataSettings = [
            'templateid' => $templateId,
            'margin_top' => 2.0,
            'margin_bottom' => 2.0,
            'margin_left' => 2.0,
            'margin_right' => 2.0,
            'format' => 'A4',
            'orientation' => 'portrait',
            'decimals' => 2,
            'decimal_point' => ',',
            'thousands_separator' => '.',
            'header' => file_get_contents('modules/PDFMaker/resources/templates/InvoiceHeader.html'),
            'footer' => file_get_contents('modules/PDFMaker/resources/templates/InvoiceFooter.html'),
            'encoding' => 'auto',
            'file_name' => null,
            'is_portal' => 0,
            'is_listview' => 0,
            'owner' => 1,
            'sharingtype' => 'public',
            'disp_header' => 3,
            'disp_footer' => 7,
        ];
        $recordModel->updateTemplate($data, $dataSettings);

        // Template 02
        $templateId = $recordModel->getTemplateIdByName('SalesOrder');
        $data = [
            'templateid' => $templateId,
            'filename' => 'SalesOrder',
            'module' => 'SalesOrder',
            'body' => file_get_contents('modules/PDFMaker/resources/templates/SalesOrder.html'),
            'description' => 'Template for SalesOrder',
            'deleted' => '0',
        ];
        $dataSettings = [
            'templateid' => $templateId,
            'margin_top' => 2.0,
            'margin_bottom' => 2.0,
            'margin_left' => 2.0,
            'margin_right' => 2.0,
            'format' => 'A4',
            'orientation' => 'portrait',
            'decimals' => 2,
            'decimal_point' => ',',
            'thousands_separator' => '',
            'header' => file_get_contents('modules/PDFMaker/resources/templates/SalesOrderHeader.html'),
            'footer' => file_get_contents('modules/PDFMaker/resources/templates/SalesOrderFooter.html'),
            'encoding' => 'auto',
            'file_name' => null,
            'is_portal' => 0,
            'is_listview' => 0,
            'owner' => 1,
            'sharingtype' => 'public',
            'disp_header' => 3,
            'disp_footer' => 7,
        ];
        $recordModel->updateTemplate($data, $dataSettings);

        // Template 03
        $templateId = $recordModel->getTemplateIdByName('PurchaseOrder');
        $data = [
            'templateid' => $templateId,
            'filename' => 'PurchaseOrder',
            'module' => 'PurchaseOrder',
            'body' => file_get_contents('modules/PDFMaker/resources/templates/PurchaseOrder.html'),
            'description' => 'Template for PurchaseOrder',
            'deleted' => '0',
        ];
        $dataSettings = [
            'templateid' => $templateId,
            'margin_top' => 2.0,
            'margin_bottom' => 2.0,
            'margin_left' => 2.0,
            'margin_right' => 2.0,
            'format' => 'A4',
            'orientation' => 'portrait',
            'decimals' => 2,
            'decimal_point' => ',',
            'thousands_separator' => '',
            'header' => file_get_contents('modules/PDFMaker/resources/templates/PurchaseOrderHeader.html'),
            'footer' => file_get_contents('modules/PDFMaker/resources/templates/PurchaseOrderFooter.html'),
            'encoding' => 'auto',
            'file_name' => null,
            'is_portal' => 0,
            'is_listview' => 0,
            'owner' => 1,
            'sharingtype' => 'public',
            'disp_header' => 3,
            'disp_footer' => 7,
        ];
        $recordModel->updateTemplate($data, $dataSettings);

        // Template 04
        $templateId = $recordModel->getTemplateIdByName('Quotes');
        $data = [
            'templateid' => $templateId,
            'filename' => 'Quotes',
            'module' => 'Quotes',
            'body' => file_get_contents('modules/PDFMaker/resources/templates/Quotes.html'),
            'description' => 'Templates for Quotes',
            'deleted' => '0',
        ];
        $dataSettings = [
            'templateid' => $templateId,
            'margin_top' => 2.0,
            'margin_bottom' => 2.0,
            'margin_left' => 2.0,
            'margin_right' => 2.0,
            'format' => 'A4',
            'orientation' => 'portrait',
            'decimals' => 2,
            'decimal_point' => ',',
            'thousands_separator' => '',
            'header' => file_get_contents('modules/PDFMaker/resources/templates/QuotesHeader.html'),
            'footer' => file_get_contents('modules/PDFMaker/resources/templates/QuotesFooter.html'),
            'encoding' => 'auto',
            'file_name' => null,
            'is_portal' => 0,
            'is_listview' => 0,
            'owner' => 1,
            'sharingtype' => 'public',
            'disp_header' => 3,
            'disp_footer' => 7,
        ];
        $recordModel->updateTemplate($data, $dataSettings);
    }

    public function install(): void
    {
        switch ($this->eventType) {
            case 'module.postupdate':
            case 'module.enabled':
            case 'module.postinstall':
                $this->addCustomLinks();
                $this->updatePermissions();
                break;
            case 'module.disabled':
            case 'module.preuninstall':
            case 'module.preupdate':
                $this->deleteCustomLinks();
                break;
        }
    }

    /**
     * @return void
     */
    public function updatePermissions(): void
    {
        $result = $this->db->pquery(
            'SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = ?)',
            ['PDFMaker']
        );

        if ($this->db->num_rows($result)) {
            $result = $this->db->pquery(
                'SELECT * FROM vtiger_pdfmaker_profilespermissions',
                []
            );

            if (0 === $this->db->num_rows($result)) {
                $this->db->pquery(
                    'INSERT INTO vtiger_pdfmaker_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                    ['PDFMaker']
                );
            }

            $this->db->pquery(
                'DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)',
                ['PDFMaker']
            );
        }
    }

    public function installTables(): void
    {
        $this->getTable('vtiger_pdfmaker', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('filename', 'varchar(100) NOT NULL')
            ->createColumn('module', 'varchar(255) NOT NULL')
            ->createColumn('body', 'longblob NOT NULL')
            ->createColumn('description', 'text NOT NULL')
            ->createColumn('deleted', 'int(1) NOT NULL DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`)');

        $this->getTable('vtiger_pdfmaker_breakline', null)
            ->createTable('crmid', 'int(11) NOT NULL')
            ->createColumn('productid', 'int(11) NOT NULL')
            ->createColumn('sequence', 'int(11) NOT NULL')
            ->createColumn('show_header', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('show_subtotal', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`crmid`,`productid`,`sequence`)');

        $this->getTable('vtiger_pdfmaker_ignorepicklistvalues', null)
            ->createTable('value', 'varchar(100) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`value`)');

        $this->getTable('vtiger_pdfmaker_images', null)
            ->createTable('crmid', 'int(11) NOT NULL')
            ->createColumn('productid', 'int(11) NOT NULL')
            ->createColumn('sequence', 'int(11) NOT NULL')
            ->createColumn('attachmentid', 'int(11) NOT NULL')
            ->createColumn('width', 'int(11) DEFAULT NULL')
            ->createColumn('height', 'int(11) DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`crmid`,`productid`,`sequence`)');

        $this->getTable('vtiger_pdfmaker_productbloc_tpl', 'id')
            ->createTable()
            ->createColumn('name', 'varchar(255) NOT NULL')
            ->createColumn('body', 'longtext NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');

        $this->getTable('vtiger_pdfmaker_releases', 'id')
            ->createTable()
            ->createColumn('version', 'varchar(10) NOT NULL')
            ->createColumn('date', 'datetime NOT NULL')
            ->createColumn('updated', 'tinyint(1) NOT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');

        $this->getTable('vtiger_pdfmaker_settings', null)
            ->createTable('templateid', 'int(11) NOT NULL')
            ->createColumn('margin_top', 'decimal(12,1) DEFAULT NULL')
            ->createColumn('margin_bottom', 'decimal(12,1) DEFAULT NULL')
            ->createColumn('margin_left', 'decimal(12,1) DEFAULT NULL')
            ->createColumn('margin_right', 'decimal(12,1) DEFAULT NULL')
            ->createColumn('format', 'varchar(255) NOT NULL DEFAULT \'A4\'')
            ->createColumn('orientation', 'varchar(255) NOT NULL DEFAULT \'portrait\'')
            ->createColumn('decimals', 'tinyint(2) NOT NULL')
            ->createColumn('decimal_point', 'char(2) NOT NULL')
            ->createColumn('thousands_separator', 'char(2) NOT NULL')
            ->createColumn('header', 'text NOT NULL')
            ->createColumn('footer', 'text NOT NULL')
            ->createColumn('encoding', 'varchar(20) NOT NULL DEFAULT \'auto\'')
            ->createColumn('file_name', 'varchar(255) DEFAULT NULL')
            ->createColumn('is_portal', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('is_listview', 'tinyint(1) NOT NULL DEFAULT \'0\'')
            ->createColumn('owner', 'int(11) NOT NULL DEFAULT \'1\'')
            ->createColumn('sharingtype', 'char(7) NOT NULL DEFAULT \'public\'')
            ->createColumn('disp_header', 'tinyint(1) NOT NULL DEFAULT \'3\'')
            ->createColumn('disp_footer', 'tinyint(1) NOT NULL DEFAULT \'7\'')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`templateid`)');
    }

    public function migrate(): void
    {
        define('MIGRATE_DATA', true);

        $this->insertPDFTemplates();
    }
}