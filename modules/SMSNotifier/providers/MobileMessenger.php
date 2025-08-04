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

include_once 'vtlib/Vtiger/Net/Client.php';

class SMSNotifier_MobileMessenger_Provider implements SMSNotifier_ISMSProvider_Model
{
    private $_username;
    private $_password;
    private $_parameters = [];

    const SERVICE_URI = 'http://www.dmssms.com/';

    private static $REQUIRED_PARAMETERS = ['app_id'];

    function __construct()
    {
    }

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'MobileMessenger';
    }

    public function setAuthParameters($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    public function setParameter($key, $value)
    {
        $this->_parameters[$key] = $value;
    }

    public function getParameter($key, $defvalue = false)
    {
        if (isset($this->_parameters[$key])) {
            return $this->_parameters[$key];
        }

        return $defvalue;
    }

    public function getRequiredParams()
    {
        return self::$REQUIRED_PARAMETERS;
    }

    public function getServiceURL($type = false)
    {
        if ($type) {
            switch (strtoupper($type)) {
                case self::SERVICE_AUTH:
                    return self::SERVICE_URI . '/http/auth';
                //case self::SERVICE_SEND: return  self::SERVICE_URI . '/http/sendmsg';
                case self::SERVICE_SEND:
                    return self::SERVICE_URI . '/servlet/HttpSMS';
                case self::SERVICE_QUERY:
                    return self::SERVICE_URI . '/http/querymsg';
            }
        }

        return false;
    }

    protected function prepareParameters()
    {
        $params = ['USERNAME' => $this->_username, 'PASSWORD' => $this->_password];
        foreach (self::$REQUIRED_PARAMETERS as $key) {
            $params[$key] = $this->getParameter($key);
        }

        return $params;
    }

    public function send($message, $tonumbers)
    {
        if (!is_array($tonumbers)) {
            $tonumbers = [$tonumbers];
        }
        $params = $this->prepareParameters();
        $params['MESSAGE_TEXT'] = $message;
        $results = [];
        foreach ($tonumbers as $tonumber) {
            if (!$this->sms($params, $tonumber)) {
                $result['error'] = true;
                $result['to'] = $tonumber;
                $result['statusmessage'] = $params["error"]; // Complete error message
            } else {
                $result['id'] = trim($matches[1]);
                $result['to'] = $tonumber;
                $result['status'] = self::MSG_STATUS_PROCESSING;
            }
            $results[] = $result;
            $sent = false;
        }

        return $results;
    }

    /** ************************************************************************
     * * name: sms
     * * description: SMSes the lead/message posted from the 'work_description'
     * * field to the mobile number stored in user_info.phone_1
     * ************************************************************************* */

    public function sms(&$d, $mobile)
    {
        $request = "USER_NAME=" . $this->_username;
        $request .= "&PASSWORD=" . $this->_password;
        $request .= "&RECIPIENT=" . $mobile;
        $request .= "&MESSAGE_TEXT=" . urlencode(stripslashes($d["MESSAGE_TEXT"]));
        $ch = curl_init("http://www.dmssms.com/servlet/HttpSMS");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        for ($i = 0; $i < 3; $i++) {
            $response = curl_exec($ch);
            if (!($error = curl_error($ch))) {
                break;
            }
        }
        curl_close($ch);

        if ($error) {
            $d["error"] .= $error . "<BR>";
            $sent = false;
        } elseif ($response != 10) {
            if ($response == 1) {
                $response .= " : Authentication Failure. Gateway username and/or password incorrect.";
            } elseif ($response == 2) {
                $response .= " : The recipient number specified was invalid.";
            } elseif ($response == 3) {
                $response .= " : General Server Error.";
            } elseif ($response == 4) {
                $response .= " : Insufficient Credit.";
            }
            $d["error"] .= "Error Code: " . $response . "<BR>";
            $sent = false;
        } else {
            $sent = true;
        }

        return $sent;
    }

    public function query($messageid)
    {
        $params = $this->prepareParameters();
        $params['apimsgid'] = $messageid;

        $serviceURL = $this->getServiceURL(self::SERVICE_QUERY);
        $httpClient = new Vtiger_Net_Client($serviceURL);
        $response = $httpClient->doPost($params);

        $response = trim($response);

        $result = ['error' => false, 'needlookup' => 1];

        if (preg_match("/ERR: (.*)/", $response, $matches)) {
            $result['error'] = true;
            $result['needlookup'] = 0;
            $result['statusmessage'] = $matches[0];
        } elseif (preg_match("/ID: ([^ ]+) Status: ([^ ]+)/", $response, $matches)) {
            $result['id'] = trim($matches[1]);
            $status = trim($matches[2]);

            // Capture the status code as message by default.
            $result['statusmessage'] = "CODE: $status";

            if ($status === '1') {
                $result['status'] = self::MSG_STATUS_PROCESSING;
            } elseif ($status === '2') {
                $result['status'] = self::MSG_STATUS_DISPATCHED;
                $result['needlookup'] = 0;
            }
        }

        return $result;
    }
}