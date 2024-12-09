<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

require_once 'modules/MailManager/MailManager.php';

class MailManager_Relate_Action extends Settings_MailConverter_MailScannerAction_Handler {

    public $recordSource = 'MAIL MANAGER';
    public $moduleName = 'MailManager';

	public function __construct($foractionid = 0) {
	}

    /**
     * @param string $baseModule
     * @param array $attachmentInfo
     * @return Core_Attachment_Model
     * @throws AppException
     */
    public function saveAttachment(string $baseModule, array $attachmentInfo): Core_Attachment_Model
    {
        $attachment = Core_Attachment_Model::getInstance($baseModule);
        $attachment->retrieveDefault($attachmentInfo['filename']);
        $attachment->setType($attachmentInfo['type']);
        $attachment->saveFile($attachmentInfo['data']);

        if ($attachment->validateSaveFile()) {
            $attachment->save();
        }

        return $attachment;
    }

    /**
     *
     * @param MailManager_Message_Model $mailRecord
     * @param int $linkTo
     * @param int $recipientId
     * @return Array
     * @throws AppException
     * @global Users $current_user
     */
    public static function associate(MailManager_Message_Model $mailRecord, int $linkTo, int $recipientId = 0): array
    {
        $instance = new self();

        $moduleName = getSalesEntityType($linkTo);

        $linkFocus = CRMEntity::getInstance($moduleName);
        $linkFocus->retrieve_entity_info($linkTo, $moduleName);
        $linkFocus->id = $linkTo;

        $emailId = $instance->createNewEmail($mailRecord, $linkFocus, array_unique([$linkFocus->id, $recipientId]));

        if (!empty($emailId)) {
            // To add entry in ModTracker for email relation
            relateEntities($linkFocus, $moduleName, $linkTo, 'ITS4YouEmails', $emailId);
        }

        $name = getEntityName($moduleName, $linkTo);

        return self::buildDetailViewLink($moduleName, $linkFocus->id, $name[$linkTo]);
    }

    /**
	 * Returns the information about the Parent
	 * @param String $module
	 * @param Integer $record
	 * @param String $label
	 * @return Array
	 */
    public static function buildDetailViewLink($module, $record, $label)
    {
        if (!empty($record) && isRecordExists($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);

            return [
                'icon' => $recordModel->getModule()->getModuleIcon(),
                'record' => $record,
                'module' => $module,
                'label' => $recordModel->getName(),
                'url' => $recordModel->getDetailViewUrl(),
            ];
        }

        $detailViewUrl = sprintf('index.php?module=%s&view=Detail&record=%s', $module, $record);
        $detailViewLink = sprintf("<a target='_blank' href='%s'>%s</a>", $detailViewUrl, textlength_check($label));

        return [
            'record' => $record,
            'module' => $module,
            'label' => $label,
            'detailviewlink' => $detailViewLink,
            'url' => $detailViewUrl,
        ];
    }

    /**
	 * Returns the related entity for a Mail
	 * @global PearDataBase $db
	 * @param integer $mailuid - Mail Number
	 * @return Array
	 */
	public static function associatedLink($mailuid) {
		$info = MailManager::lookupMailAssociation($mailuid);
		if ($info) {
			return self::getSalesEntityInfo($info['crmid']);
		}
		return false;
	}

	/**
	 * Returns the information about the Parent
	 * @global PearDataBase $db
	 * @param Integer $crmid
	 * @return Array
	 */
	public static function getSalesEntityInfo($crmid) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid=? AND deleted=0", array($crmid));
		if ($db->num_rows($result)) {
			$modulename = $db->query_result($result, 0, 'setype');
			$recordlabels = getEntityName($modulename, array($crmid));
			return self::buildDetailViewLink($modulename, $crmid, $recordlabels[$crmid]);
		}
	}
}