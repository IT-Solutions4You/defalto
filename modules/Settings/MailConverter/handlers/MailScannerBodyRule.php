<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

class Vtiger_MailScannerBodyRule {
    
    var $scannerId = false;
    var $ruleId = false;
    var $module = false;
    var $delimiter = false;
    var $mappingData = false;
    var $subject = false;
    var $fromemail = false;
    var $fromname = false;
    var $body = false;

    function __construct($scannerId, $ruleId) {
        $this->initialize($scannerId, $ruleId);
    }
    
    function initialize($scannerId, $ruleId) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT * FROM vtiger_mailscanner_bodyrule WHERE scannerid = ? AND ruleid = ?", array($scannerId, $ruleId));
        if($db->num_rows($result)) {
            $this->scannerId = $scannerId;
            $this->ruleId = $ruleId;
            $this->delimiter = decode_html(decode_html($db->query_result($result, 0, 'delimiter')));
            $this->module = $db->query_result($result, 0, 'module');
        }
        $result = $db->pquery("SELECT * FROM vtiger_mailscanner_mapping WHERE scannerid = ? AND ruleid = ?", array($scannerId, $ruleId));
        $count = $db->num_rows($result);
        $fieldMapping = array();
        for($i = 0; $i < $count; $i++) {
            $crmField = decode_html($db->query_result($result, $i, 'crm_field'));
            $bodyField = decode_html($db->query_result($result, $i, 'body_field'));
            $fieldMapping[$bodyField] = $crmField;
        }
        $this->mappingData = $fieldMapping;
    }
    
    function getFieldValues($body) {
        $bodyFields = $this->parseBody($body);
        return $this->apply($bodyFields);
    }
    
    function parseBody($body) {
        $body = decode_html($body);
        $this->body = $body;
        $bodyFields = array();
        $rows = explode("\n", $body);
        foreach($rows as $row) {
            if(strrpos($row, $this->delimiter)) {
                $columns = explode($this->delimiter, $row);
                $label = trim(decode_html($columns[0]));
                unset($columns[0]);
                $bodyFields[strtolower($label)] = trim(decode_html(implode($this->delimiter, $columns)));
            }
        }
        return $bodyFields;
    }
    
    function apply($bodyFields) {
        $data = array();
        foreach($this->mappingData as $bodyField => $crmField) {
            $bodyField = strtolower($bodyField);
            if(!empty($bodyFields[$bodyField])) {
                $data[$crmField] = $bodyFields[$bodyField];
            }
            if($bodyField == 'subject') {
                $data[$crmField] = $this->subject;
            }
            if($bodyField == 'from email') {
                $data[$crmField] = $this->fromemail;
            }
            if($bodyField == 'from name') {
                $data[$crmField] = $this->fromname;
            }
            if($bodyField == 'email content') {
                $data[$crmField] = $this->body;
            }
        }
        return $this->transformData($data);
    }
    
    function transformData($fields) {
        $moduleInstance = Vtiger_Module_Model::getInstance($this->module);
        foreach($fields as $fieldName => $value) {
            $fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);
            if(!$fieldInstance) {
                unset($fields[$fieldName]);
                continue;
            }
            $fieldInfo = $fieldInstance->getFieldInfo();
            
            switch ($fieldInfo['type']) {
                case 'date'          : $fields[$fieldName] = date('Y-m-d', strtotime($value));
                                       break;
                case 'time'          : $fields[$fieldName] = date('H:i:s', strtotime($value));
                                       break;
                case 'currency'      : if(!is_numeric($value))
                                            unset($fields[$fieldName]);
                                       break;
                case 'double'        : if(!is_numeric($value))
                                            unset($fields[$fieldName]);
                                       break;
                case 'integer'       : if(!is_numeric($value))
                                            unset($fields[$fieldName]);
                                       else
                                           $fields[$fieldName] = round($value);
                                       break;
                case 'percentage'    : if(!is_numeric($value) || $value > 100)
                                            unset($fields[$fieldName]);
                                       break;
                case 'email'         : if(!preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $value)) {
                                            unset($fields[$fieldName]);
                                       }
                                       break;
                case 'picklist'      : $picklistValues = $fieldInfo['picklistvalues'];
                                       $picklistValue = $this->getPicklistValue($value, $picklistValues);
                                       if($picklistValue)
                                            $fields[$fieldName] = $picklistValue;
                                       else
                                           unset($fields[$fieldName]);
                                       break;
                case 'multipicklist' : $picklistValues = $fieldInfo['picklistvalues'];
                                       $bodyValues = explode(',', $value);
                                       foreach($bodyValues as $key => $bodyValue) {
                                           $picklistValue = $this->getPicklistValue($bodyValue, $picklistValues);
                                           if($picklistValue)
                                                $bodyValues[$key] = $picklistValue;
                                           else
                                                unset($bodyValues[$key]);
                                       }
                                       $fields[$fieldName] = implode(' |##| ', $bodyValues);
                                       break;
                case 'reference'     : unset($fields[$fieldName]);
                                       break;
                default              : break;
            }
        }
        return $fields;
    }
    
    function getPicklistValue($value, $allValues) {
        $result = false;
        $value = trim($value);
        foreach($allValues as $option) {
            if(strtolower($value) == strtolower($option)) {
                $result = $option;
                break;
            }
        }
        return $result;
    }
    
    public static function hasBodyRule($scannerId, $ruleId) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery("SELECT 1 FROM vtiger_mailscanner_bodyrule WHERE scannerid = ? AND ruleid = ?", array($scannerId, $ruleId));
        if($db->num_rows($result) > 0) {
            return true;
        }
        return false;
    }
}