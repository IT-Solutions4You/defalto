<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker extends CRMExtension
{
    public $list_fields_name = [];
    public $list_fields = [];
    public $related_tables = [];

    public string $moduleName = 'PDFMaker';
    public string $moduleLabel = 'PDFMaker';
    public string $parentName = 'TOOLS';
    public string $moduleVersion = '0.2';
    /**
     * @var mixed|null
     */
    public $id;

    public function __construct()
    {
        parent::__construct();
        $this->name = $this->moduleName;
        $this->id = getTabId($this->moduleName);
    }

    public function vtlib_handler($moduleName, $eventType)
    {
        PDFMaker_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}