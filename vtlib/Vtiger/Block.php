<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once('vtlib/Vtiger/Utils.php');
require_once 'includes/runtime/Cache.php';

/**
 * Provides API to work with vtiger CRM Module Blocks
 * @package vtlib
 */
class Vtiger_Block
{
    /** ID of this block instance */
    var $id;
    /** Label for this block instance */
    var $label;

    var $sequence;
    var $showtitle = 0;
    var $visible = 0;
    var $increateview = 0;
    var $ineditview = 0;
    var $indetailview = 0;

    var $display_status = 1;
    var $iscustom = 0;

    public $blockuitype = 1;

    var $module;

    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Get unquie id for this instance
     * @access private
     */
    function __getUniqueId()
    {
        global $adb;

        /** Sequence table was added from 5.1.0 */
        $maxblockid = $adb->getUniqueID('vtiger_blocks');

        return $maxblockid;
    }

    /**
     * Get next sequence value to use for this block instance
     * @access private
     */
    function __getNextSequence()
    {
        global $adb;
        $result = $adb->pquery("SELECT MAX(sequence) as max_sequence from vtiger_blocks where tabid = ?", [$this->module->id]);
        $maxseq = 0;
        if ($adb->num_rows($result)) {
            $maxseq = $adb->query_result($result, 0, 'max_sequence');
        }

        return ++$maxseq;
    }

    /**
     * Initialize this block instance
     *
     * @param Array Map of column name and value
     * @param Vtiger_Module Instance of module to which this block is associated
     *
     * @access private
     */
    function initialize($valuemap, $moduleInstance = false)
    {
        $this->id = isset($valuemap['blockid']) ? $valuemap['blockid'] : null;
        $this->label = isset($valuemap['blocklabel']) ? $valuemap['blocklabel'] : null;
        $this->display_status = isset($valuemap['display_status']) ? $valuemap['display_status'] : null;
        $this->sequence = isset($valuemap['sequence']) ? $valuemap['sequence'] : null;
        $this->iscustom = isset($valuemap['iscustom']) ? $valuemap['iscustom'] : null;
        $this->blockuitype = isset($valuemap['blockuitype']) ? $valuemap['blockuitype'] : 1;
        $tabid = isset($valuemap['tabid']) ? $valuemap['tabid'] : null;
        $this->module = $moduleInstance ? $moduleInstance : Vtiger_Module::getInstance($tabid);
    }

    /**
     * Create vtiger CRM block
     * @access private
     */
    public function __create($moduleInstance)
    {
        $this->module = $moduleInstance;
        $this->id = $this->__getUniqueId();

        if (!$this->sequence) {
            $this->sequence = $this->__getNextSequence();
        }

        $this->getBlockTable()->insertData([
            'blockid'     => $this->id,
            'tabid'       => $this->module->id,
            'blocklabel'  => $this->label,
            'sequence'    => $this->sequence,
            'show_title'  => $this->showtitle,
            'visible'     => $this->visible,
            'create_view' => $this->increateview,
            'edit_view'   => $this->ineditview,
            'detail_view' => $this->indetailview,
            'iscustom'    => $this->iscustom,
            'blockuitype' => $this->blockuitype,
        ]);

        self::log("Creating Block $this->label ... DONE");
        self::log("Module language entry for $this->label ... CHECK");
    }

    /**
     * Update vtiger CRM block
     * @access   private
     * @internal TODO
     */
    function __update()
    {
        self::log("Updating Block $this->label ... DONE");
    }

    /**
     * Delete this instance
     * @access private
     */
    function __delete()
    {
        global $adb;
        self::log("Deleting Block $this->label ... ", false);
        $adb->pquery("DELETE FROM vtiger_blocks WHERE blockid=?", [$this->id]);
        self::log("DONE");
    }

    /**
     * Save this block instance
     *
     * @param Vtiger_Module Instance of the module to which this block is associated
     */
    function save($moduleInstance = false)
    {
        if ($this->id) {
            $this->__update();
        } else {
            $this->__create($moduleInstance);
        }

        return $this->id;
    }

    /**
     * Delete block instance
     *
     * @param Boolean True to delete associated fields, False to avoid it
     */
    function delete($recursive = true)
    {
        if ($recursive) {
            $fields = Vtiger_Field::getAllForBlock($this);
            foreach ($fields as $fieldInstance) {
                $fieldInstance->delete($recursive);
            }
        }
        $this->__delete();
    }

