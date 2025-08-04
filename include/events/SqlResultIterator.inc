<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class SqlResultIterator implements Iterator
{
    protected $result;
    protected PearDatabase $adb;
    protected int $pos;

    function __construct($adb, $result)
    {
        $this->result = $result;
        $this->adb = $adb;
        $this->pos = 0;
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    function valid(): bool
    {
        $adb = $this->adb;

        return $this->pos < $adb->num_rows($this->result);
    }

    public function next(): void
    {
        $this->pos += 1;
    }

    public function current(): mixed
    {
        $adb = $this->adb;
        $data = $adb->raw_query_result_rowdata($this->result, $this->pos);

        return new SqlResultIteratorRow($data);
    }

    public function key(): mixed
    {
        return $this->pos;
    }

    /**
     * Return the contents of the resultset as an array. Destroys a running iterator's state.
     *
     * This method will reset the iterator using the rewind method.
     *
     * $copyFields specify which fields of the result to copy to the array.
     * If not specified the function will return values for all the fields.
     */
    function toArray($copyFields = null)
    {
        $adb = $this->adb;
        $this->rewind();

        if ($copyFields === null) {
            $columnData = $adb->getFieldsDefinition($this->result);
            $columnNames = [];
            foreach ($columnData as $column) {
                $columnNames[] = $column->name;
            }
            $copyFields = $columnNames;
        }

        $arr = [];
        foreach ($this as $row) {
            $rowArr = [];
            foreach ($copyFields as $name) {
                $rowArr[$name] = $row->$name;
            }
            $arr[] = $rowArr;
        }

        return $arr;
        $this->rewind();
    }
}

class SqlResultIteratorRow
{

    public $data;

    function __construct($data)
    {
        $this->data = $data;
    }

    function get($column)
    {
        return $this->data[$column];
    }

    function __get($column)
    {
        return $this->get($column);
    }
}