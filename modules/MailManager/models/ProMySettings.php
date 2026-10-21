<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/**
 * My Settings of a user — the own mailboxes (name, folders, SMTP, reply
 * style) and the own labels; rendered as a page or inside the inbox
 */
class MailManager_ProMySettings_Model
{
    /** Tab key => template pane suffix (mmMyTab…) */
    public const TABS = [
        'general' => 'General',
        'folders' => 'Folders',
        'smtp' => 'Smtp',
        'style' => 'Style',
        'signature' => 'Signature',
        'labels' => 'Labels',
        'learning' => 'Learning',
        'sharing' => 'Sharing',
        'history' => 'History',
    ];

    /**
     * Everything the My Settings template shows. A user manages the own
     * mailboxes (a shared one somebody else runs stays with the admin); the
     * admin opens any mailbox the same way from Settings ($adminMode) and
     * also sets what only the admin may: the type, the members of a shared
     * mailbox and the trusted certificate
     *
     * @param int $userId
     * @param int $mailboxId requested mailbox, 0 = the first one
     * @param bool $adminMode the admin's mailbox detail in Settings
     * @return array template variables
     */
    public static function getViewData($userId, $mailboxId, $adminMode = false)
    {
        $mailboxes = self::getMailboxes($userId, $adminMode);
        $selected = $mailboxes[0] ?? null;
        $hasPersonal = false;
        foreach ($mailboxes as $mailbox) {
            if ((int)$mailbox['mailboxid'] === (int)$mailboxId) {
                $selected = $mailbox;
            }
            // own labels work in the personal mailboxes only
            $hasPersonal = $hasPersonal || $mailbox['mailbox_type'] === 'personal';
        }
        $isPersonal = $selected && $selected['mailbox_type'] === 'personal';
        // the labels shown belong to the mailbox owner — the admin edits them for the owner
        $ownerId = $adminMode && $selected ? (int)$selected['owner_userid'] : (int)$userId;

        // what the mailbox picked above learned — the owner sees the
        // personal ones, the admin any mailbox
        $learningMailboxIds = $selected && ($isPersonal || $adminMode) ? [(int)$selected['mailboxid']] : [];

        $data = [
            'ADMIN_MODE' => $adminMode,
            'MAILBOXES' => $mailboxes,
            'SELECTED' => $selected,
            'HAS_PERSONAL' => $hasPersonal,
            'CAN_MANAGE' => $isPersonal || $adminMode,
            'SHOW_LEARNING' => $hasPersonal || $adminMode,
            'BACK_URL' => $adminMode
                ? 'index.php?module=MailManager&parent=Settings&view=Index'
                : 'index.php?module=MailManager&view=List&app=TOOLS',
            'MY_LABELS' => !$adminMode || $isPersonal ? Settings_MailManager_Label_Model::getOwn($ownerId) : [],
            'SHOW_OWN_LABELS' => $adminMode ? $isPersonal : $hasPersonal,
            // the common labels as the owner set them up for the personal mailboxes
            'DEFAULT_LABELS' => ($adminMode ? $isPersonal : $hasPersonal)
                ? Settings_MailManager_Label_Model::getGlobalForUser($ownerId)
                : [],
            // the user's own mail signature (the core user field) — never somebody else's
            'SHOW_SIGNATURE' => !$adminMode,
            // the user's own browser notifications of new mail — never somebody else's
            'SHOW_NOTIFICATIONS' => !$adminMode,
            'NOTIFY_ENABLED' => !$adminMode && MailManager_ProNotifier_Model::isEnabled((int)$userId),
            // the list tab the user's inbox opens with — never somebody else's
            'SHOW_LIST_SETTINGS' => !$adminMode,
            'LIST_TABS' => MailManager_ProThreadList_Model::LIST_FILTERS,
            'DEFAULT_LIST_TAB' => $adminMode ? 'all' : MailManager_ProThreadList_Model::getDefaultListTab((int)$userId),
            // the user's own "Undo" time after Send — never somebody else's
            'SHOW_SENDING' => !$adminMode,
            'SEND_DELAYS' => MailManager_ProUserSetting_Model::SEND_DELAYS,
            'SEND_DELAY' => $adminMode ? 0 : (int)MailManager_ProUserSetting_Model::get(
                (int)$userId,
                MailManager_ProUserSetting_Model::SEND_DELAY,
                '0'
            ),
            // the user's own knowledge files — the drafts he writes answer from them first
            'SHOW_KNOWLEDGE' => !$adminMode && MailManager_ProKnowledge_Model::isEnabled(),
            'KNOWLEDGE_FILES_JSON' => !$adminMode && MailManager_ProKnowledge_Model::isEnabled()
                ? json_encode(MailManager_ProKnowledge_Model::getFiles(
                    MailManager_ProKnowledge_Model::SCOPE_PERSONAL,
                    $userId
                ), JSON_UNESCAPED_UNICODE)
                : '[]',
            'SIGNATURE_HTML' => $adminMode ? '' : self::getSignature(),
            'LABEL_RULES' => $adminMode
                ? MailManager_ProLabelRule_Model::getMailboxRules($learningMailboxIds)
                : MailManager_ProLabelRule_Model::getUserRules($userId, $learningMailboxIds),
            'LABEL_PALETTE' => Settings_MailManager_Label_Model::PALETTE,
            'SUGGESTED_OPTIONS' => Settings_MailManager_Label_Model::getSuggestedOptions(),
            'LABEL_STRINGS_JSON' => Settings_MailManager_Label_Model::getEditorStringsJson(),
            'SPAM_ENABLED' => MailManager_ProSpamGuard_Model::isEnabled(),
            'SPAM_RULES' => MailManager_ProSpamGuard_Model::getRules($learningMailboxIds),
            'SPAM_TOTAL' => MailManager_ProSpamGuard_Model::getMovedTotal($learningMailboxIds),
            'SPAM_ACTIVATION_HITS' => MailManager_ProSpamGuard_Model::ACTIVATION_HITS,
        ];
        if ($selected) {
            $selectedId = (int)$selected['mailboxid'];
            $data += [
                'FOLDERS' => MailManager_ProFolder_Model::getFolders($selectedId),
                'IS_PERSONAL' => $isPersonal,
                'SMTP_MODE' => $selected['smtp_mode'],
                'SMTP_SERVER' => (string)$selected['smtp_server'],
                'SMTP_PORT' => $selected['smtp_port'] ? (int)$selected['smtp_port'] : '',
                'SMTP_ENCRYPTION' => (string)$selected['smtp_encryption'],
                'SMTP_USERNAME' => (string)$selected['smtp_username'],
                'SMTP_HAS_PASSWORD' => (string)$selected['smtp_password'] !== '',
                'STYLE_PROFILE' => MailManager_ProAiAssist_Model::getStyleProfile($selectedId),
                'HISTORY' => MailManager_ProHistory_Model::getPage($selectedId),
                'HISTORY_MAILBOX_ID' => $selectedId,
                'HISTORY_ACTIONS' => MailManager_ProHistory_Model::ACTIONS,
                'DEPUTIES' => MailManager_ProSharing_Model::getDeputies($selectedId),
                'DEPUTY_OPTIONS' => MailManager_ProSharing_Model::getUserOptions((int)$selected['owner_userid']),
                'DATE_FORMAT' => Users_Record_Model::getCurrentUserModel()->get('date_format'),
            ];
        }
        if ($selected && $adminMode) {
            $data += [
                'OWNER_NAME' => trim((string)getUserFullName((int)$selected['owner_userid'])),
                'USERS_LIST' => get_user_array(false),
                'MEMBERS_SELECTED' => Settings_MailManager_Mailbox_Model::getAccessMembers($selectedId),
                'IMAP_CERT_FINGERPRINT' => (string)$selected['imap_cert_fingerprint'],
            ];
        }

        return $data;
    }

