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

class SMSNotifier_MyProvider_Provider implements SMSNotifier_ISMSProvider_Model
{
	private $userName;
	private $password;
	private $parameters = [];

	const SERVICE_URI = 'http://localhost:9898';
	private static $REQUIRED_PARAMETERS = ['app_id'];

	/**
	 * Function to get provider name
	 * @return <String> provider name
	 */
	public function getName()
	{
		return 'MyProvider';
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
		if ($type) {
			switch (strtoupper($type)) {
				case self::SERVICE_AUTH:
					return self::SERVICE_URI . '/http/auth';
				case self::SERVICE_SEND:
					return self::SERVICE_URI . '/http/sendmsg';
				case self::SERVICE_QUERY:
					return self::SERVICE_URI . '/http/querymsg';
			}
		}

		return false;
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
		$params = ['user' => $this->userName, 'pwd' => $this->password];
		foreach (self::$REQUIRED_PARAMETERS as $key) {
			$params[$key] = $this->getParameter($key);
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
		$params['text'] = $message;
		$params['to'] = implode(',', $toNumbers);

		$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
		$httpClient = new Vtiger_Net_Client($serviceURL);
		$response = $httpClient->doPost($params);
		$responseLines = explode("\n", $response);

		$results = [];
		foreach ($responseLines as $responseLine) {
			$responseLine = trim($responseLine);
			if (empty($responseLine)) {
				continue;
			}

			$result = ['error' => false, 'statusmessage' => ''];
			if (preg_match("/ERR:(.*)/", trim($responseLine), $matches)) {
				$result['error'] = true;
				$result['to'] = $toNumbers[$i++];
				$result['statusmessage'] = $matches[0]; // Complete error message
			} elseif (preg_match("/ID: ([^ ]+)TO:(.*)/", $responseLine, $matches)) {
				$result['id'] = trim($matches[1]);
				$result['to'] = trim($matches[2]);
				$result['status'] = self::MSG_STATUS_PROCESSING;
			} elseif (preg_match("/ID: (.*)/", $responseLine, $matches)) {
				$result['id'] = trim($matches[1]);
				$result['to'] = $toNumbers[0];
				$result['status'] = self::MSG_STATUS_PROCESSING;
			}
			$results[] = $result;
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
		$params['apimsgid'] = $messageId;

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