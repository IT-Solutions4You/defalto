<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/**
 * Provides basic API to work with vtiger CRM Fields
 * @package vtlib
 */
class Vtiger_FieldBasic {

	/** ID of this field instance */
	var $id;
	var $name;
	var $label = false;
	var $table = false;
	var $column = false;
	var $columntype = false;
	var $helpinfo = '';
	var $summaryfield = 0;
	var $masseditable = 1; // Default: Enable massedit for field

	var $uitype = 1;
	var $typeofdata = 'V~O';
	var	$displaytype   = 1;
	var $generatedtype = 1;
	var	$readonly      = 1;
	var	$presence      = 2;
	var	$defaultvalue  = '';
	var	$maximumlength = 100;
	var	$sequence      = false;
	var	$quickcreate   = 1;
	var	$quicksequence = false;
	var	$info_type     = 'BAS';
	var $isunique = false;
	var $block;
	var $headerfield = 0;
    public $related_modules;
    public $quickcreatesequence;
    public $headerfieldsequence;

	/**
	 * Constructor
	 */
	function __construct() {
	}

	/**
	 * Initialize this instance
	 * @param Array 
	 * @param Vtiger_Module Instance of module to which this field belongs
	 * @param Vtiger_Block Instance of block to which this field belongs
	 * @access private
	 */
	function initialize($valuemap, $moduleInstance=false, $blockInstance=false) {
		$valuemap = vtlib_array($valuemap);
		$this->id = $valuemap['fieldid'];
		$this->name = $valuemap['fieldname'];
		$this->label= $valuemap['fieldlabel'];
		$this->column = $valuemap['columnname'];
		$this->table  = $valuemap['tablename'];
		$this->uitype = $valuemap['uitype'];
		$this->typeofdata = $valuemap['typeofdata'];
		$this->helpinfo = $valuemap['helpinfo'];
		$this->masseditable = $valuemap['masseditable'];
		$this->displaytype   = $valuemap['displaytype'];
		$this->generatedtype = $valuemap['generatedtype'];
		$this->readonly      = $valuemap['readonly'];
		$this->presence      = $valuemap['presence'];
		$this->defaultvalue  = $valuemap['defaultvalue'];
		$this->quickcreate = $valuemap['quickcreate'];
		$this->quicksequence = $valuemap['quickcreatesequence'];
		$this->sequence = $valuemap['sequence'];
		$this->summaryfield = $valuemap['summaryfield'];
		$this->isunique = $valuemap['isunique'];
		$this->block= $blockInstance? $blockInstance : Vtiger_Block::getInstance($valuemap['block'], $moduleInstance);
		$this->headerfield = $valuemap['headerfield'];
		$this->headerfieldsequence = $valuemap['headerfieldsequence'];
	}

	/** Cache (Record) the schema changes to improve performance */
	static $__cacheSchemaChanges = Array();

	/**
	 * Initialize vtiger schema changes.
	 * @access private
	 */
	function __handleVtigerCoreSchemaChanges() {
		// Add helpinfo column to the vtiger_field table
		if (empty(self::$__cacheSchemaChanges['vtiger_field.helpinfo'])) {
			Vtiger_Utils::AddColumn('vtiger_field', 'helpinfo', ' TEXT');
			self::$__cacheSchemaChanges['vtiger_field.helpinfo'] = true;
		}
		if (empty(self::$__cacheSchemaChanges['vtiger_field.summaryfield'])) {
			Vtiger_Utils::AddColumn('vtiger_field', 'summaryfield', ' INT(10) NOT NULL DEFAULT 0');
			self::$__cacheSchemaChanges['vtiger_field.summaryfield'] = 0;
		}
		if (empty(self::$__cacheSchemaChanges['vtiger_field.headerfield'])) {
			Vtiger_Utils::AddColumn('vtiger_field', 'headerfield', ' INT(1) DEFAULT 0');
			self::$__cacheSchemaChanges['vtiger_field.headerfield'] = 0;
		}
	}