    /**
     * Add field to this block
     *
     * @param Vtiger_Field Instance of field to add to this block.
     *
     * @return Reference to this block instance
     */
    function addField($fieldInstance)
    {
        $fieldInstance->save($this);

        return $this;
    }

    /**
     * Helper function to log messages
     *
     * @param String Message to log
     * @param Boolean true appends linebreak, false to avoid it
     *
     * @access private
     */
    static function log($message, $delim = true)
    {
        Vtiger_Utils::Log($message, $delim);
    }

    /**
     * Get instance of block
     *
     * @param mixed block id or block label
     * @param Vtiger_Module Instance of the module if block label is passed
     */
    static function getInstance($value, $moduleInstance = false)
    {
        global $adb;
        $instance = false;

        if (Vtiger_Utils::isNumber($value)) {
            $query = "SELECT * FROM vtiger_blocks WHERE blockid=?";
            $queryParams = [$value];
        } else {
            $query = "SELECT * FROM vtiger_blocks WHERE blocklabel=? AND tabid=?";
            $queryParams = [$value, $moduleInstance->id];
        }

        $result = $adb->pquery($query, $queryParams);
        if ($adb->num_rows($result)) {
            $instance = new self();
            $instance->initialize($adb->fetch_array($result), $moduleInstance);
        }

        return $instance;
    }

    /**
     * Get all block instances associated with the module
     *
     * @param Vtiger_Module Instance of the module
     */
    static function getAllForModule($moduleInstance)
    {
        global $adb;
        $instances = [];

        $query = "SELECT * FROM vtiger_blocks WHERE tabid=? ORDER BY sequence";
        $queryParams = [$moduleInstance->id];

        $result = $adb->pquery($query, $queryParams);
        for ($index = 0; $index < $adb->num_rows($result); ++$index) {
            $instance = new self();
            $instance->initialize($adb->fetch_array($result), $moduleInstance);
            $instances[] = $instance;
        }

        return $instances;
    }

    /**
     * Delete all blocks associated with module
     *
     * @param Vtiger_Module Instnace of module to use
     * @param Boolean true to delete associated fields, false otherwise
     *
     * @access private
     */
    static function deleteForModule($moduleInstance, $recursive = true)
    {
        global $adb;
        if ($recursive) {
            Vtiger_Field::deleteForModule($moduleInstance);
        }
        $adb->pquery("DELETE FROM vtiger_blocks WHERE tabid=?", [$moduleInstance->id]);
        self::log("Deleting blocks for module ... DONE");
    }

    /**
     * Changes the block ui type for the block in both actual object instance and database
     *
     * @param int $blockUiType
     *
     * @return void
     */
    public function changeBlockUiType(int $blockUiType): void
    {
        $this->blockuitype = $blockUiType;
        $db = PearDatabase::getInstance();
        $db->pquery('UPDATE vtiger_blocks SET blockuitype = ? WHERE blockid = ?', [$blockUiType, $this->id]);
    }

    public function getBlockTable()
    {
        return (new Core_DatabaseData_Model())->getTable('vtiger_blocks', 'blockid');
    }

    /**
     * @throws Exception
     */
    public function createTables(): void
    {
        $this->getBlockTable()
            ->createTable('blockid')
            ->createColumn('tabid', 'int(19) NOT NULL')
            ->createColumn('blocklabel', 'varchar(100) NOT NULL')
            ->createColumn('sequence', 'int(10) DEFAULT NULL')
            ->createColumn('show_title', 'int(2) DEFAULT NULL')
            ->createColumn('visible', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('create_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('edit_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('detail_view', 'int(2) NOT NULL DEFAULT "0"')
            ->createColumn('display_status', 'int(1) NOT NULL DEFAULT 1')
            ->createColumn('iscustom', 'int(1) NOT NULL DEFAULT "0"')
            ->createColumn('blockuitype', 'int(11) NOT NULL DEFAULT 1')
            ->createKey('PRIMARY KEY IF NOT EXISTS (blockid)')
            ->createKey('KEY IF NOT EXISTS block_tabid_idx (tabid)')
            ->createKey('CONSTRAINT fk_1_vtiger_blocks FOREIGN KEY IF NOT EXISTS (tabid) REFERENCES vtiger_tab (tabid) ON DELETE CASCADE');
    }
}