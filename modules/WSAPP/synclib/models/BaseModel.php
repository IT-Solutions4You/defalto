<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class WSAPP_BaseModel
{
	protected $data;

	function __construct($values = [])
	{
		$this->data = $values;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setData($values)
	{
		$this->data = $values;

		return $this;
	}

	public function set($key, $value)
	{
		$this->data[$key] = $value;

		return $this;
	}

	public function get($key)
	{
		return $this->data[$key];
	}

	public function has($key)
	{
		return array_key_exists($key, $this->data);
	}

	/**
	 * Function to check if the key is empty.
	 *
	 * @param type $key
	 */
	public function isEmpty($key)
	{
		return (!isset($this->data[$key]) || empty($this->data[$key]));
	}
}