	/**
	 * Get unique id for this instance
	 * @access private
	 */
	function __getUniqueId() {
		global $adb;
		return $adb->getUniqueID('vtiger_field');
	}

	/**
	 * Get next sequence id to use within a block for this instance
	 * @access private
	 */
	function __getNextSequence() {
		global $adb;
		$result = $adb->pquery("SELECT MAX(sequence) AS max_seq FROM vtiger_field WHERE tabid=? AND block=?", Array($this->getModuleId(), $this->getBlockId()));
		$maxseq = 0;
		if ($result && $adb->num_rows($result)) {
			$maxseq = $adb->query_result($result, 0, 'max_seq');
			$maxseq += 1;
		}
		return $maxseq;
	}

	/**
	 * Get next quick create sequence id for this instance
	 * @access private
	 */
	function __getNextQuickCreateSequence() {
		global $adb;
		$result = $adb->pquery("SELECT MAX(quickcreatesequence) AS max_quickcreateseq FROM vtiger_field WHERE tabid=?", Array($this->getModuleId()));
		$max_quickcreateseq = 0;
		if ($result && $adb->num_rows($result)) {
			$max_quickcreateseq = $adb->query_result($result, 0, 'max_quickcreateseq');
			$max_quickcreateseq += 1;
		}
		return $max_quickcreateseq;
	}

    /**
     * Create this field instance
     * @param Vtiger_Block Instance of the block to use
     * @access private
     * @throws AppException
     */
	function __create($blockInstance) {
		$this->__handleVtigerCoreSchemaChanges();

		global $adb;

		$this->block = $blockInstance;

		$moduleInstance = $this->getModuleInstance();

		$this->id = $this->__getUniqueId();

		if (!$this->sequence) {
			$this->sequence = $this->__getNextSequence();
		}

		if ($this->quickcreate != 1) { // If enabled for display
			if (!$this->quicksequence) {
				$this->quicksequence = $this->__getNextQuickCreateSequence();
			}
		} else {
			$this->quicksequence = null;
		}

		// Initialize other variables which are not done
		if (!$this->table)
			$this->table = $moduleInstance->basetable;
		if (!$this->column) {
			$this->column = strtolower($this->name);
		}
		if (!$this->columntype)
			$this->columntype = 'VARCHAR(100)';

        if (!$this->label)
            $this->label = $this->name;

		if (!empty($this->columntype)) {
			Vtiger_Utils::AddColumn($this->table, $this->column, $this->columntype);

			if (71 == $this->uitype) {
				$entityTableResult = $adb->pquery('SELECT tablename FROM vtiger_entityname WHERE tabid = ?', [$this->getModuleId()]);

				if ($entityTableResult) {
					$entityTableRow = $adb->fetchByAssoc($entityTableResult);
					$entityTable = $entityTableRow['tablename'];

					Vtiger_Utils::AddColumn($entityTable, 'currency_id', 'INT(19)');
					Vtiger_Utils::AddColumn($entityTable, 'conversion_rate', 'DECIMAL(10,3)');
				}
			}
		}

		if (!$this->label) {
			$this->label = $this->name;
		}

        $params = [
            'tabid' => $this->getModuleId(),
            'fieldid' => $this->id,
            'columnname' => $this->column,
            'tablename' => $this->table,
            'generatedtype' => intval($this->generatedtype),
            'uitype' => $this->uitype,
            'fieldname' => $this->name,
            'fieldlabel' => $this->label,
            'readonly' => $this->readonly,
            'presence' => $this->presence,
            'defaultvalue' => $this->defaultvalue,
            'maximumlength' => $this->maximumlength,
            'sequence' => $this->sequence,
            'block' => $this->getBlockId(),
            'displaytype' => $this->displaytype,
            'typeofdata' => $this->typeofdata,
            'quickcreate' => intval($this->quickcreate),
            'quickcreatesequence' => intval($this->quicksequence),
            'info_type' => $this->info_type,
            'helpinfo' => $this->helpinfo,
            'summaryfield' => intval($this->summaryfield),
            'headerfield' => $this->headerfield,
            'headerfieldsequence' => $this->headerfieldsequence,
            'masseditable' => $this->masseditable,
        ];
        $this->getFieldTable()->insertData($params);

		Vtiger_Profile::initForField($this);

		self::log("Creating Field $this->name ... DONE");
		self::log("Module language mapping for $this->label ... CHECK");
	}

