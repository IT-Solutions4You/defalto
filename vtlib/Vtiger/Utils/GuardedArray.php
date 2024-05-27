<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_GuardedArray implements \ArrayAccess
{
    private $data;

    function __construct($data = null)
    {
        $this->data = is_null($data) || $data === false ? [] : $data;
    }

    function offsetExists($key)
    {
        return isset($this->data[$key]) && array_key_exists($key, $this->data);
    }

    function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->data[$key];
        }

        return null;
    }

    function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}