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
}