    /**
     * @return Core_DatabaseData_Model
     */
    public function getFieldTable(): Core_DatabaseData_Model
    {
        return (new Core_DatabaseData_Model())->getTable('vtiger_field', 'fieldid');
    }

    /**
     * @return void
     * @throws AppException
     */
    public function createTables(): void
    {
        $this->getFieldTable()
            ->createTable()
            ->createColumn('tabid', 'int(19) NOT NULL')
            ->createColumn('columnname', 'varchar(30) NOT NULL')
            ->createColumn('tablename', 'varchar(100) DEFAULT NULL')
            ->createColumn('generatedtype', 'int(19) NOT NULL DEFAULT 0')
            ->createColumn('uitype', 'varchar(30) NOT NULL')
            ->createColumn('fieldname', 'varchar(50) NOT NULL')
            ->createColumn('fieldlabel', 'varchar(50) NOT NULL')
            ->createColumn('readonly', 'int(1) NOT NULL')
            ->createColumn('presence', 'int(19) NOT NULL DEFAULT 1')
            ->createColumn('defaultvalue', 'text DEFAULT NULL')
            ->createColumn('maximumlength', 'int(19) DEFAULT NULL')
            ->createColumn('sequence', 'int(19) DEFAULT NULL')
            ->createColumn('block', 'int(19) DEFAULT NULL')
            ->createColumn('displaytype', 'int(19) DEFAULT NULL')
            ->createColumn('typeofdata', 'varchar(100) DEFAULT NULL')
            ->createColumn('quickcreate', 'int(10) NOT NULL DEFAULT 1')
            ->createColumn('quickcreatesequence', 'int(19) DEFAULT NULL')
            ->createColumn('info_type', 'varchar(20) DEFAULT NULL')
            ->createColumn('masseditable', 'int(10) NOT NULL DEFAULT 1')
            ->createColumn('helpinfo', 'text DEFAULT NULL')
            ->createColumn('summaryfield', 'int(10) NOT NULL DEFAULT 0')
            ->createColumn('headerfield', 'int(1) DEFAULT 0')
            ->createColumn('headerfieldsequence', 'int(19) DEFAULT 0')
            ->createColumn('isunique', 'tinyint(1) DEFAULT 0')
            ->createKey('PRIMARY KEY IF NOT EXISTS (fieldid)')
            ->createKey('KEY IF NOT EXISTS field_tabid_idx (tabid)')
            ->createKey('KEY IF NOT EXISTS field_fieldname_idx (fieldname)')
            ->createKey('KEY IF NOT EXISTS field_block_idx (block)')
            ->createKey('KEY IF NOT EXISTS field_displaytype_idx (displaytype)')
            ->createKey('CONSTRAINT fk_1_vtiger_field FOREIGN KEY IF NOT EXISTS (tabid) REFERENCES vtiger_tab(tabid) ON DELETE CASCADE');
    }

    /**
	 * Update this field instance
	 * @access private
	 * @internal TODO
	 */
	function __update() {
		self::log("Make use of Vtiger_Field_Model => __update() api.");
	}

