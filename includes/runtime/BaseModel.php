<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/**
 * Base Model Class
 */
class Vtiger_Base_Model {
	protected $valueMap;

	/**
	 * Constructor
	 * @param Array $values
	 */
	function  __construct($values=array()) {
		$this->valueMap = $values;
	}

	/**
	 * Function to get the value for a given key
	 * @param $key
	 * @return mixed for the given key
	 */
	public function get($key){
		return isset($this->valueMap[$key]) ? $this->valueMap[$key] : null;
	}

	/**
	* Function to get the raw value for a given key
	* @param $key
	* @return Raw Value for the given key
	*/
	public function getRaw($key){
		return $this->rawData[$key];
	}

	/**
	 * Function to get the value if its safe to use for SQL Query (column).
	 * @param <String> $key
	 * @param <Boolean> $skipEmpty - Skip the check if string is empty
	 * @return string Value for the given key
	 */
	public function getForSql($key, $skipEmtpy=true) {
		return Vtiger_Util_Helper::validateStringForSql($this->get($key), $skipEmtpy);
	}

	/**
	 * Function to set the value for a given key
	 * @param $key
	 * @param $value
	 * @return Vtiger_Base_Model
	 */
	public function set($key,$value){
		$this->valueMap[$key] = $value;
		return $this;
	}

	/**
	 * Function to set all the values for the Object
	 * @param Array (key-value mapping) $values
	 * @return Vtiger_Base_Model
	 */
	public function setData($values){
		$this->valueMap = $values;
		return $this;
	}

	/**
	 * Function to get all the values of the Object
	 * @return Array (key-value mapping)
	 */
	public function getData(){
		return $this->valueMap;
	}

	/**
	 * Function to check if the key exists.
	 * @param String $key
	 */
	public function has($key) {
		return array_key_exists($key, (array)$this->valueMap); // valueMap can be array or TrackableObject
	}

	/**
	 * Function to check if the key is empty.
	 * @param string $key
	 */
	public function isEmpty($key) {
		return (!isset($this->valueMap[$key]) || empty($this->valueMap[$key]));
	}

}