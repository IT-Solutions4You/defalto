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
}