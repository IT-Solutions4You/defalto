<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker extends CRMExtension
{
    public $log;
    public $db;
    public $moduleModel;
    public $id;
    public $name;
    private $basicModules;
    private $profilesActions;
    private $profilesPermissions;
    public string $moduleName = 'EMAILMaker';
    public string $parentName = 'Tools';
    public $list_fields_name = [];
    public $list_fields = [];
    public $related_tables = [];
    public string $moduleVersion = '1.0';

    public function __construct()
    {
        global $log;

        $this->log = $log;
        $this->db = PearDatabase::getInstance();
        $this->basicModules = ['20', '21', '22', '23'];
        $this->profilesActions = [
            'EDIT'       => 'EditView',
            'DETAIL'     => 'DetailView', // View
            'DELETE'     => 'Delete', // Delete
            'EXPORT_RTF' => 'Export', // Export to RTF
        ];
        $this->profilesPermissions = [];
        $this->name = 'EMAILMaker';
        $this->id = getTabId($this->name);
    }

    public function vtlib_handler($moduleName, $eventType)
    {
        EMAILMaker_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}