<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class VTSimpleTemplate
{

    function __construct($templateString)
    {
        $this->template = $templateString;
    }

    function render($entityCache, $entityId)
    {
        $this->cache = $entityCache;
        $this->parent = $this->cache->forId($entityId);

        return $this->parseTemplate();
    }

    private function matchHandler($match)
    {
        preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $match[1], $matches);
        if ($match[1] == 'logo') {
            return $this->getMetaValue($match[1]);
        }
        // If parent is empty then we can't do any thing here
        if (!empty($this->parent)) {
            if (php7_count($matches) == 0) {
                $fieldname = $match[1];

                //To handle comments for this module
                if (in_array($fieldname, ModComments_Module_Model::getCommentFieldNames())) {
                    return $this->getComments($this->parent->getModuleName(), $fieldname, $this->parent->getId());
                }

                $data = $this->parent->getData();

                if (isset($data[$fieldname]) || $fieldname === '_DATE_FORMAT_') {
                    if ($this->useValue($data, $fieldname)) {
                        $result = $this->transformToUserFormat($this->parent->getModuleName(), $fieldname, $data[$fieldname]);
                    } else {
                        $result = '';
                    }
                } else {
                    $result = '$' . $fieldname;
                }
            } else {
                [$full, $referenceField, $referenceModule, $fieldname] = $matches;
                if ($referenceModule === '__VtigerMeta__' || $fieldname === 'dbLabel') {
                    $result = $this->getMetaValue($fieldname);
                } elseif ('__VtigerCompany__' == $referenceModule) {
                    $result = $this->getCompanySetting($fieldname);
                } else {
                    $referenceId = $this->parent->get($referenceField);
                    if ($referenceId == null) {
                        $result = "";
                    } else {
                        //To handle comments for this reference module
                        if (in_array($fieldname, ModComments_Module_Model::getCommentFieldNames())) {
                            return $this->getComments($referenceModule, $fieldname, $referenceId);
                        }

                        if ($referenceField === 'contact_id') {
                            $referenceIdsList = explode(',', $referenceId);
                            $parts = [];
                            foreach ($referenceIdsList as $referenceId) {
                                $entity = $this->cache->forId($referenceId);
                                $data = $entity->getData();
                                if ($this->useValue($data, $fieldname)) {
                                    $parts[] = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
                                }
                            }

                            return implode(',', $parts);
                        }
                        $entity = $this->cache->forId($referenceId);
                        if ($referenceModule === "Users" && $entity->getModuleName() == "Groups") {
                            [$groupEntityId, $groupId] = vtws_getIdComponents($referenceId);

                            require_once('include/utils/GetGroupUsers.php');
                            $ggu = new GetGroupUsers();
                            $ggu->getAllUsersInGroup($groupId);

                            //Clearing static cache for sub groups
                            GetGroupUsers::$groupIdsList = [];

                            $users = $ggu->group_users;
                            $parts = [];
                            foreach ($users as $userId) {
                                $refId = vtws_getWebserviceEntityId("Users", $userId);
                                $entity = $this->cache->forId($refId);
                                $data = $entity->getData();
                                if ($this->useValue($data, $fieldname)) {
                                    $parts[] = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
                                }
                            }
                            $result = implode(",", $parts);
                        } elseif ($entity->getModuleName() === $referenceModule) {
                            $data = $entity->getData();
                            if ($this->useValue($data, $fieldname)) {
                                $handler = vtws_getModuleHandlerFromName($referenceModule, $this->cache->user);
                                $meta = $handler->getMeta();
                                $referenceFieldList = $meta->getReferenceFieldDetails();
                                if (array_key_exists($fieldname, $referenceFieldList)) {
                                    $webserviceId = $data[$fieldname];
                                    $idComponents = vtws_getIdComponents($webserviceId);
                                    if ($fieldname == 'currency_id' && $referenceModule == 'Users') {
                                        $result = decode_html(getCurrencyName($idComponents[1]));
                                    } else {
                                        $result = decode_html(Vtiger_Util_Helper::getRecordName($idComponents[1]));
                                    }
                                } else {
                                    $result = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
                                }
                            } else {
                                $result = '';
                            }
                        } else {
                            $result = '';
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function useValue($data, $fieldname)
    {
        return true;
    }

    function parseTemplate()
    {
        return preg_replace_callback('/\\$(\w+|\((\w+) : \(([_\w]+)\) (\w+)\))/', [$this, "matchHandler"], $this->template ?: '');
    }

    function getCompanySetting($fieldname)
    {
        return Settings_Vtiger_CompanyDetails_Model::getSetting($fieldname);
    }

    function getMetaValue($fieldname)
    {
        require_once 'config.inc.php';
        global $site_URL, $PORTAL_URL, $current_user, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID;
        switch ($fieldname) {
            case 'date'                    :
                $ownerId = $this->getOwnerId();
                $ownerObject = new Users();
                $ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

                $date = new DateTimeField(null);

                return $date->getDisplayDate($ownerObject);
            case 'time'                    :
                $ownerId = $this->getOwnerId();
                $ownerObject = new Users();
                $ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

                $date = new DateTimeField(null);
                $time = $date->getDisplayTime($ownerObject);

                return Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat($time);
            case 'dbtimezone'            :
                return DateTimeField::getDBTimeZone();
            case 'usertimezone'            :
                $ownerId = $this->getOwnerId();

                if (!$ownerId && ($this->parent->moduleName == "PriceBooks" || $this->parent->moduleName == "Faq")) {
                    $modifiedby = explode("x", $this->parent->data['modifiedby']);
                    $ownerId = $modifiedby[1];
                }
                if ($ownerId) {
                    $ownerFocus = CRMEntity::getInstance('Users');
                    $ownerFocus->retrieve_entity_info($ownerId, 'Users');

                    return getTranslatedString($ownerFocus->column_fields['time_zone'], 'Users');
                }

                return '';
            case 'crmdetailviewurl'        :
                $wsId = $this->parent->getId();
                $parts = explode('x', $wsId);
                $recordId = $parts[1];
                $moduleName = $this->parent->getModuleName();

                return "$site_URL/index.php?module=$moduleName&view=Detail&record=$recordId";
            case 'portaldetailviewurl'    :
                $wsId = $this->parent->getId();
                $parts = explode('x', $wsId);
                $recordId = $parts[1];
                $moduleName = $this->parent->getModuleName();
                $recorIdName = 'id';
                if ($moduleName == 'HelpDesk') {
                    $recorIdName = 'ticketid';
                }
                if ($moduleName == 'Faq') {
                    $recorIdName = 'faqid';
                }
                if ($moduleName == 'Products') {
                    $recorIdName = 'productid';
                }

                return $PORTAL_URL . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&status=true';
            case 'portalpdfurl'            :
                $wsId = $this->parent->getId();
                $parts = explode('x', $wsId);
                $recordId = $parts[1];
                $moduleName = $this->parent->getModuleName();
                $recorIdName = 'id';

                return $PORTAL_URL . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&downloadfile=true';
            case 'siteurl'                :
                return $site_URL;
            case 'portalurl'            :
                return $PORTAL_URL;
            case 'logo'                    :
                return '<img src="cid:logo" />';

            case 'recordId'                :
                [$moduleId, $recordId] = vtws_getIdComponents($this->parent->getId());

                return $recordId;

            case 'supportName'            :
                return $HELPDESK_SUPPORT_NAME;
            case 'supportEmailId'        :
                return $HELPDESK_SUPPORT_EMAIL_ID;
            case 'reports_to_id'        :
                $ownerId = $this->getOwnerId();
                $ownerObject = new Users();
                $ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

                $reportsToId = $ownerObject->column_fields['reports_to_id'];
                if (empty($reportsToId)) {
                    return '';
                }
                $reportsToOwnerObject = new Users();
                $reportsToOwnerObject->retrieveCurrentUserInfoFromFile($reportsToId);

                return $reportsToOwnerObject->column_fields['email1'];

            default:
                '';
        }
    }

    /**
     * Function to transform the field values into user format
     *
     * @param <String> $moduleName
     * @param <String> $fieldName
     * @param <String> $fieldValue
     *
     * @return <String> $fieldValue
     */
    public function transformToUserFormat($moduleName, $fieldName, $fieldValue)
    {
        global $adb, $log, $current_user;

        //getting owner info
        $referenceId = $this->parent->get('assigned_user_id');
        if ($referenceId) {
            $entity = $this->cache->forId($referenceId);
            [$entityId, $ownerId] = vtws_getIdComponents($referenceId);

            if ($entity->getModuleName() === 'Groups') {
                [$moduleId, $recordId] = vtws_getIdComponents($this->parent->getId());
                $ownerId = Vtiger_Util_Helper::getCreator($recordId);
            }
        }
        if (empty($ownerId)) {
            [$moduleId, $recordId] = vtws_getIdComponents($this->parent->getId());
            $ownerArr = getRecordOwnerId($recordId);
            if (empty($ownerArr)) {
                $ownerArr['Users'] = Vtiger_Util_Helper::getCreator($recordId);
            }
            $ownerId = $ownerArr['Users'];
        }
        $ownerObject = new Users();
        $ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

        if ($ownerObject && $fieldName === '_DATE_FORMAT_') {
            return $ownerObject->column_fields['date_format'];
        }

        //getting field instance info
        $entityObject = VtigerWebserviceObject::fromName($adb, $moduleName);
        $handlerPath = $entityObject->getHandlerPath();
        $handlerClass = $entityObject->getHandlerClass();

        require_once $handlerPath;

        $entityHandler = new $handlerClass($entityObject, $current_user, $adb, $log);
        $entityMeta = $entityHandler->getMeta();
        $entityFields = $entityMeta->getModuleFields();
        $fieldInstance = $entityFields[$fieldName];
        if (!$fieldInstance) {
            return;
        }
        switch ($fieldInstance->getFieldDataType()) {
            case 'date'            :
                if (!empty($fieldValue)) {
                    $dateFieldObj = new DateTimeField($fieldValue);
                    $fieldValue = $dateFieldObj->getDisplayDate($ownerObject);
                }
                break;

            case 'datetime'        :
                $fieldValue = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($fieldValue, $ownerObject);
                break;

            case 'currency'        :
                [, $currencyId] = vtws_getIdComponents($this->parent->get('currency_id'));
                $fieldValue = CurrencyField::convertToUserFormat($fieldValue, $ownerObject, true);
                $currencyInfo = getCurrencySymbolandCRate($currencyId);
                $currencySymbol = $currencyInfo['symbol'];

                $fieldValue = CurrencyField::appendCurrencySymbol($fieldValue, $currencySymbol);
                break;

            case 'time'            :
                $fieldValue = Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat($fieldValue, $ownerObject);
                break;

            case 'picklist'        :
                require_once 'includes/runtime/LanguageHandler.php';
                require_once 'includes/runtime/Globals.php';
                $fieldValue = vtranslate($fieldValue, $moduleName, $ownerObject->column_fields['language']);
                break;

            case 'multipicklist' :
                require_once 'includes/runtime/LanguageHandler.php';
                require_once 'includes/runtime/Globals.php';
                $fieldValueParts = explode(';', $fieldValue);
                foreach ($fieldValueParts as $index => $fieldValue) {
                    $fieldValueParts[$index] = vtranslate($fieldValue, $moduleName, $ownerObject->column_fields['language']);
                }
                $fieldValue = implode(';', $fieldValueParts);
                break;
            case 'boolean'        :
                require_once 'includes/runtime/LanguageHandler.php';
                require_once 'includes/runtime/Globals.php';
                if ($fieldValue == 1) {
                    $fieldValue = vtranslate('LBL_YES', $moduleName);
                } else {
                    $fieldValue = vtranslate('LBL_NO', $moduleName);
                }
                break;
            case 'owner' :
            case 'reference'    :
                if ($fieldName == 'currency_id') {
                    $currencyId = explode('x', $fieldValue);
                    $currencyId = $currencyId[1];
                    $fieldValue = decode_html(getCurrencyName($currencyId));
                } else {
                    if ($fieldValue != 0 && $fieldValue != null) {
                        $id = explode('x', $fieldValue);
                        $webServiceObject = VtigerWebserviceObject::fromId($adb, $id[0]);
                        if ($webServiceObject) {
                            $Value = getEntityName($webServiceObject->getEntityName(), $id);
                            $fieldValue = $Value[$id[1]];
                        }
                    }
                }
                break;
            case 'double'        :
                if ($moduleName === 'HelpDesk' && in_array($fieldName, ['days', 'hours'])) {
                    $fieldValue = decimalFormat($fieldValue);
                } else {
                    $fieldValue;
                }
                break;
            default                :
                $fieldValue;
        }

        return nl2br($fieldValue);
    }

    /**
     * Function to fieldvalues of Comments
     *
     * @param string $moduleName
     * @param string $fieldName
     * @param string $fieldValue
     *
     * @return string $comments
     */
    public function getComments(string $moduleName, string $fieldName, string $fieldValue): string
    {
        global $current_user;

        $isSummaryField = in_array($fieldName, ModComments_Module_Model::$commentFieldsSummary);
        $limit = ModComments_Module_Model::$commentFieldsLimit[$fieldName];
        $recordId = vtws_getIdComponents($fieldValue)[1];
        $comments = ModComments_Record_Model::getCommentsByRecord($recordId, $limit ?? null);
        $commentsList = '';

        foreach ($comments as $comment) {
            $content = decode_html($comment['commentcontent']);

            if (!empty($content)) {
                $commentsList .= nl2br($content);

                if ($isSummaryField) {
                    $dateTime = new DateTimeField($comment['createdtime']);
                    $dateTimeValue = $dateTime->getDisplayDateTimeValue();
                    $dateTimeParts = explode(' ', $dateTimeValue);
                    $createdTime = $dateTimeParts[0] . ' ' . Vtiger_Time_UIType::getDisplayValueUserFormat($dateTimeParts[1]);
                    $commentsList .= sprintf(
                        '<br>%s %s:<small>%s (%s)</small><br><br>',
                        getOwnerName($comment['userid']),
                        vtranslate('LBL_COMMENTED_AT', 'ModComments'),
                        $createdTime,
                        vtranslate($current_user->time_zone, 'Users'),
                    );
                }
            }
        }

        return $commentsList;
    }

    /**
     * Function to get the record owner id of the current record
     * @return <integer> $ownerId
     */
    function getOwnerId()
    {
        $referenceId = $this->parent->get('assigned_user_id');
        if ($referenceId) {
            $entity = $this->cache->forId($referenceId);
            [$entityId, $ownerId] = vtws_getIdComponents($referenceId);
            if ($entity->getModuleName() === 'Groups') {
                [$moduleId, $recordId] = vtws_getIdComponents($this->parent->getId());
                $ownerId = Vtiger_Util_Helper::getCreator($recordId);
            }
        }

        return $ownerId;
    }
}