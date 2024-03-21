<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker
{
    public $log;
    public $db;
    public $moduleModel;
    public $id;
    public $name;
    private $basicModules;
    private $profilesActions;
    private $profilesPermissions;
    public $moduleName = 'EMAILMaker';
    public $parentName = 'Tools';
    public $list_fields_name  = [];
    public $list_fields = [];
    public $related_tables = [];



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

    public function vtlib_handler($moduleName, $eventType)
    {
        EMAILMaker_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}