    /**
     * @param int $userId
     * @param bool $adminMode all mailboxes instead of the user's own
     * @return array mailbox rows
     */
    private static function getMailboxes($userId, $adminMode)
    {
        $adb = PearDatabase::getInstance();
        $result = $adminMode
            ? $adb->pquery('SELECT * FROM df_mailmanager_mailbox ORDER BY name', [])
            : $adb->pquery(
                "SELECT * FROM df_mailmanager_mailbox
                 WHERE owner_userid = ? AND mailbox_type = 'personal' ORDER BY is_default DESC, name",
                [$userId]
            );
        $mailboxes = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $mailboxes[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $mailboxes;
    }

    /**
     * The mailbox the current user may manage in these settings — the own
     * one, any mailbox for the admin
     *
     * @param int $mailboxId
     * @return array|null mailbox row
     */
    public static function getManagedMailbox($mailboxId)
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $mailbox = Settings_MailManager_Mailbox_Model::getById((int)$mailboxId);
        // a mailbox the admin turned into a shared one is no longer its former owner's to manage
        $isOwn = $mailbox && $mailbox['mailbox_type'] === 'personal'
            && (int)$mailbox['owner_userid'] === (int)$user->getId();
        if (!$mailbox || (!$user->isAdminUser() && !$isOwn)) {
            return null;
        }

        return $mailbox;
    }

    /**
     * Scope of the learned rules the current user may delete
     *
     * @return int[]|null the own personal mailboxes, null = every mailbox (the admin)
     */
    public static function getManagedMailboxIds()
    {
        $user = Users_Record_Model::getCurrentUserModel();

        return $user->isAdminUser() ? null : self::getOwnPersonalMailboxIds((int)$user->getId());
    }

    /**
     * @return string the current user's mail signature as HTML (the core
     *                user field "Signature" of My Preferences), '' when none
     */
    public static function getSignature()
    {
        $signature = decode_html((string)Users_Record_Model::getCurrentUserModel()->get('signature'));

        return trim(strip_tags($signature, '<img>')) === '' ? '' : $signature;
    }

