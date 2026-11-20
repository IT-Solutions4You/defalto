<?php
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouMention license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Mention_Module_Model extends Vtiger_Module_Model
{
    public static $mobileIcon = 'mention';

    public static function getSiteUrl()
    {
        return rtrim(vglobal('site_URL'), '/') . '/';
    }

    public function isMentionContextPermitted($sourceModule, int $sourceRecord): bool
    {
        if (!is_string($sourceModule) || !preg_match('/\A[A-Za-z][A-Za-z0-9]*\z/', $sourceModule)) {
            return false;
        }

        if (!vtlib_isModuleActive($sourceModule) || !Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $sourceRecord)) {
            return false;
        }

        if (!$sourceRecord) {
            return true;
        }

        return $this->getMentionRecordModule($sourceModule, $sourceRecord) !== null && isRecordExists($sourceRecord);
    }

    /**
     * @param string $sourceModule
     * @return array
     */
    public function getMentionUsers($sourceModule, $sourceRecord = 0)
    {
        foreach (array_keys($_SESSION['Mention'] ?? []) as $key) {
            if (strpos($key, 'mention_users_') === 0) {
                unset($_SESSION['Mention'][$key]);
            }
        }

        $ownerIds = [];

        if ($sourceRecord) {
            $recordModule = $this->getMentionRecordModule($sourceModule, $sourceRecord);

            if (!$recordModule || !isRecordExists($sourceRecord)) {
                return [];
            }

            $ownerIds = $this->fetchRecordOwnerIds($recordModule, $sourceRecord);
        }

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $accessibleIds = array_keys((array)$currentUser->getAccessibleUsers());
        $userDetails = $this->fetchMentionUserDetails($accessibleIds, $ownerIds);
        $users = [];

        foreach ($accessibleIds as $userId) {
            if (isset($userDetails[$userId])) {
                $users[$userId] = $userDetails[$userId];
            }
        }

        foreach ($this->getAccessibleGroups($sourceModule) as $groupId => $groupModel) {
            $users[$groupId] = [
                'username' => $this->formatUserName($this->getGroupName($groupModel)),
                'name' => decode_html($groupModel->getName()),
                'image' => '',
                'id' => $groupId,
                'type' => 'group',
            ];
        }

        foreach ($ownerIds as $userId) {
            if (isset($userDetails[$userId])) {
                $users[$userId] = $userDetails[$userId];
            }
        }

        return $this->selectMentionUsers($sourceModule, $sourceRecord, array_values($users));
    }

    public function getMentionRecordModule($sourceModule, $sourceRecord)
    {
        $recordModule = getSalesEntityType($sourceRecord);

        if ($recordModule && ($recordModule === $sourceModule || ($sourceModule === 'Calendar' && $recordModule === 'Events'))) {
            return $recordModule;
        }

        return null;
    }

    private function fetchRecordOwnerIds($recordModule, $sourceRecord)
    {
        $module = Vtiger_Module_Model::getInstance($recordModule);
        $fields = $module ? $module->getFieldsByType('owner') : [];

        if (!$fields) {
            return [];
        }

        $entity = CRMEntity::getInstance($recordModule);
        $columns = [];

        foreach ($fields as $field) {
            $table = $field->get('table');

            if (isset($entity->tab_name_index[$table])) {
                $columns[$table][$field->getName()] = $field->get('column');
            }
        }

        $db = PearDatabase::getInstance();
        $values = [];

        foreach ($columns as $table => $tableColumns) {
            $index = $entity->tab_name_index[$table];
            $columnList = implode(', ', array_unique($tableColumns));
            $result = $db->pquery("SELECT $columnList FROM $table WHERE $index = ? LIMIT 1", [$sourceRecord]);

            if ($db->num_rows($result)) {
                $row = $db->query_result_rowdata($result);

                foreach ($tableColumns as $fieldName => $column) {
                    $values[$fieldName] = (int)$row[$column];
                }
            }
        }

        $ids = [];

        foreach ($fields as $field) {
            $id = $values[$field->getName()] ?? 0;

            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function fetchMentionUserDetails(array $accessibleIds, array $ownerIds)
    {
        global $site_URL;

        $ids = array_values(array_unique(array_merge($accessibleIds, $ownerIds)));

        if (!$ids) {
            return [];
        }

        $db = PearDatabase::getInstance();
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $result = $db->pquery(
            "SELECT u.id, u.user_name, u.first_name, u.last_name, u.deleted, u.status,
                    a.attachmentsid, a.name AS attachmentname
             FROM vtiger_users u
             LEFT JOIN vtiger_salesmanattachmentsrel r ON r.smid = u.id
             LEFT JOIN vtiger_attachments a ON a.attachmentsid = r.attachmentsid
             WHERE u.id IN ($placeholders)
             ORDER BY u.id, a.attachmentsid",
            $ids
        );

        if (!$result) {
            throw new Exception('Unable to load Mention user details.');
        }

        $accessible = array_fill_keys($accessibleIds, true);
        $usersModule = Vtiger_Module_Model::getInstance('Users');
        $users = [];

        while ($row = $db->fetchByAssoc($result)) {
            $id = (int)$row['id'];

            if (isset($users[$id]) || !$this->isMentionUserAvailable($row, isset($accessible[$id]))) {
                continue;
            }

            $user = new Users_Record_Model();
            $user->setData($row)->setModuleFromInstance($usersModule);
            $image = empty($row['attachmentname'])
                ? self::getSiteUrl() . 'layouts/d1/modules/Mention/resources/User.png'
                : Core_Storage_Model::getInstance()->getAttachmentPublicUrl((int)$row['attachmentsid'], decode_html($row['attachmentname']));

            $users[$id] = [
                'username' => $this->formatUserName($row['user_name']),
                'name' => decode_html($user->getName()),
                'image' => $image,
                'id' => $id,
                'type' => 'user',
            ];
        }

        return $users;
    }

    private function isMentionUserAvailable(array $user, bool $accessible): bool
    {
        return $accessible || ((int)$user['deleted'] === 0 && $user['status'] === 'Active');
    }

    private function selectMentionUsers($sourceModule, $sourceRecord, array $users)
    {
        if (!is_string($sourceModule) || !preg_match('/\A[A-Za-z][A-Za-z0-9]*\z/', $sourceModule)) {
            return $users;
        }

        $selectorClass = 'Mention_' . $sourceModule . 'Users_Model';

        if (class_exists($selectorClass)) {
            $selector = new $selectorClass();
            return array_values($selector->selectUsers($sourceModule, (int)$sourceRecord, $users));
        }

        return $users;
    }

    public function formatUserName($value)
    {
        return str_replace(' ', '_', $value);
    }

    /**
     * @param string $sourceModule
     * @return array
     */
    public function getAccessibleGroups($sourceModule)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $accessibleGroups = (array)$currentUserModel->getAccessibleGroups();

        if (!$accessibleGroups) {
            return [];
        }

        $allGroups = Settings_Groups_Record_Model::getAll();
        $groups = [];

        foreach ($accessibleGroups as $groupId => $groupName) {
            if (isset($allGroups[$groupId])) {
                $groups[$groupId] = $allGroups[$groupId];
            }
        }

        return $groups;
    }

    /**
     * @param object $groupModel
     * @return string
     */
    public function getGroupName($groupModel)
    {
        return $this->replaceSpecialChars($groupModel->getName(), '') . $groupModel->getId();
    }

    /**
     * @param string $value
     * @param string $replace
     * @return string
     */
    public function replaceSpecialChars($value, $replace = '')
    {
        return preg_replace('/[^a-zA-Z0-9]/', $replace, $value);
    }

    public function sendEmailToMentioned($parentId, $comment)
    {
        /** @var $mentionRecord Mention_Record_Model */
        $mentionRecord = Vtiger_Record_Model::getCleanInstance('Mention');
        $mentionRecord->setParent($parentId);
        $mentionRecord->setComment($comment);
        $mentionRecord->sendEmail();
    }

    public function getDatabaseTables()
    {
        return [];
    }

    public function getPicklistFields()
    {
        return [];
    }
}
