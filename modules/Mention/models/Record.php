<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Mention_Record_Model extends Vtiger_Record_Model
{
    protected $parent;
    protected $comment;
    protected $mentioned = array();

    public function setParent($value)
    {
        $this->parent = $value;
    }

    public function setComment($value)
    {
        $this->comment = $value;
    }

    /**
     * @return array
     */
    public function getMentionedUsers()
    {
        $recordModel = $this->getParentRecord();

        /** @var Mention_Module_Model $mentionModule */
        $mentionModule = Mention_Module_Model::getInstance('Mention');
        $mentionUsers = $mentionModule->getMentionUsers($recordModel->getModuleName(), $recordModel->getId());

        $comment = decode_html($this->comment);
        preg_match_all('/@([^\s]+)/u', $comment, $matches);
        $commentUsers = $matches[1];

        $emailUsers = [];

        foreach ($mentionUsers as $user) {
            if (!in_array($user['username'], $commentUsers)) {
                continue;
            }

            $userId = $user['id'];

            if ('group' === $user['type']) {
                $groupRecord = Settings_Groups_Record_Model::getInstance($userId);

                foreach ($groupRecord->getUsersList() as $userId => $userRecord) {
                    $emailUsers[$userId] = $userRecord;
                }
            } else {
                $emailUsers[$userId] = Users_Record_Model::getInstanceById($userId, 'Users');
            }
        }

        return $emailUsers;
    }

    /**
     * @return string
     */
    public function getParentModuleName()
    {
        return $this->getParentRecord()->getModuleName();
    }

    /**
     * @return object
     */
    public function getParentRecord()
    {
        return Vtiger_Record_Model::getInstanceById($this->parent);
    }

    public function save(): void
    {
    }

    public function sendEmail()
    {
        global $log;
        $log->info("Mention:: Send->Started");

        $parentRecord = $this->getParentRecord();
        $emailInfo = $this->getEmailInfo();

        foreach ($emailInfo as $userId => $userEmail) {
            /** @var $email ITS4YouEmails_Record_Model */
            $email = Vtiger_Record_Model::getCleanInstance('ITS4YouEmails');
            $email->set('subject', $this->getEmailSubject());
            $email->set('body', $this->getEmailMessage($userId));
            $email->set('to_email', $userEmail);
            $email->set('to_email_ids', implode('|', [$userId, $userEmail, 'Users']));
            $email->set('related_to', $parentRecord->getId());
            $email->set('email_flag', 'SAVED');
            $email->set('assigned_user_id', $parentRecord->get('assigned_user_id'));
            $email->save();

            $email = Vtiger_Record_Model::getInstanceById($email->getId(), 'ITS4YouEmails');
            $email->send();
        }

        $log->info("Mention:: Send->Ended");
    }

    public function getEmailSubject()
    {
        $userRecord = Users_Record_Model::getCurrentUserModel();
        $parentRecord = $this->getParentRecord();

        return $userRecord->getName() . ' ' . vtranslate('mentioned you on', $this->getModuleName()) . ' ' . $parentRecord->getName();
    }

    public function getCommentMessage()
    {
        /**
         * @var Mention_Module_Model $moduleModel
         * @var Settings_Groups_Record_Model $groupRecord
         */
        $parentModule = $this->getParentModuleName();
        $moduleModel = $this->getModule();
        $comment = htmlspecialchars(decode_html($this->comment), ENT_QUOTES, 'UTF-8');
        $users = $this->getMentionedUsers();

        foreach ($users as $user) {
            $username = htmlspecialchars($moduleModel->formatUserName($user->get('user_name')), ENT_QUOTES, 'UTF-8');
            $name = htmlspecialchars(decode_html($user->getName()), ENT_QUOTES, 'UTF-8');
            $comment = str_replace(sprintf('@%s', $username), sprintf('<b>@%s</b>', $name), $comment);
        }

        $groups = $moduleModel->getAccessibleGroups($parentModule);

        foreach ($groups as $groupId => $group) {
            $name = htmlspecialchars(decode_html($group->getName()), ENT_QUOTES, 'UTF-8');
            $comment = str_replace(sprintf('@%s', $moduleModel->getGroupName($group)), sprintf('<b>@%s</b>', $name), $comment);
        }

        return nl2br($comment);
    }

    /**
     * @param int $userId
     * @return string
     */
    public function getEmailMessage($userId = 0)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        /** @var Vtiger_Record_Model $parentRecord */
        $parentRecord = $this->getParentRecord();
        $moduleName = $this->getModuleName();
        $mentionedMessage = vtranslate('LBL_MENTIONED_YOU', $moduleName);
        $clickMessage = vtranslate('LBL_CLICK_HERE', $moduleName);

        if ($userId) {
            $userRecordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');

            if ($userRecordModel) {
                $language = $userRecordModel->get('language');
                $mentionedMessage = Vtiger_Language_Handler::getLanguageTranslatedString($language, 'LBL_MENTIONED_YOU', $moduleName);
                $clickMessage = Vtiger_Language_Handler::getLanguageTranslatedString($language, 'LBL_CLICK_HERE', $moduleName);
            }
        }

        if(empty($mentionedMessage)) {
            $mentionedMessage = 'mentioned you in a comment.';
        }

        if(empty($clickMessage)) {
            $clickMessage = 'Please click here to view the record.';
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('RECORD_TITLE', $parentRecord->getName());
        $viewer->assign('RECORD_MESSAGE', $this->getCommentMessage());
        $viewer->assign('RECORD_URL', Mention_Module_Model::getSiteUrl() . $parentRecord->getDetailViewUrl());
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('RECORD_MODEL', $this);
        $viewer->assign('COMPANY', Vtiger_CompanyDetails_Model::getInstanceById());
        $viewer->assign('COMPANY_LOGO', $this->getCompanyImage());
        $viewer->assign('CURRENT_USER', $currentUser);
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('MESSAGE_MENTIONED_YOU', $mentionedMessage);
        $viewer->assign('MESSAGE_CLICK_HERE', $clickMessage);

        return $viewer->view('Email.tpl', $this->getModuleName(), true);
    }

    public function getCompanyImage() {
        $company = Vtiger_CompanyDetails_Model::getInstanceById();
        $logo = $company->getLogo();

        return ($logo && is_file($logo->get('imagepath'))) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo->get('imagepath'))) : '';
    }

    public function getEmailInfo()
    {
        $users = $this->getMentionedUsers();
        $emails = array();

        foreach ($users as $user) {
            $emails[$user->getId()] = $user->get('email1');
        }

        return $emails;
    }
}
