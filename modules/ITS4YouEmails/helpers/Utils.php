<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_Utils_Helper
{
    /**
     * @param int $templateId
     * @return bool
     */
    public static function isTemplateForListView($templateId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM vtiger_emakertemplates WHERE templateid=? AND deleted=? AND is_listview=?',
            array($templateId, 0, 1)
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * @param int $sendingId
     * @return array
     */
    public static function sendEmails($sendingId)
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT crmid FROM vtiger_crmentity 
            INNER JOIN its4you_emails ON its4you_emails.its4you_emails_id=vtiger_crmentity.crmid 
            WHERE setype=? AND deleted=? AND sending_id=?';
        $result = $adb->pquery($sql,
            ['ITS4YouEmails', 0, $sendingId]
        );

        $sendingResult = array(
            'total' => (int)$adb->num_rows($result),
            'error' => 0,
            'sent' => 0,
	        'error_message' => '',
        );

        while ($row = $adb->fetchByAssoc($result)) {
            /** @var ITS4YouEmails_Record_Model $recordModel */
            $recordModel = Vtiger_Record_Model::getInstanceById($row['crmid'], 'ITS4YouEmails');

            if ($recordModel) {
                $recordModel->send();

                if ($recordModel->get('email_flag') === $recordModel::$FLAG_ERROR) {
	                $sendingResult['error_message'] .= '<br>' . $recordModel->get('result');
                    $sendingResult['error']++;
                } elseif ($recordModel->get('email_flag') === $recordModel::$FLAG_SENT) {
                    $sendingResult['sent']++;
                }
            }
        }

        return $sendingResult;
    }

    public static function getSendingId()
    {
        return PearDatabase::getInstance()->getUniqueID('its4you_emails_sending');
    }

    /**
     * @param array $templateIds
     * @return array
     * @throws Exception
     */
    public static function validatePDFTemplates($templateIds, $templateInfo = [])
    {
        $templates = array_flip($templateIds);
        /** @var PDFMaker_Module_Model $moduleModel */
        $moduleModel = Vtiger_Module_Model::getInstance('PDFMaker');
        $pdfMakerModel = new PDFMaker_PDFMaker_Model();

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT templateid as template_id, description, filename, module FROM vtiger_pdfmaker WHERE templateid IN (' . generateQuestionMarks($templateIds) . ')',
            $templateIds
        );

        while ($row = $adb->fetchByAssoc($result)) {
            $templateId = $row['template_id'];

            if ($moduleModel->CheckTemplatePermissions($row['module'], $templateId, false)) {
                if(!empty($templateInfo) && method_exists($pdfMakerModel, 'getPreparedName')) {
                    $templateName = $pdfMakerModel->getPreparedName((array)$templateInfo['records'], [$templateId], $templateInfo['module'], $templateInfo['language']);
                } else {
                    $templateName = $row['filename'];
                }

                $templates[$templateId] = $templateName;
            } else {
                unset($templates[$templateId]);
            }
        }

        return $templates;
    }

    /**
     * @throws Exception
     */
    public static function getSavedFromField($templateId)
    {
		if(empty($templateId) && !vtlib_isModuleActive('EMAILMaker')) {
			return '';
		}

        $current_user = Users_Record_Model::getCurrentUserModel();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT fieldname FROM vtiger_emakertemplates_default_from WHERE templateid=? AND userid=?',
            array($templateId, $current_user->getId())
        );

        return $adb->query_result($result, 0, 'fieldname');
    }

    /**
     * @param array $fromEmails
     * @param string $savedDefaultFrom
     * @return string
     * @throws Exception
     */
    public static function getOrganizationFromEmails(&$fromEmails, $savedDefaultFrom = '')
    {
        $fromEmailField = ITS4YouEmails_Record_Model::getVtigerFromEmailField();
        $selectedDefaultFrom = '';

        if (!empty($fromEmailField)) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery('select * from vtiger_organizationdetails where organizationname!=?', array(''));

            while ($row = $adb->fetchByAssoc($result)) {
                $fromKey = 'a::' . $row['organizationname'];
                $fromEmails[$fromKey] = $row['organizationname'] . ' &lt;' . $fromEmailField . '&gt;';

                if ('0_organization_email' === $savedDefaultFrom) {
                    $selectedDefaultFrom = $fromKey;
                }
            }
        }

        return $selectedDefaultFrom;
    }

    /**
     * @param array $fromEmails
     * @param string $savedDefaultFrom
     * @return string
     */
    public static function getUserFromEmails(&$fromEmails, $savedDefaultFrom = '')
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT fieldname, fieldlabel FROM vtiger_field WHERE tabid=? AND uitype IN ( ? , ? ) ORDER BY fieldid',
            array(29, 104, 13)
        );
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUser->getId();
        $currentUserModel = Users_Record_Model::getInstanceById($currentUserId, 'Users');
        $selectedDefaultFrom = '';

        while ($row = $adb->fetchByAssoc($result)) {
            $fieldName = $row['fieldname'];

            if (!$currentUserModel->isEmpty($fieldName)) {
                $fromKey = $fieldName . '::' . $currentUserId;
                $fromEmails[$fromKey] = $currentUserModel->getName() . ' &lt;' . $currentUserModel->get($fieldName) . '&gt;';

                if ('1_' . $fieldName === $savedDefaultFrom) {
                    $selectedDefaultFrom = $fromKey;
                }
            }
        }

        return $selectedDefaultFrom;
    }

    public static function updateShorUrlData($body)
    {
        $regex = '/shorturl[.]php[?]id[=][0-9a-z]*[.][0-9]*/m';

        preg_match_all($regex, $body, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            $body = str_replace($match[0], $match[0] . '&fromcrm=1', $body);
        }

        return $body;
    }
}