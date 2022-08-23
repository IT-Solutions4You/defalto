<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

vimport('~~/modules/WSAPP/synclib/connectors/TargetConnector.php');
require_once 'vtlib/Vtiger/Net/Client.php';

Class Google_Contacts_Connector extends WSAPP_TargetConnector {

	protected $apiConnection;
	protected $totalRecords;
	protected $createdRecords;
	protected $maxResults = 100;

	const CONTACTS_URI = 'https://www.google.com/m8/feeds/contacts/default/full';

	const CONTACTS_GROUP_URI = 'https://www.google.com/m8/feeds/groups/default/full';

	const CONTACTS_BATCH_URI = 'https://www.google.com/m8/feeds/contacts/default/full/batch';

	const USER_PROFILE_INFO = 'https://www.googleapis.com/oauth2/v1/userinfo';

	protected $NS = array(
		'gd' => 'http://schemas.google.com/g/2005',
		'gContact' => 'http://schemas.google.com/contact/2008',
		'batch' => 'http://schemas.google.com/gdata/batch'
	);

	protected $apiVersion = '3.0';

	private $groups = null;

	private $selectedGroup = null;

	private $fieldMapping = null;

	private $maxBatchSize = 100;

	protected $fields = array(
		'salutationtype' => array(
				'name' => 'gd:namePrefix'
			),
		'firstname' => array(
				'name' => 'gd:givenName'
			),
		'lastname' => array(
				'name' => 'gd:familyName'
			),
		'title' => array(
				'name' => 'gd:orgTitle'
			),
		'organizationname' => array(
				'name' => 'gd:orgName'
			),
		'birthday' => array(
				'name' => 'gContact:birthday'
			),  
		'email' => array(
			'name' => 'gd:email',
			'types' => array('home','work','custom')
			),
		'phone' => array(
			'name' => 'gd:phoneNumber',
			'types' => array('mobile','home','work','main','work_fax','home_fax','pager','custom')
			),
		'address' => array(
			'name' => 'gd:structuredPostalAddress',
			'types' => array('home','work','custom')
		),
		'date' => array(
			'name' => 'gContact:event',
			'types' => array('anniversary','custom')
		),
		'description' => array(
			'name' => 'content'
		),
		'custom' => array(
			'name' => 'gContact:userDefinedField'
		),
		'url' => array(
			'name' => 'gContact:website',
			'types' => array('profile','blog','home-page','work','custom')
		)
	);

	public function __construct($oauth2Connection) {
		$this->apiConnection = $oauth2Connection;
	}

	/**
	 * Get the name of the Google Connector
	 * @return string
	 */
	public function getName() {
		return 'GoogleContacts';
	}

	/**
	 * Function to get Fields
	 * @return <Array>
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Function to get the mapped value
	 * @param <Array> $valueSet
	 * @param <Array> $mapping
	 * @return <Mixed>
	 */
	public function getMappedValue($valueSet,$mapping) {
		$key = $mapping['google_field_type'];
		if($key == 'custom')
			$key = $mapping['google_custom_label'];
		return $valueSet[decode_html($key)];
	}

	/**
	 * Function to get field value of google field
	 * @param <Array> $googleFieldDetails
	 * @param <Google_Contacts_Model> $user
	 * @return <Mixed>
	 */
	public function getGoogleFieldValue($googleFieldDetails, $googleRecord, $user) {
		$googleFieldValue = '';
		switch ($googleFieldDetails['google_field_name']) {
			case 'gd:namePrefix' : 
				$googleFieldValue = $googleRecord->getNamePrefix();
				break;
			case 'gd:givenName' : 
				$googleFieldValue = $googleRecord->getFirstName();
				break;
			case 'gd:familyName' : 
				$googleFieldValue = $googleRecord->getLastName();
				break;
			case 'gd:orgTitle' : 
				$googleFieldValue = $googleRecord->getTitle();
				break;
			case 'gd:orgName' : 
				$googleFieldValue = $googleRecord->getAccountName($user->id);
				break;
			case 'gContact:birthday' : 
				$googleFieldValue = $googleRecord->getBirthday();
				break;
			case 'gd:email' : 
				$emails = $googleRecord->getEmails();
				$googleFieldValue = $this->getMappedValue($emails, $googleFieldDetails);
				break;
			case 'gd:phoneNumber' : 
				$phones = $googleRecord->getPhones();
				$googleFieldValue = $this->getMappedValue($phones, $googleFieldDetails);
				break;
			case 'gd:structuredPostalAddress' : 
				$addresses = $googleRecord->getAddresses();
				$googleFieldValue = $this->getMappedValue($addresses, $googleFieldDetails);
				break;
			case 'content' : 
				$googleFieldValue = $googleRecord->getDescription();
				break;
			case 'gContact:userDefinedField' : 
				$userDefinedFields = $googleRecord->getUserDefineFieldsValues();
				$googleFieldValue = $this->getMappedValue($userDefinedFields, $googleFieldDetails);
				break;
			case 'gContact:website' : 
				$websites = $googleRecord->getUrlFields();
				$googleFieldValue = $this->getMappedValue($websites, $googleFieldDetails);
				break;
		}
		return $googleFieldValue;
	}

	/**
	 * Tarsform Google Records to Vtiger Records
	 * @param <array> $targetRecords 
	 * @return <array> tranformed Google Records
	 */
	public function transformToSourceRecord($targetRecords, $user = false) {
		$entity = array();
		$contacts = array();

		if(!isset($this->fieldMapping)) {
			$this->fieldMapping = Google_Utils_Helper::getFieldMappingForUser($user);
		}

		foreach ($targetRecords as $googleRecord) {
			if ($googleRecord->getMode() != WSAPP_SyncRecordModel::WSAPP_DELETE_MODE) {
				if(!$user) $user = Users_Record_Model::getCurrentUserModel();

				$entity = Vtiger_Functions::getMandatoryReferenceFields('Contacts');
				$entity['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $user->id);

				foreach($this->fieldMapping as $vtFieldName => $googleFieldDetails) {
					$googleFieldValue = $this->getGoogleFieldValue($googleFieldDetails, $googleRecord, $user);
					if($vtFieldName == 'mailingaddress') {
						$address = $googleFieldValue;
						$entity['mailingstreet'] = $address['street'];
						$entity['mailingpobox'] = $address['pobox'];
						$entity['mailingcity'] = $address['city'];
						$entity['mailingstate'] = $address['region'];
						$entity['mailingzip'] = $address['postcode'];
						$entity['mailingcountry'] = $address['country'];
						if(empty($entity['mailingstreet'])) {
							$entity['mailingstreet'] = $address['formattedAddress'];
						}
					} else if($vtFieldName == 'otheraddress') {
						$address = $googleFieldValue;
						$entity['otherstreet'] = $address['street'];
						$entity['otherpobox'] = $address['pobox'];
						$entity['othercity'] = $address['city'];
						$entity['otherstate'] = $address['region'];
						$entity['otherzip'] = $address['postcode'];
						$entity['othercountry'] = $address['country'];
						if(empty($entity['otherstreet'])) {
							$entity['otherstreet'] = $address['formattedAddress'];
						}
					} else {
						$entity[$vtFieldName] = $googleFieldValue;
					}
				}

				if (empty($entity['lastname'])) {
					if (!empty($entity['firstname'])) {
						$entity['lastname'] = $entity['firstname'];
					} else if(empty($entity['firstname']) && !empty($entity['email'])) {
						$entity['lastname'] = $entity['email'];
					} else if( !empty($entity['mobile']) || !empty($entity['mailingstreet'])) {
						$entity['lastname'] = 'Google Contact';
					} else {
						continue;
					}
			   }
			}
			$contact = $this->getSynchronizeController()->getSourceRecordModel($entity);

			$contact = $this->performBasicTransformations($googleRecord, $contact);
			$contact = $this->performBasicTransformationsToSourceRecords($contact, $googleRecord);
			$contacts[] = $contact;
		}
		return $contacts;
	}

	/**
	 * Pull the contacts from google
	 * @param <object> $SyncState
	 * @return <array> google Records
	 */
	public function pull($SyncState, $user = false) {
		return $this->getContacts($SyncState, $user);
	}

	/**
	 * Helper to send http request using NetClient
	 * @param <String> $url
	 * @param <Array> $headers
	 * @param <Array> $params
	 * @param <String> $method
	 * @return <Mixed>
	 */
	protected function fireRequest($url,$headers,$params=array(),$method='POST') {
		$httpClient = new Vtiger_Net_Client($url);
		if(count($headers)) $httpClient->setHeaders($headers);
		switch ($method) {
			case 'POST': 
				$response = $httpClient->doPost($params);
				break;
			case 'GET': 
				$response = $httpClient->doGet($params);
				break;
		}
		return $response;
	}

	function fetchContactsFeed($query) {
		$query['alt'] = 'json';
		if($this->apiConnection->isTokenExpired()) $this->apiConnection->refreshToken();
		$headers = array(
			'GData-Version' => $this->apiVersion,
			'Authorization' => $this->apiConnection->token['access_token']['token_type'] . ' ' . 
							   $this->apiConnection->token['access_token']['access_token'],
		);
		$response = $this->fireRequest(self::CONTACTS_URI, $headers, $query, 'GET');
		return $response;
	}

	function getContactListFeed($query) {
		$feed = $this->fetchContactsFeed($query);
		$decoded_feed = json_decode($feed,true);
		return $decoded_feed['feed'];
	}

	function googleFormat($date) {
		return str_replace(' ', 'T', $date);
	}

	/**
	 * Pull the contacts from google
	 * @param <object> $SyncState
	 * @return <array> google Records
	 */
	public function getContacts($SyncState, $user = false) {
		if(!$user) $user = Users_Record_Model::getCurrentUserModel();
		$query = array(
			'max-results' => $this->maxResults,
			'start-index' => 1,
			'orderby' => 'lastmodified',
			'sortorder' => 'ascending',
		);
		if(!isset($this->selectedGroup))
			$this->selectedGroup = Google_Utils_Helper::getSelectedContactGroupForUser($user);

		if($this->selectedGroup != '' && $this->selectedGroup != 'all') {
			if($this->selectedGroup == 'none') return array();
			if(!isset($this->groups)) {
				$this->groups = $this->pullGroups(TRUE);
			}
			if(in_array($this->selectedGroup, $this->groups['entry']))
				$query['group'] = $this->selectedGroup;
			else
				return array();
		}

		if (Google_Utils_Helper::getSyncTime('Contacts', $user)) {
			$query['updated-min'] = $this->googleFormat(Google_Utils_Helper::getSyncTime('Contacts', $user));
			$query['showdeleted'] = 'true';
		}

		$feed = $this->getContactListFeed($query);

		$this->totalRecords = $feed['openSearch$totalResults']['$t'];
		$contactRecords = array();
		if (count($feed['entry']) > 0) {
			$lastEntry = end($feed['entry']);
			$maxModifiedTime = date('Y-m-d H:i:s', strtotime(Google_Contacts_Model::vtigerFormat($lastEntry['updated']['$t'])) + 1);
			if ($this->totalRecords > $this->maxResults) {
				if (!Google_Utils_Helper::getSyncTime('Contacts', $user)) {
					$query['updated-min'] = $this->googleFormat(date('Y-m-d H:i:s', strtotime(Google_Contacts_Model::vtigerFormat($lastEntry['updated']['$t']))));
					$query['start-index'] = $this->maxResults;
				}
				if($this->selectedGroup != '' && $this->selectedGroup != 'all') {
					$query['group'] = $this->selectedGroup;
				}
				$query['max-results'] = (5000);
				$query['updated-max'] = $this->googleFormat($maxModifiedTime);
				$extendedFeed = $this->getContactListFeed($query);
				if(is_array($extendedFeed['entry'])) {
					$contactRecords = array_merge($feed['entry'], $extendedFeed['entry']);
				} else {
					$contactRecords = $feed['entry'];
				}
			} else {
				$contactRecords = $feed['entry'];
			}
		}

		$googleRecords = array();
		foreach ($contactRecords as $i => $contact) {
			$recordModel = Google_Contacts_Model::getInstanceFromValues(array('entity' => $contact));
			$deleted = false;
			if(array_key_exists('gd$deleted', $contact)) {
				$deleted = true;
			}
			if (!$deleted) {
				$recordModel->setType($this->getSynchronizeController()->getSourceType())->setMode(WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE);
			} else {
				$recordModel->setType($this->getSynchronizeController()->getSourceType())->setMode(WSAPP_SyncRecordModel::WSAPP_DELETE_MODE);
			}
			$googleRecords[$contact['id']['$t']] = $recordModel;
		}
		$this->createdRecords = count($googleRecords);
		if (isset($maxModifiedTime)) {
			Google_Utils_Helper::updateSyncTime('Contacts', $maxModifiedTime, $user);
		} else {
			Google_Utils_Helper::updateSyncTime('Contacts', false, $user);
		}
		return $googleRecords;
	}

	/**
	 * Function to send a batch request
	 * @param <String> <Xml> $batchFeed
	 * @return <Mixed>
	 */
	protected function sendBatchRequest($batchFeed) {
		if($this->apiConnection->isTokenExpired()) $this->apiConnection->refreshToken();
		$headers = array(
			'GData-Version' => $this->apiVersion,
			'Authorization' => $this->apiConnection->token['access_token']['token_type'] . ' ' . 
							   $this->apiConnection->token['access_token']['access_token'],
			'If-Match' => '*',
			'Content-Type' => 'application/atom+xml',
		);
		$response = $this->fireRequest(self::CONTACTS_BATCH_URI, $headers, $batchFeed);
		return $response;
	}

	public function mbEncode($str) {
		global $default_charset;
		$convmap = array(0x080, 0xFFFF, 0, 0xFFFF);
		return mb_encode_numericentity(htmlspecialchars($str), $convmap, $default_charset);
	}

	/**
	 * Function to add detail to entry element
	 * @param <SimpleXMLElement> $entry
	 * @param <Google_Contacts_Model> $entity
	 * @param <Users_Record_Model> $user
	 */
	protected function addEntityDetailsToAtomEntry(&$entry,$entity,$user) {
		$gdNS = $this->NS['gd'];
		$gdName = $entry->addChild("name",'',$gdNS);
		if($entity->get('salutationtype')) $gdName->addChild("namePrefix", Google_Utils_Helper::toGoogleXml($entity->get('salutationtype')),$gdNS);
		if($entity->get('firstname')) $gdName->addChild("givenName",  Google_Utils_Helper::toGoogleXml($entity->get('firstname')),$gdNS);
		if($entity->get('lastname')) $gdName->addChild("familyName", Google_Utils_Helper::toGoogleXml($entity->get('lastname')),$gdNS);        
		$gdRel = $gdNS . '#';

		if($entity->get('account_id') || $entity->get('title')) {
			$gdOrganization = $entry->addChild("organization",null,$gdNS);
			$gdOrganization->addAttribute("rel","http://schemas.google.com/g/2005#other");
			if($entity->get('account_id')) $gdOrganization->addChild("orgName",  Google_Utils_Helper::toGoogleXml($entity->get('account_id')),$gdNS);
			if($entity->get('title')) $gdOrganization->addChild("orgTitle", Google_Utils_Helper::toGoogleXml($entity->get('title')),$gdNS);
		}

		if(!isset($this->fieldMapping)) {
			$this->fieldMapping = Google_Utils_Helper::getFieldMappingForUser($user);
		}

		foreach($this->fieldMapping as $vtFieldName => $googleFieldDetails) {
			if(in_array($googleFieldDetails['google_field_name'],array('gd:givenName','gd:familyName','gd:orgTitle','gd:orgName','gd:namePrefix')))
				continue;

			switch ($googleFieldDetails['google_field_name']) {  
				case 'gd:email' : 
					if($entity->get($vtFieldName)) {
						$gdEmail = $entry->addChild("email",'',$gdNS);
						if($googleFieldDetails['google_field_type'] == 'custom')
							$gdEmail->addAttribute("label",$this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
						else
							$gdEmail->addAttribute("rel",$gdRel . $googleFieldDetails['google_field_type']);
						$gdEmail->addAttribute("address", Google_Utils_Helper::toGoogleXml($entity->get($vtFieldName)));
						if($vtFieldName == 'email')
							$gdEmail->addAttribute("primary",'true');
					}
					break;
				case 'gContact:birthday' : 
					if($entity->get('birthday')) {
						$gContactNS = $this->NS['gContact'];
						$gContactBirthday = $entry->addChild("birthday",'',$gContactNS);
						$gContactBirthday->addAttribute("when",$entity->get('birthday'));
					}
					break;
				case 'gd:phoneNumber' :
					if($entity->get($vtFieldName)) {
						$gdPhoneMobile = $entry->addChild("phoneNumber",Google_Utils_Helper::toGoogleXml($entity->get($vtFieldName)),$gdNS);
						if($googleFieldDetails['google_field_type'] == 'custom')
							$gdPhoneMobile->addAttribute("label",$this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
						else
							$gdPhoneMobile->addAttribute("rel",$gdRel . $googleFieldDetails['google_field_type']);
					}
					break;
				case 'gd:structuredPostalAddress' : 
					if($vtFieldName == 'mailingaddress') {
						if($entity->get('mailingstreet') || $entity->get('mailingpobox') || $entity->get('mailingzip') ||
								$entity->get('mailingcity') || $entity->get('mailingstate') || $entity->get('mailingcountry')) {
							$gdAddressHome = $entry->addChild("structuredPostalAddress",null,$gdNS);
							if($googleFieldDetails['google_field_type'] == 'custom')
								$gdAddressHome->addAttribute("label",$this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
							else
								$gdAddressHome->addAttribute("rel",$gdRel . $googleFieldDetails['google_field_type']);
							if($entity->get('mailingstreet')) $gdAddressHome->addChild("street", Google_Utils_Helper::toGoogleXml($entity->get('mailingstreet')),$gdNS);
							if($entity->get('mailingpobox')) $gdAddressHome->addChild("pobox",  Google_Utils_Helper::toGoogleXml($entity->get('mailingpobox')),$gdNS);
							if($entity->get('mailingzip')) $gdAddressHome->addChild("postcode",  Google_Utils_Helper::toGoogleXml($entity->get('mailingzip')),$gdNS);
							if($entity->get('mailingcity')) $gdAddressHome->addChild("city",  Google_Utils_Helper::toGoogleXml($entity->get('mailingcity')),$gdNS);
							if($entity->get('mailingstate')) $gdAddressHome->addChild("region", Google_Utils_Helper::toGoogleXml($entity->get('mailingstate')),$gdNS);
							if($entity->get('mailingcountry')) $gdAddressHome->addChild("country", Google_Utils_Helper::toGoogleXml($entity->get('mailingcountry')),$gdNS);
						}
					} else {
						if($entity->get('otherstreet') || $entity->get('otherpobox') || $entity->get('otherzip') ||
								$entity->get('othercity') || $entity->get('otherstate') || $entity->get('othercountry')) {
							$gdAddressWork = $entry->addChild("structuredPostalAddress",null,$gdNS);
							if($googleFieldDetails['google_field_type'] == 'custom')
								$gdAddressWork->addAttribute("label",$this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
							else
								$gdAddressWork->addAttribute("rel",$gdRel . $googleFieldDetails['google_field_type']);
							if($entity->get('otherstreet')) $gdAddressWork->addChild("street", Google_Utils_Helper::toGoogleXml($entity->get('otherstreet')),$gdNS);
							if($entity->get('otherpobox')) $gdAddressWork->addChild("pobox", Google_Utils_Helper::toGoogleXml($entity->get('otherpobox')),$gdNS);
							if($entity->get('otherzip')) $gdAddressWork->addChild("postcode", Google_Utils_Helper::toGoogleXml($entity->get('otherzip')),$gdNS);
							if($entity->get('othercity')) $gdAddressWork->addChild("city", Google_Utils_Helper::toGoogleXml($entity->get('othercity')),$gdNS);
							if($entity->get('otherstate')) $gdAddressWork->addChild("region", Google_Utils_Helper::toGoogleXml($entity->get('otherstate')),$gdNS);
							if($entity->get('othercountry')) $gdAddressWork->addChild("country", Google_Utils_Helper::toGoogleXml($entity->get('othercountry')),$gdNS);
						}
					}
					break;
				case 'content' : 
					if($entity->get($vtFieldName)) $entry->addChild('content', Google_Utils_Helper::toGoogleXml($entity->get($vtFieldName)));
					break;
				case 'gContact:userDefinedField' : 
					if($entity->get($vtFieldName) && $googleFieldDetails['google_custom_label']) {
						$userDefinedField = $entry->addChild('userDefinedField','',$this->NS['gContact']);
						$userDefinedField->addAttribute('key', $this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
						$userDefinedField->addAttribute('value', Google_Utils_Helper::toGoogleXml($this->mbEncode($entity->get($vtFieldName))));
					}
					break;
				case 'gContact:website' : 
					if($entity->get($vtFieldName)) {
						$websiteField = $entry->addChild('website','',$this->NS['gContact']);
						if($googleFieldDetails['google_field_type'] == 'custom')
							$websiteField->addAttribute('label',$this->mbEncode(decode_html($googleFieldDetails['google_custom_label'])));
						else
							$websiteField->addAttribute('rel',$googleFieldDetails['google_field_type']);
						$websiteField->addAttribute('href', Google_Utils_Helper::toGoogleXml($this->mbEncode($entity->get($vtFieldName))));
					}
					break;
			}
		}

	}

	/**
	 * Function to add update entry to the atomfeed
	 * @param <SimpleXMLElement> $feed
	 * @param <Google_Contacts_Model> $entity
	 * @param <Users_Record_Model> $user
	 */
	protected function addUpdateContactEntry(&$feed,$entity,$user,$contactsGroupMap=array()) {
		$batchNS = $this->NS['batch'];
		$baseEntryId = $entryId = $entity->get('_id');
		$entryId = str_replace('/base/','/full/',$entryId);
		//fix for issue https://code.google.com/p/gdata-issues/issues/detail?id=2129
		$entry = $feed->addChild('entry');
		$entry->addChild('id','update',$batchNS);
		$batchOperation = $entry->addChild('operation','',$batchNS);
		$batchOperation->addAttribute('type','update');
		$category = $entry->addChild("category");
		$category->addAttribute("scheme","http://schemas.google.com/g/2005#kind");
		$category->addAttribute("term","http://schemas.google.com/g/2008#contact");
		$entry->addChild('id',$entryId);

		if(!$user) $user = Users_Record_Model::getCurrentUserModel ();

		if(!isset($this->selectedGroup))
			$this->selectedGroup = Google_Utils_Helper::getSelectedContactGroupForUser($user);

		if(array_key_exists($baseEntryId, $contactsGroupMap)) {
			$groupMemberShip = $entry->addChild('groupMembershipInfo','',$this->NS['gContact']);
			$groupMemberShip->addAttribute('href',$contactsGroupMap[$baseEntryId]);
		} elseif($this->selectedGroup != '' && $this->selectedGroup != 'all') {
			$groupMemberShip = $entry->addChild('groupMembershipInfo','',$this->NS['gContact']);
			$groupMemberShip->addAttribute('href',$this->selectedGroup);
		}

		$this->addEntityDetailsToAtomEntry($entry, $entity, $user);
	}

	/**
	 * Function to add delete contact entry to atom feed
	 * @param <SimpleXMLElement> $feed
	 * @param <Google_Contacts_Model> $entity
	 */
	protected function addDeleteContactEntry(&$feed,$entity) {  
		$batchNS = $this->NS['batch'];
		$entryId = $entity->get('_id');
		$entryId = str_replace('/base/','/full/',$entryId);
		//fix for issue https://code.google.com/p/gdata-issues/issues/detail?id=2129
		$entry = $feed->addChild('entry');
		$entry->addChild('id','delete',$batchNS);
		$batchOperation = $entry->addChild('operation','',$batchNS);
		$batchOperation->addAttribute('type','delete');
		$entry->addChild('id',$entryId);
	}

	/**
	 * Function to add create entry to the atomfeed
	 * @param <SimpleXMLElement> $feed
	 * @param <Google_Contacts_Model> $entity
	 * @param <Users_Record_Model> $user
	 */
	protected function addCreateContactEntry(&$feed,$entity,$user) {
		$entry = $feed->addChild("entry");
		$batchNS = $this->NS['batch'];
		$entry->addChild("id","create",$batchNS);
		$batchOperation = $entry->addChild("operation",'',$batchNS);
		$batchOperation->addAttribute("type","insert");
		$category = $entry->addChild("category");
		$category->addAttribute("scheme","http://schemas.google.com/g/2005#kind");
		$category->addAttribute("term","http://schemas.google.com/g/2008#contact");

		if(!$user) $user = Users_Record_Model::getCurrentUserModel ();

		if(!isset($this->selectedGroup))
			$this->selectedGroup = Google_Utils_Helper::getSelectedContactGroupForUser($user);

		if($this->selectedGroup != '' && $this->selectedGroup != 'all') {
			$groupMemberShip = $entry->addChild('groupMembershipInfo','',$this->NS['gContact']);
			$groupMemberShip->addAttribute('href',$this->selectedGroup);
		}

		$this->addEntityDetailsToAtomEntry($entry, $entity, $user);
	}

	/**
	 * Function to add Retreive entry to atomfeed
	 * @param <SimpleXMLElement> $feed
	 * @param <Google_Contacts_Model> $entity
	 * @param <Users_Record_Model> $user
	 */
	protected function addRetrieveContactEntry(&$feed, $entity, $user) {
		$entryId = $entity->get('_id');
		$entryId = str_replace('/base/','/full/',$entryId);
		$entry = $feed->addChild("entry");
		$batchNS = $this->NS['batch'];
		$entry->addChild("id","retrieve",$batchNS);
		$batchOperation = $entry->addChild("operation",'',$batchNS);
		$batchOperation->addAttribute("type","query");
		$entry->addChild('id',$entryId);
	}

	/**
	 * Function to get GoogleContacts-ContactsGroup map for the supplied records
	 * @global  $default_charset
	 * @param <Array> $records
	 * @param <Users_Record_Model> $user
	 * @return <Array>
	 */
	protected function googleContactsGroupMap($records,$user) {
		global $default_charset;
		$contactsGroupMap = array();

		$atom = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?>
		<feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008'
			  xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch' />");

		foreach($records as $record) {
			$entity = $record->get('entity');
			$this->addRetrieveContactEntry($atom, $entity, $user);
		}

		$payLoad = html_entity_decode($atom->asXML(), ENT_QUOTES, $default_charset);
		$response = $this->sendBatchRequest($payLoad);


        $main1 = new SimpleXMLElement($payLoad);
        $main1->entry->addAttribute('gd:etag', '*');
        $payLoad = $main1->asXML();


		if($response) {
			$responseXml = simplexml_load_string($response);
			$responseXml->registerXPathNamespace('gd', $this->NS['gd']);
			$responseXml->registerXPathNamespace('gContact', $this->NS['gContact']);
			$responseXml->registerXPathNamespace('batch', $this->NS['batch']);

			foreach($responseXml->entry as $entry) {
				$entryXML = $entry->asXML();
				$p = xml_parser_create();
				xml_parse_into_struct($p, $entryXML, $xmlList, $index);
				xml_parser_free($p);

				if(count($xmlList)) {
					foreach($xmlList as $tagDetails) {

						if($tagDetails['tag'] == 'ID') {
							$googleContactId = $tagDetails['value'];
						}

						if($tagDetails['tag'] == 'GCONTACT:GROUPMEMBERSHIPINFO') {
							$attribs = $tagDetails['attributes'];
							$googleContactGroupId = $attribs['HREF'];
						}

						if(isset($googleContactId) && isset($googleContactGroupId)) {
							$contactsGroupMap[$googleContactId] = $googleContactGroupId;
							unset($googleContactId);unset($googleContactGroupId);
						}

					}
				}
			}
		}
		return $contactsGroupMap;
	}

	/**
	 * Function to push records in a batch
	 * https://developers.google.com/google-apps/contacts/v3/index#batch_operations
	 * @global <String> $default_charset
	 * @param <Array> $records
	 * @param <Users_Record_Model> $user
	 * @return <Array> - pushedRecords
	 */
	protected function pushChunk($records,$user) {
		global $default_charset;
		$atom = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?>
		<feed xmlns='http://www.w3.org/2005/Atom' xmlns:gContact='http://schemas.google.com/contact/2008'
			  xmlns:gd='http://schemas.google.com/g/2005' xmlns:batch='http://schemas.google.com/gdata/batch' />");

		if($records[0]->getMode() == WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE) {
			$contactsGroupMap = $this->googleContactsGroupMap($records,$user);
		}
		foreach ($records as $record) {
			$entity = $record->get('entity');
			try {
				if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE) {
					$this->addUpdateContactEntry($atom,$entity,$user, $contactsGroupMap);
				} else if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_DELETE_MODE) {
					$this->addDeleteContactEntry($atom,$entity);
				} else {
					$this->addCreateContactEntry($atom,$entity,$user);
				}
			} catch (Exception $e) {
				continue;
			}
		}
		$payLoad = html_entity_decode($atom->asXML(), ENT_QUOTES, $default_charset);

       $main1 = new SimpleXMLElement($payLoad);
       $main1->entry->addAttribute('gd:etag', '*');
       $payLoad = $main1->asXML();


		$response = $this->sendBatchRequest($payLoad);
		$responseXml = simplexml_load_string($response);
		if($responseXml) {
			$responseXml->registerXPathNamespace('gd', $this->NS['gd']);
			$responseXml->registerXPathNamespace('gContact', $this->NS['gContact']);
			$responseXml->registerXPathNamespace('batch', $this->NS['batch']);
			foreach ($records as $index => $record) {
				$newEntity = array();
				$entry = $responseXml->entry[$index];
				$newEntityId = (string)$entry->id;
				$newEntity['id']['$t'] = str_replace('/full/', '/base/', $newEntityId);
				$newEntity['updated']['$t'] = (string)$entry->updated[0];
				$record->set('entity', $newEntity);
			}
		}
		return $records;
	}

	/**
	 * Function to push records in batch of maxBatchSize
	 * @param <Array Google_Contacts_Model> $records
	 * @param <Users_Record_Model> $user
	 * @return <Array> - pushed records
	 */
	protected function batchPush($records,$user) {
		$chunks = array_chunk($records, $this->maxBatchSize);
		$mergedRecords = array();
		foreach($chunks as $chunk) {
			$pushedRecords = $this->pushChunk($chunk, $user);
			$mergedRecords = array_merge($mergedRecords,$pushedRecords);
		}
		return $mergedRecords;
	}

	/**
	 * Push the vtiger records to google
	 * @param <array> $records vtiger records to be pushed to google
	 * @return <array> pushed records
	 */
	public function push($records, $user = false) {
		if(!$user) $user = Users_Record_Model::getCurrentUserModel();

		if(!isset($this->selectedGroup))
			$this->selectedGroup = Google_Utils_Helper::getSelectedContactGroupForUser($user);

		if($this->selectedGroup != '' && $this->selectedGroup != 'all') {
			if($this->selectedGroup == 'none') return array();
			if(!isset($this->groups)) {
				$this->groups = $this->pullGroups(TRUE);
			}
			if(!in_array($this->selectedGroup, $this->groups['entry']))
				return array();
		}

		$updateRecords = $deleteRecords = $addRecords = array();
		foreach($records as $record) {
			if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE) {
				$updateRecords[] = $record;
			} else if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_DELETE_MODE) {
				$deleteRecords[] = $record;
			} else {
				$addRecords[] = $record;
			}
		}

		if(count($deleteRecords)) {
			$deletedRecords = $this->batchPush($deleteRecords, $user);
		}

		if(count($updateRecords)) {
			$updatedRecords = $this->batchPush($updateRecords, $user);
		}

		if(count($addRecords)) {
			$addedRecords = $this->batchPush($addRecords, $user);
		}

		$i = $j = $k = 0;
		foreach($records as $record) {
			if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_UPDATE_MODE) {
				$uprecord = $updatedRecords[$i++];
				$newEntity = $uprecord->get('entity');
				$record->set('entity',$newEntity);
			} else if ($record->getMode() == WSAPP_SyncRecordModel::WSAPP_DELETE_MODE) {
				$delrecord = $deletedRecords[$j++];
				$newEntity = $delrecord->get('entity');
				$record->set('entity',$newEntity);
			} else {
				$adrecord = $addedRecords[$k++];
				$newEntity = $adrecord->get('entity');
				$record->set('entity',$newEntity);
			}
		}
		return $records;
	}

	/**
	 * Tarsform  Vtiger Records to Google Records
	 * @param <array> $vtContacts 
	 * @return <array> tranformed vtiger Records
	 */
	public function transformToTargetRecord($vtContacts) {
		$records = array();
		foreach ($vtContacts as $vtContact) {
			$recordModel = Google_Contacts_Model::getInstanceFromValues(array('entity' => $vtContact));
			$recordModel->setType($this->getSynchronizeController()->getSourceType())->setMode($vtContact->getMode())->setSyncIdentificationKey($vtContact->get('_syncidentificationkey'));
			$recordModel = $this->performBasicTransformations($vtContact, $recordModel);
			$recordModel = $this->performBasicTransformationsToTargetRecords($recordModel, $vtContact);
			$records[] = $recordModel;
		}
		return $records;
	}

	/**
	 * returns if more records exits or not
	 * @return <boolean> true or false
	 */
	public function moreRecordsExits() {
		return ($this->totalRecords - $this->createdRecords > 0) ? true : false;
	}

	/**
	 * Function to pull contact groups for user
	 * @param <Boolean> $onlyIds
	 * @return <Array>
	 */
	public function pullGroups($onlyIds = FALSE) {
		//max-results: If you want to receive all of the groups, rather than only the default maximum.
		$query = array(
			'alt' => 'json',
			'max-results' => 1000,
		);
		if($this->apiConnection->isTokenExpired()) $this->apiConnection->refreshToken();
		$headers = array(
			'GData-Version' => $this->apiVersion,
			'Authorization' => $this->apiConnection->token['access_token']['token_type'] . ' ' . 
							   $this->apiConnection->token['access_token']['access_token']
		);
		$response = $this->fireRequest(self::CONTACTS_GROUP_URI, $headers,$query,'GET');
		$decoded_resp = json_decode($response,true);
		$feed = $decoded_resp['feed'];
		$entries = $feed['entry'];
		$groups = array(
			'title' => $feed['title']['$t']
		);
		if(is_array($entries)) {
			foreach($entries as $entry) {
				$group = array(
					'id' => $entry['id']['$t'],
					'title' => $entry['title']['$t']
				);
				if($onlyIds) $group = $group['id'];
				$groups['entry'][] = $group;
			}
		}
		return $groups;
	}    

	/**
	 * Function to get user profile info
	 * @return <Mixed>
	 */
	public function getUserProfileInfo() {
		if($this->apiConnection->isTokenExpired()) $this->apiConnection->refreshToken();
		$headers = array(
			'GData-Version' => $this->apiVersion,
			'Authorization' => $this->apiConnection->token['access_token']['token_type'] . ' ' . 
							   $this->apiConnection->token['access_token']['access_token'],
			'If-Match' => '*',
			'Content-Type' => 'application/json',
		);
		$response = $this->fireRequest(self::USER_PROFILE_INFO, $headers, array(), 'GET');
		return $response;
	}
}
