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

class Import_VCardReader_Reader extends Import_FileReader_Reader
{
    protected $vCardPattern = '/BEGIN:VCARD.*?END:VCARD/si';
    protected $skipLabels = ['BEGIN', 'END', 'VERSION'];
    static $fileContents = null;

    public function hasHeader()
    {
        return true;
    }

    public function getFirstRowData($hasHeader = true)
    {
        global $default_charset;

        $filePath = $this->getFilePath();
        if (empty(self::$fileContents)) {
            self::$fileContents = file_get_contents($filePath);
        }
        $fileContents = self::$fileContents;

        $data = null;
        $matches = [];
        preg_match_all($this->vCardPattern, $fileContents, $matches);

        $row = $matches[0][0];
        $fieldValueMappings = explode("\r\n", $row);
        $data = [];
        foreach ($fieldValueMappings as $fieldValueMapping) {
            [$label, $value] = explode(':', $fieldValueMapping, 2);
            $value = str_replace(';', ' ', $value);
            if (!in_array($label, $this->skipLabels)) {
                $data[$label] = $this->convertCharacterEncoding($value, $this->request->get('file_encoding'), $default_charset);
            }
        }

        return $data;
    }

    public function read()
    {
        global $default_charset;

        $filePath = $this->getFilePath();
        $status = $this->createTable();
        if (!$status) {
            return false;
        }

        $fieldMapping = $this->request->get('field_mapping');

        if (empty(self::$fileContents)) {
            self::$fileContents = file_get_contents($filePath);
        }
        $fileContents = self::$fileContents;

        $matches = [];
        preg_match_all($this->vCardPattern, $fileContents, $matches);
        for ($i = 0; $i < php7_count($matches[0]); ++$i) {
            $row = $matches[0][$i];
            $fieldValueMappings = explode("\r\n", $row);
            $data = [];
            $valueCounter = 0;
            foreach ($fieldValueMappings as $fieldValueMapping) {
                [$label, $value] = explode(':', $fieldValueMapping, 2);
                $value = str_replace(';', ' ', $value);
                if (!in_array($label, $this->skipLabels)) {
                    $data[$valueCounter++] = $value;
                }
            }
            $mappedData = [];
            $allValuesEmpty = true;
            foreach ($fieldMapping as $fieldName => $index) {
                $fieldValue = $data[$index];
                $mappedData[$fieldName] = $fieldValue;
                if ($this->request->get('file_encoding') != $default_charset) {
                    $mappedData[$fieldName] = $this->convertCharacterEncoding($fieldValue, $this->request->get('file_encoding'), $default_charset);
                }
                if (!empty($fieldValue)) {
                    $allValuesEmpty = false;
                }
            }
            if ($allValuesEmpty) {
                continue;
            }
            $fieldNames = array_keys($mappedData);
            $fieldValues = array_values($mappedData);
            $this->addRecordToDB($fieldNames, $fieldValues);
        }
    }
}