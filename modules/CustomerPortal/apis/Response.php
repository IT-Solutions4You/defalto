<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomerPortal_API_Response
{
    private $error = null;
    private $result = null;

    function setError($code, $message)
    {
        $error = ['code' => $code, 'message' => $message];
        $this->error = $error;
    }

    function getError()
    {
        return $this->error;
    }

    function hasError()
    {
        return !is_null($this->error);
    }

    function setResult($result)
    {
        $this->result = $result;
    }

    function getResult()
    {
        return $this->result;
    }

    function addToResult($key, $value)
    {
        $this->result[$key] = $value;
    }

    function prepareResponse()
    {
        $response = [];
        if ($this->result === null) {
            $response['success'] = false;
            $response['error'] = $this->error;
        } else {
            $response['success'] = true;
            $response['result'] = $this->result;
        }

        return $response;
    }

    function emitJSON()
    {
        return Zend_Json::encode($this->prepareResponse());
    }

    function emitHTML()
    {
        if ($this->result === null) {
            return (is_string($this->error)) ? $this->error : var_export($this->error, true);
        }

        return $this->result;
    }
}