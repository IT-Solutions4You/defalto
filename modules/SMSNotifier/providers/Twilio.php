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

class SMSNotifier_Twilio_Provider implements SMSNotifier_ISMSProvider_Model
{
    private $userName;
    private $password;
    private $parameters = [];

    private $SERVICE_URI = 'https://api.twilio.com/2010-04-01/Accounts/{sid}/SMS/Messages';
    private static $REQUIRED_PARAMETERS = [
        ['name' => 'AccountSID', 'label' => 'Account SID', 'type' => 'text'],
        ['name' => 'AuthToken', 'label' => 'Auth Token', 'type' => 'text'],
        ['name' => 'From', 'label' => 'From', 'type' => 'text']
    ];

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName()
    {
        return 'Twilio';
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams()
    {
        return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get service URL to use for a given type
     *
     * @param <String> $type like SEND, PING, QUERY
     */
    public function getServiceURL($type = false)
    {
        $accountSID = $this->getParameter('AccountSID');
        $this->SERVICE_URI = str_replace('{sid}', $accountSID, $this->SERVICE_URI);
        if ($type) {
            switch (strtoupper($type)) {
                case self::SERVICE_AUTH:
                    return $this->SERVICE_URI . '/http/auth';
                case self::SERVICE_SEND:
                    return $this->SERVICE_URI . '/http/sendmsg';
                case self::SERVICE_QUERY:
                    return $this->SERVICE_URI . '/http/querymsg';
            }
        }

        return $this->SERVICE_URI;
    }

    /**
     * Function to set authentication parameters
     *
     * @param <String> $userName
     * @param <String> $password
     */
    public function setAuthParameters($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * Function to set non-auth parameter.
     *
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     *
     * @param <String> $key
     * @param <String> $defaultValue
     *
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }

        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    protected function prepareParameters()
    {
        foreach (self::$REQUIRED_PARAMETERS as $key => $fieldInfo) {
            $params[$fieldInfo['name']] = $this->getParameter($fieldInfo['name']);
        }

        return $params;
    }

    /**
     * Function to handle SMS Send operation
     *
     * @param <String> $message
     * @param <Mixed>  $toNumbers One or Array of numbers
     */
    public function send($message, $toNumbers)
    {
        if (!is_array($toNumbers)) {
            $toNumbers = [$toNumbers];
        }
        $params = $this->prepareParameters();
        $httpClient = new Vtiger_Net_Client($this->getServiceURL());
        $httpClient->setHeaders(['Authorization' => 'Basic ' . base64_encode($params['AccountSID'] . ':' . $params['AuthToken'])]);

        foreach ($toNumbers as $toNumber) {
            $xmlResponse = $httpClient->doPost(['From' => $params['From'], 'To' => $toNumber, 'Body' => $message]);

            $xmlObject = simplexml_load_string($xmlResponse);
            $result = [];
            if ($xmlObject->SMSMessage) {
                $result['id'] = (string)$xmlObject->SMSMessage->Sid;
                $status = (string)$xmlObject->SMSMessage->Status;
                $result['status'] = (string)$xmlObject->SMSMessage->Status;
                $result['to'] = (string)$xmlObject->SMSMessage->To;

                switch ($status) {
                    case 'queued'        :
                    case 'sending'        :
                        $status = self::MSG_STATUS_PROCESSING;
                        break;
                    case 'sent'            :
                        $status = self::MSG_STATUS_DISPATCHED;
                        break;
                    case 'delivered'    :
                        $status = self::MSG_STATUS_DELIVERED;
                        break;
                    case 'undelivered'    :
                    case 'failed'        :
                        $status = self::MSG_STATUS_FAILED;
                        break;
                }
                $results[] = $result;
            } else {
                $result['error'] = true;
                $result['statusmessage'] = (string)$xmlObject->RestException->Message;
                $result['to'] = $toNumber;
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Function to get query for status using messgae id
     *
     * @param <Number> $messageId
     */
    public function query($messageId)
    {
        $params = $this->prepareParameters();
        $params['Sid'] = $messageId;

        $params = $this->prepareParameters();
        $httpClient = new Vtiger_Net_Client($this->getServiceURL() . '/' . $messageId);
        $httpClient->setHeaders(['Authorization' => 'Basic ' . base64_encode($params['AccountSID'] . ':' . $params['AuthToken'])]);

        $xmlResponse = $httpClient->doGet([]);
        $xmlObject = simplexml_load_string($xmlResponse);

        $result = [];
        $result['error'] = false;
        $status = (string)$xmlObject->Message->Status;

        switch ($status) {
            case 'queued'        :
            case 'sending'        :
                $status = self::MSG_STATUS_PROCESSING;
                $result['needlookup'] = 1;
                break;

            case 'sent'            :
                $status = self::MSG_STATUS_DISPATCHED;
                $result['needlookup'] = 1;
                break;

            case 'delivered'    :
                $status = self::MSG_STATUS_DELIVERED;
                $result['needlookup'] = 0;
                break;

            case 'undelivered'    :
            case 'failed'        :
            default                :
                $status = self::MSG_STATUS_FAILED;
                $result['needlookup'] = 1;
                break;
        }

        $result['status'] = $status;
        $result['statusmessage'] = $status;

        return $result;
    }

    function getProviderEditFieldTemplateName()
    {
        return 'Twilio.tpl';
    }
}