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

class Vtiger_GuardedArray implements \ArrayAccess
{
    private $data;

    function __construct($data = null)
    {
        $this->data = is_null($data) || $data === false ? [] : $data;
    }

    function offsetExists($key): bool
    {
        return isset($this->data[$key]) && array_key_exists($key, $this->data);
    }

    function offsetGet(mixed $key): mixed
    {
        if ($this->offsetExists($key)) {
            return $this->data[$key];
        }

        return null;
    }

    function offsetSet($key, $value): void
    {
        $this->data[$key] = $value;
    }

    function offsetUnset($key): void
    {
        unset($this->data[$key]);
    }
}