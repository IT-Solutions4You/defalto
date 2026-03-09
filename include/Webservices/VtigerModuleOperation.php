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

require_once 'include/Webservices/LineItem/InventoryItemHelpers.php';

class VtigerModuleOperation extends WebserviceEntityOperation
{
    protected $tabId;
    protected $isEntity = true;
    protected $partialDescribeFields = null;

    public function __construct($webserviceObject, $user, $adb, $log)
    {
        parent::__construct($webserviceObject, $user, $adb, $log);
        $this->meta = $this->getMetaInstance();
        $this->tabId = $this->meta->getTabId();
    }

    protected function getMetaInstance()
    {
        if (empty(WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id])) {
            WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id] = new VtigerCRMObjectMeta($this->webserviceObject, $this->user);
        }

        return WebserviceEntityOperation::$metaCache[$this->webserviceObject->getEntityName()][$this->user->id];
    }

    public function create($elementType, $element)
    {
        $crmObject = new VtigerCRMObject($elementType, false);

        $element = DataTransform::sanitizeForInsert($element, $this->meta);
        $this->logCurrencyDebug('create_sanitized', $elementType, $element);
        $element = $this->ensureCurrencyId($element);
        $this->logCurrencyDebug('create_currency', $elementType, $element);

        $error = $crmObject->create($element);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        $id = $crmObject->getObjectId();

        // Bulk Save Mode
        if (CRMEntity::isBulkSaveMode()) {
            // Avoiding complete read, as during bulk save mode, $result['id'] is enough
            return ['id' => vtws_getId($this->meta->getEntityId(), $id)];
        }

        $error = $crmObject->read($id);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        return DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);
    }

    /**
     * @param array $element
     * @return array
     */
    protected function ensureCurrencyId(array $element): array
    {
        $currencyId = $element['currency_id'] ?? null;
        $currencyInfo = $this->resolveCurrencyInfo($currencyId);
        $element['currency_id'] = $currencyInfo['id'];
        $_REQUEST['currency_id'] = $currencyInfo['id'];

        if (empty($element['conversion_rate'])) {
            $element['conversion_rate'] = $currencyInfo['conversion_rate'];
        }

        if (empty($_REQUEST['conversion_rate'])) {
            $_REQUEST['conversion_rate'] = $element['conversion_rate'];
        }

        $this->logCurrencyDebug('ensure_currency', $this->webserviceObject->getEntityName(), [
            'currency_id' => $currencyId,
            'resolved' => $currencyInfo['id'],
            'conversion_rate' => $element['conversion_rate'],
        ]);

        return $element;
    }

    /**
     * @param string $stage
     * @param string $elementType
     * @param array $element
     * @return void
     */
    protected function logCurrencyDebug(string $stage, string $elementType, array $element): void
    {
        $logFile = 'logs/webservice_currency_debug.log';
        $currency = $element['currency_id'] ?? null;
        $message = sprintf(
            '[%s] stage=%s module=%s currency=%s keys=%s',
            date('Y-m-d H:i:s'),
            $stage,
            $elementType,
            is_scalar($currency) ? (string)$currency : gettype($currency),
            implode(',', array_keys($element))
        );
        error_log($message . PHP_EOL, 3, $logFile);
    }

    /**
     * @param $currencyId
     * @return array|int[]
     * @throws Exception
     */
    protected function resolveCurrencyInfo($currencyId): array
    {
        if (empty($currencyId)) {
            $this->logCurrencyDebug('resolve_currency_default', $this->webserviceObject->getEntityName(), [
                'currency_id' => $currencyId,
            ]);

            return ['id' => 1, 'conversion_rate' => 1];
        }

        $lookup = $currencyId;
        $id = InventoryItem_Webservice_Helpers::getCrmIdFromWsId($currencyId);

        if (!empty($id)) {
            $lookup = $id;
        }

        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT id, conversion_rate FROM vtiger_currency_info WHERE (id = ? OR currency_code = ?) AND deleted = 0 AND currency_status = ?',
            [$lookup, $lookup, 'Active']
        );

        if ($result && $db->num_rows($result) > 0) {
            $this->logCurrencyDebug('resolve_currency_hit', $this->webserviceObject->getEntityName(), [
                'currency_id' => $currencyId,
                'lookup' => $lookup,
            ]);

            return [
                'id' => (int)$db->query_result($result, 0, 'id'),
                'conversion_rate' => (float)$db->query_result($result, 0, 'conversion_rate'),
            ];
        }

        $this->logCurrencyDebug('resolve_currency_miss', $this->webserviceObject->getEntityName(), [
            'currency_id' => $currencyId,
            'lookup' => $lookup,
        ]);

        return ['id' => 1, 'conversion_rate' => 1];
    }


    public function retrieve($id)
    {
        $ids = vtws_getIdComponents($id);
        $elemid = $ids[1];

        $crmObject = new VtigerCRMObject($this->tabId, true);
        $error = $crmObject->read($elemid);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        return DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);
    }

    public function relatedIds($id, $relatedModule, $relatedLabel, $relatedHandler = null)
    {
        $ids = vtws_getIdComponents($id);
        $sourceModule = $this->webserviceObject->getEntityName();
        global $currentModule;
        $db = PearDatabase::getInstance();
        $currentModule = $sourceModule;
        $sourceRecordModel = Vtiger_Record_Model::getInstanceById($ids[1], $sourceModule);
        $targetModel = Vtiger_RelationListView_Model::getInstance($sourceRecordModel, $relatedModule, $relatedLabel);
        $sql = $targetModel->getRelationQuery();

        $relatedWebserviceObject = VtigerWebserviceObject::fromName($db, $relatedModule);
        $relatedModuleWSId = $relatedWebserviceObject->getEntityId();

        // Rewrite query to pull only crmid transformed as webservice id.
        $sqlFromPart = substr($sql, stripos($sql, ' FROM ') + 6);
        $sql = sprintf("SELECT DISTINCT concat('%sx',vtiger_crmentity.crmid) as wsid FROM %s", $relatedModuleWSId, $sqlFromPart);

        $rs = $this->pearDB->pquery($sql, []);
        $relatedIds = [];
        while ($row = $this->pearDB->fetch_array($rs)) {
            $relatedIds[] = $row['wsid'];
        }

        return $relatedIds;
    }

    public function update($element)
    {
        $ids = vtws_getIdComponents($element["id"]);
        $element = DataTransform::sanitizeForInsert($element, $this->meta);

        $crmObject = new VtigerCRMObject($this->tabId, true);
        $crmObject->setObjectId($ids[1]);
        $error = $crmObject->update($element);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        $id = $crmObject->getObjectId();

        $error = $crmObject->read($id);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        return DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);
    }

    public function revise($element)
    {
        $ids = vtws_getIdComponents($element["id"]);
        $element = DataTransform::sanitizeForInsert($element, $this->meta);

        $crmObject = new VtigerCRMObject($this->tabId, true);
        $crmObject->setObjectId($ids[1]);
        $error = $crmObject->revise($element);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        $id = $crmObject->getObjectId();

        $error = $crmObject->read($id);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        return DataTransform::filterAndSanitize($crmObject->getFields(), $this->meta);
    }

    public function delete($id)
    {
        $ids = vtws_getIdComponents($id);
        $elemid = $ids[1];

        $crmObject = new VtigerCRMObject($this->tabId, true);

        $error = $crmObject->delete($elemid);
        if (!$error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        return ["status" => "successful"];
    }

    public function query($q)
    {
        $parser = new Parser($this->user, $q);
        $error = $parser->parse();

        if ($error) {
            return $parser->getError();
        }

        $mysql_query = $parser->getSql();
        $meta = $parser->getObjectMetaData();
        $this->pearDB->startTransaction();
        $result = $this->pearDB->pquery($mysql_query, []);
        $tableIdColumn = $meta->getIdColumn();
        $error = $this->pearDB->hasFailedTransaction();
        $this->pearDB->completeTransaction();

        if ($error) {
            throw new WebServiceException(
                WebServiceErrorCode::$DATABASEQUERYERROR,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$DATABASEQUERYERROR
                )
            );
        }

        $noofrows = $this->pearDB->num_rows($result);
        $output = [];
        for ($i = 0; $i < $noofrows; $i++) {
            $row = $this->pearDB->fetchByAssoc($result, $i);
            if (!$meta->hasPermission(EntityMeta::$RETRIEVE, $row[$tableIdColumn])) {
                continue;
            }
            $output[$row[$tableIdColumn]] = DataTransform::sanitizeDataWithColumn($row, $meta);
        }

        $newOutput = [];
        if (php7_count($output)) {
            //Added check if tags was requested or not
            if (stripos($mysql_query, $meta->getEntityBaseTable() . '.tags') !== false) {
                $tags = Vtiger_Tag_Model::getAllAccessibleTags(array_keys($output));
            }
            foreach ($output as $id => $row1) {
                if (!empty($tags[$id])) {
                    $output[$id]['tags'] = $tags[$id];
                }
                $newOutput[] = $output[$id];
            }
        }

        return $newOutput;
    }

    public function describe($elementType)
    {
        $app_strings = VTWS_PreserveGlobal::getGlobal('app_strings');
        $current_user = vtws_preserveGlobal('current_user', $this->user);;

        $label = (isset($app_strings[$elementType])) ? $app_strings[$elementType] : $elementType;
        $createable = (strcasecmp(isPermitted($elementType, EntityMeta::$CREATE), 'yes') === 0) ? true : false;
        $updateable = (strcasecmp(isPermitted($elementType, EntityMeta::$UPDATE), 'yes') === 0) ? true : false;
        $deleteable = $this->meta->hasDeleteAccess();
        $retrieveable = $this->meta->hasReadAccess();
        $fields = $this->getModuleFields();

        return [
            'label'           => $label,
            'name'            => $elementType,
            'createable'      => $createable,
            'updateable'      => $updateable,
            'deleteable'      => $deleteable,
            'retrieveable'    => $retrieveable,
            'fields'          => $fields,
            'idPrefix'        => $this->meta->getEntityId(),
            'isEntity'        => $this->isEntity,
            'allowDuplicates' => $this->meta->isDuplicatesAllowed(),
            'labelFields'     => $this->meta->getNameFields()
        ];
    }

    public function describePartial($elementType, $fields = null)
    {
        $this->partialDescribeFields = $fields;
        $result = $this->describe($elementType);
        $this->partialDescribeFields = null;

        return $result;
    }

    function getModuleFields()
    {
        $fields = [];
        $moduleFields = $this->meta->getModuleFields();
        foreach ($moduleFields as $fieldName => $webserviceField) {
            if (((int)$webserviceField->getPresence()) == 1) {
                continue;
            }
            array_push($fields, $this->getDescribeFieldArray($webserviceField));
        }
        array_push($fields, $this->getIdField($this->meta->getObectIndexColumn()));

        return $fields;
    }

    function getDescribeFieldArray($webserviceField)
    {
        $default_language = VTWS_PreserveGlobal::getGlobal('default_language');

        $fieldLabel = getTranslatedString($webserviceField->getFieldLabelKey(), $this->meta->getTabName());

        $typeDetails = [];
        if (!is_array($this->partialDescribeFields)) {
            $typeDetails = $this->getFieldTypeDetails($webserviceField);
        } elseif (in_array($webserviceField->getFieldName(), $this->partialDescribeFields)) {
            $typeDetails = $this->getFieldTypeDetails($webserviceField);
        }

        //set type name, in the type details array.
        $typeDetails['name'] = $webserviceField->getFieldDataType();
        //Reference module List is missing in DescribePartial api response
        if ($typeDetails['name'] === "reference") {
            $typeDetails['refersTo'] = $webserviceField->getReferenceList();
        }
        $editable = $this->isEditable($webserviceField);

        $describeArray = [
            'name'      => $webserviceField->getFieldName(),
            'label'     => $fieldLabel,
            'mandatory' => $webserviceField->isMandatory(),
            'type'      => $typeDetails,
            'isunique'  => $webserviceField->isUnique(),
            'nullable'  => $webserviceField->isNullable(),
            'editable'  => $editable
        ];
        if ($webserviceField->hasDefault()) {
            $describeArray['default'] = $webserviceField->getDefault();
        }

        return $describeArray;
    }

    function getMeta()
    {
        return $this->meta;
    }

    function getField($fieldName)
    {
        $moduleFields = $this->meta->getModuleFields();

        return $this->getDescribeFieldArray($moduleFields[$fieldName]);
    }

    /**
     * Function to get the file content
     *
     * @param type $id
     *
     * @return type
     * @throws WebServiceException
     */
    public function file_retrieve($crmid, $elementType, $attachmentId = false)
    {
        $ids = vtws_getIdComponents($crmid);
        $crmid = $ids[1];
        $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $elementType);
        if ($attachmentId) {
            $attachmentDetails = $recordModel->getFileDetails($attachmentId);
        } else {
            $attachmentDetails = $recordModel->getFileDetails();
        }
        $fileDetails = [];
        if (!empty ($attachmentDetails)) {
            if (is_array(current(($attachmentDetails)))) {
                foreach ($attachmentDetails as $key => $attachment) {
                    $fileDetails[$key] = vtws_filedetails($attachment);
                }
            } elseif (is_array($attachmentDetails)) {
                $fileDetails[] = vtws_filedetails($attachmentDetails);
            }
        }

        return $fileDetails;
    }
}