	/**
	 * Delete this field instance
	 * @access private
	 */
	function __delete() {
		global $adb;

		Vtiger_Profile::deleteForField($this);

		//TODO : should we check if the field is realtion field or not
		$this->getModuleInstance()->unsetRelatedListForField($this->id);

		$adb->pquery("DELETE FROM vtiger_field WHERE fieldid=?", Array($this->id));

		$em = new VTEventsManager($adb);
		$em->triggerEvent('vtiger.field.afterdelete', $this);

		self::log("Deleteing Field $this->name ... DONE");
	}

	/**
	 * Get block id to which this field instance is associated
	 */
	function getBlockId() {
		return $this->block->id;
	}

	/**
	 * Get module id to which this field instance is associated
	 */
	function getModuleId() {
        return ($this->block && $this->block->module) ? $this->block->module->id : '';
	}

	/**
	 * Get module name to which this field instance is associated
	 */
	function getModuleName() {
		return $this->block && $this->block->module ? $this->block->module->name : "";
	}

	/**
	 * Get module instance to which this field instance is associated
	 */
	function getModuleInstance() {
		return $this->block->module;
	}

	/**
	 * Save this field instance
	 * @param Vtiger_Block Instance of block to which this field should be added.
	 */
	function save($blockInstance = false) {
		if ($this->id)
			$this->__update();
		else
			$this->__create($blockInstance);
		// Clearing cache
		Vtiger_Cache::flushModuleandBlockFieldsCache($this->getModuleInstance(), $this->getBlockId());
		return $this->id;
	}

	/**
	 * Delete this field instance
	 *
	 * @param $checkUsage
	 *
	 * @return void
	 */
	public function delete($checkUsage = false): void
	{
		if ($checkUsage && $this->checkUsage()) {
			return;
		}

		$this->__delete();

		// Clearing cache
		Vtiger_Cache::flushModuleandBlockFieldsCache($this->getModuleInstance(), $this->getBlockId());
	}

	/**
	 * Checks whether the field has been used (only on non-deleted entities). If it was used or if the control can not be executed, returns true.
	 *
	 * @return bool
	 */
	protected function checkUsage(): bool
	{
		$return = true;
		$db = PearDatabase::getInstance();

		if (!$this->table || !$this->name) {
			return true;
		}

		$moduleInstance = $this->getModuleInstance();
		$module = CRMEntity::getInstance($moduleInstance->name);

		$sql = 'SELECT ' . $this->name . '
				FROM ' . $this->table . '
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ' . $this->table . '.' . $module->tab_name_index[$this->table] . ' AND vtiger_crmentity.deleted = 0
				WHERE ' . $this->name . '<>""
					AND ' . $this->name . ' IS NOT NULL';
		$res = $db->query($sql);

		if (!$db->num_rows($res)) {
			$return = false;
		}

		return $return;
	}

	/**
	 * Set Help Information for this instance.
	 * @param String Help text (content)
	 */
	function setHelpInfo($helptext) {
		// Make sure to initialize the core tables first
		$this->__handleVtigerCoreSchemaChanges();

		global $adb;
		$adb->pquery('UPDATE vtiger_field SET helpinfo=? WHERE fieldid=?', Array($helptext, $this->id));
		self::log("Updated help information of $this->name ... DONE");
	}

	/**
	 * Set Masseditable information for this instance.
	 * @param Integer Masseditable value
	 */
	function setMassEditable($value) {
		global $adb;
		$adb->pquery('UPDATE vtiger_field SET masseditable=? WHERE fieldid=?', Array($value, $this->id));
		self::log("Updated masseditable information of $this->name ... DONE");
	}

	/**
     * Set Summaryfield information for this instance. 
     * @param Integer Summaryfield value 
     */ 
    function setSummaryField($value) { 
        global $adb; 
        $adb->pquery('UPDATE vtiger_field SET summaryfield=? WHERE fieldid=?', Array($value, $this->id)); 
        self::log("Updated summaryfield information of $this->name ... DONE"); 
    } 

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true) {
		Vtiger_Utils::Log($message, $delim);
	}
}