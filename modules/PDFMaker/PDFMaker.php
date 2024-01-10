<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PDFMaker
{
    public $log;
    public $db;
    public $list_fields_name = [];
    public $list_fields = [];
    public $related_tables = [];

    public $moduleName = 'PDFMaker';
    public $moduleLabel = 'PDFMaker';
    public $parentName = 'Tools';
    /**
     * @var mixed|null
     */
    public $id;

    public function __construct()
    {
        global $log;

        $this->log = $log;
        $this->db = PearDatabase::getInstance();
        $this->name = $this->moduleName;
        $this->id = getTabId($this->moduleName);
    }

    public function vtlib_handler($moduleName, $eventType)
    {
        PDFMaker_Install_Model::getInstance($eventType, $moduleName)->install();
    }

    public function addCustomLinks()
    {
        $this->updateCustomLinks();
    }

    public function updateCustomLinks($register = true)
    {
        foreach ($this->registerCustomLinks as $customLink) {
            list($moduleName, $type, $label, $url, $icon, $sequence, $handler) = array_pad($customLink, 7, null);
            $module = Vtiger_Module::getInstance($moduleName);
            $url = str_replace('$LAYOUT$', Vtiger_Viewer::getDefaultLayoutName(), $url);

            if ($module) {
                $module->deleteLink($type, $label);

                if ($register) {
                    $module->addLink($type, $label, $url, $icon, $sequence, $handler);
                }
            }
        }
    }
    public function deleteCustomLinks()
    {
        $this->updateCustomLinks(false);
    }

    public function postUpdate()
    {
        $this->db->pquery('DELETE FROM vtiger_links WHERE linklabel = ? AND linktype = ? AND linkurl = ?', array('PDFMakerJS', 'HEADERSCRIPT', 'modules/PDFMaker/PDFMakerActions.js'));
        $this->db->pquery('DELETE FROM vtiger_links WHERE linklabel = ? AND linktype = ? AND linkurl LIKE ?', array('PDF Export', 'LISTVIEWBASIC', '%getPDFListViewPopup2%'));
        $this->db->pquery('DELETE FROM vtiger_links  WHERE linklabel = ? AND linktype = ? AND linkurl = ?', array('PDFMaker', 'DETAILVIEWWIDGET', 'module=PDFMaker&action=PDFMakerAjax&file=getPDFActions&record=$RECORD$'));

        $res = $this->db->pquery("SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=(SELECT tabid FROM vtiger_tab WHERE name = ?)", array('PDFMaker'));
        if ($this->db->num_rows($res) > 0) {
            $res = $this->db->pquery("SELECT * FROM vtiger_pdfmaker_profilespermissions", array());
            if ($this->db->num_rows($res) == 0) {
                $this->db->pquery("INSERT INTO vtiger_pdfmaker_profilespermissions SELECT profileid, operation, permissions FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)", array('PDFMaker'));
            }
            $this->db->pquery("DELETE FROM vtiger_profile2standardpermissions WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)", array('PDFMaker'));
        }

        $this->deleteAllRefLinks();
    }

    public function deleteAllRefLinks()
    {
        require_once('vtlib/Vtiger/Link.php');

        $link_res = $this->db->pquery("SELECT tabid FROM vtiger_tab WHERE isentitytype = ?", array('1'));

        while ($link_row = $this->db->fetchByAssoc($link_res)) {
            Vtiger_Link::deleteLink($link_row["tabid"], "DETAILVIEWWIDGET", "PDFMaker");
            Vtiger_Link::deleteLink($link_row["tabid"], "DETAILVIEWSIDEBARWIDGET", "PDFMaker");
            Vtiger_Link::deleteLink($link_row["tabid"], "LISTVIEWMASSACTION", "PDF Export", 'javascript:getPDFListViewPopup2(this,\'$MODULE$\');');
            Vtiger_Link::deleteLink($link_row["tabid"], "LISTVIEWMASSACTION", "PDF Export", "javascript:PDFMaker_Actions_Js.getPDFListViewPopup2(this,'$" . "MODULE$');");
        }
    }

    public function removeLinks()
    {
        require_once('vtlib/Vtiger/Link.php');

        Vtiger_Link::deleteAll(getTabId('PDFMaker'));

        $this->deleteAllRefLinks();
    }
}