    /**
     * Saves the own personal mailbox of a user (My settings): its name, folders, the
     * default flag and the outgoing server.
     *
     * @param int    $mailboxId personal mailbox the user owns
     * @param string $name      mailbox name, not empty
     * @param array  $input     folders (JSON), is_default, smtp_mode, smtp_server, smtp_port,
     *                          smtp_encryption, smtp_username, smtp_password (raw)
     *
     * @return void
     * @throws Exception when the mailbox cannot be saved
     */
    public static function saveOwnMailbox(int $mailboxId, string $name, array $input): void
    {
        Settings_MailManager_Mailbox_Model::update($mailboxId, [
            'name' => $name,
            'mailbox_type' => 'personal',
            'shared_with' => [],
            'folders' => json_decode($input['folders'], true) ?: [],
            'is_default' => (int)$input['is_default'] === 1,
            'smtp_mode' => $input['smtp_mode'] === 'custom' ? 'custom' : 'crm',
            'smtp_server' => $input['smtp_server'],
            'smtp_port' => $input['smtp_port'],
            'smtp_encryption' => $input['smtp_encryption'],
            'smtp_username' => $input['smtp_username'],
            'smtp_password' => $input['smtp_password'],
        ]);
    }

    /**
     * Saves the e-mail signature of a user — purified HTML of the signature editor.
     *
     * @param int    $userId
     * @param string $html
     *
     * @return void
     * @throws Exception when the user cannot be saved
     */
    public static function saveSignature(int $userId, string $html): void
    {
        $recordModel = Users_Record_Model::getInstanceById($userId, 'Users');
        $recordModel->set('mode', 'edit');
        $recordModel->set('signature', MailManager_ProMailText_Model::purifyHtml($html));
        $recordModel->save();
    }

    /**
     * The user whose labels a label request changes: the owner of the personal mailbox
     * the admin edits in Settings, otherwise the current user.
     *
     * @param int $mailboxId mailbox of the request, 0 = none
     *
     * @return int
     */
    public static function getLabelOwnerId(int $mailboxId): int
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $mailbox = $mailboxId && $user->isAdminUser()
            ? Settings_MailManager_Mailbox_Model::getById($mailboxId)
            : null;

        return $mailbox && $mailbox['mailbox_type'] === 'personal'
            ? (int)$mailbox['owner_userid']
            : (int)$user->getId();
    }

    /**
     * @param int $userId
     * @return int[] the personal mailboxes the user owns
     */
    public static function getOwnPersonalMailboxIds($userId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT mailboxid FROM df_mailmanager_mailbox WHERE owner_userid = ? AND mailbox_type = 'personal'",
            [$userId]
        );
        $ids = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $ids[] = (int)$adb->query_result($result, $i, 'mailboxid');
        }

        return $ids;
    }

    /**
     * The tabs of the mailbox My Settings opens with (the user's default
     * own mailbox): a personal one has all of them (sharing included), a
     * shared one the reply style and the activity history; the own labels need any personal mailbox
     *
     * @param int $userId
     * @return array of ['key', 'label'], only Sending when the user owns no mailbox
     */
    public static function getTabs($userId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT mailbox_type FROM df_mailmanager_mailbox
             WHERE owner_userid = ? ORDER BY is_default DESC, name',
            [$userId]
        );
        $types = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $types[] = $adb->query_result($result, $i, 'mailbox_type');
        }
        if (!$types) {
            // the user's own sending options apply to the shared mailboxes too
            return [['key' => 'sending', 'label' => vtranslate('LBL_SENDING', 'Settings:MailManager')]];
        }

        $keys = [];
        if ($types[0] === 'personal') {
            $keys = ['general', 'folders', 'smtp', 'sharing'];
        }
        $keys[] = 'style';
        $keys[] = 'signature';
        $keys[] = 'notifications';
        $keys[] = 'list';
        if (MailManager_ProKnowledge_Model::isEnabled()) {
            $keys[] = 'knowledge';
        }
        if (in_array('personal', $types, true)) {
            $keys[] = 'labels';
            $keys[] = 'learning';
        }
        $keys[] = 'history';
        $keys[] = 'sending';

        $labels = [
            'general' => 'LBL_TAB_GENERAL',
            'folders' => 'LBL_FOLDERS_TO_SCAN',
            'smtp' => 'LBL_SMTP_MODE',
            'style' => 'LBL_STYLE_PROFILE',
            'signature' => 'LBL_SIGNATURE',
            'notifications' => 'LBL_NOTIFICATIONS',
            'list' => 'LBL_MAIL_LIST',
            'knowledge' => 'LBL_KNOWLEDGE',
            'labels' => 'LBL_EMAIL_LABELS',
            'learning' => 'LBL_LEARNING',
            'history' => 'LBL_ACTIVITY_HISTORY',
            'sending' => 'LBL_SENDING',
            'sharing' => 'LBL_SHARING',
        ];
        $tabs = [];
        foreach ($keys as $key) {
            $tabs[] = ['key' => $key, 'label' => vtranslate($labels[$key], 'Settings:MailManager')];
        }

        return $tabs;
    }
}
