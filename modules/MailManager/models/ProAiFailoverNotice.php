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
 * The e-mail to the administrator about an AI outage (I17): one when it starts, one
 * when it ends — never more for one outage (df_mailmanager_aioutage notified_*), never
 * mail content, never a key. A rejected key or a spent credit says what to check. Sent
 * through ITS4YouEmails to the address of the AI settings or to every active admin.
 */
class MailManager_ProAiFailoverNotice_Model
{
    public const TYPE_START = 'start';
    public const TYPE_END = 'end';

    /**
     * @param string $type     TYPE_*
     * @param string $provider the provider of the outage
     * @param array  $state    the breaker state (since, reason, error, backup, outageid)
     *
     * @return void
     */
    public static function send(string $type, string $provider, array $state): void
    {
        $outageId = (int)($state['outageid'] ?? 0);

        if (!$outageId || !self::claim($outageId, $type)) {
            return;
        }

        $recipients = self::getRecipients();

        if (!$recipients || !vtlib_isModuleActive('ITS4YouEmails')) {
            return;
        }

        $module = 'Settings:MailManager';
        $providerLabel = MailManager_ProAiFailover_Model::getProviderLabel($provider);
        $backupLabel = MailManager_ProAiFailover_Model::getProviderLabel((string)($state['backup'] ?? ''));
        $reason = (string)($state['reason'] ?? '');
        // the time as the admin reads it, not as the user whose call opened the outage
        $since = MailManager_ProAiFailover_Model::formatTime((int)($state['since'] ?? time()), Users::getActiveAdminUser());

        if (self::TYPE_START === $type) {
            $subject = sprintf(vtranslate('LBL_AI_OUTAGE_MAIL_START_SUBJECT', $module), $providerLabel);
            $lines = [
                sprintf(vtranslate('LBL_AI_OUTAGE_MAIL_START_TEXT', $module), $providerLabel, $since, $backupLabel),
                vtranslate('LBL_AI_OUTAGE_REASON_' . strtoupper($reason), $module),
            ];

            // the admin has to act — a key or a credit does not come back by itself
            if (in_array($reason, [MailManager_ProAiFailover_Model::REASON_AUTH, MailManager_ProAiFailover_Model::REASON_CREDIT], true)) {
                $lines[] = vtranslate('LBL_AI_OUTAGE_MAIL_CHECK_' . strtoupper($reason), $module);
            }
        } else {
            $subject = sprintf(vtranslate('LBL_AI_OUTAGE_MAIL_END_SUBJECT', $module), $providerLabel);
            $lines = [sprintf(vtranslate('LBL_AI_OUTAGE_MAIL_END_TEXT', $module), $providerLabel, $since)];
        }

        global $site_URL, $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
        $lines[] = vtranslate('LBL_AI_OUTAGE_MAIL_STATUS', $module) . ' '
            . rtrim((string)$site_URL, '/') . '/index.php?module=MailManager&parent=Settings&view=AISettings';
        $body = '<p>' . implode('</p><p>', array_map(function ($line) {
            return nl2br(htmlspecialchars($line, ENT_QUOTES, 'UTF-8'), false);
        }, $lines)) . '</p>';
        $from = (string)($HELPDESK_SUPPORT_EMAIL_ID ?: 'noreply@' . (parse_url((string)$site_URL, PHP_URL_HOST) ?: 'localhost'));
        $fromName = (string)($HELPDESK_SUPPORT_NAME ?: 'Defalto CRM');

        try {
            /** @var ITS4YouEmails_Record_Model $record */
            $record = Vtiger_Record_Model::getCleanInstance('ITS4YouEmails');
            $record->set('assigned_user_id', Users::getActiveAdminId());
            $record->set('subject', $subject);
            $record->set('body', $body);
            $record->set('email_flag', ITS4YouEmails_Record_Model::$FLAG_SAVED);
            $record->set('from_email', $from);
            $record->set('from_name', $fromName);
            $record->set('from_email_ids', 'email|' . $from . '|' . $fromName);
            $record->set('to_email', implode(',', $recipients));
            $record->set('to_email_ids', implode(',', array_map(function ($address) {
                return 'email|' . $address . '|';
            }, $recipients)));
            $record->save();

            /** @var ITS4YouEmails_Record_Model $saved */
            $saved = ITS4YouEmails_Record_Model::getInstanceById($record->getId(), 'ITS4YouEmails');
            $saved->send();
        } catch (Throwable $e) {
            // the notice is a courtesy — an AI call never fails because of it
        }
    }

    /**
     * @param int    $outageId
     * @param string $type
     *
     * @return bool this caller sends the notice (the first one only)
     */
    private static function claim(int $outageId, string $type): bool
    {
        $adb = PearDatabase::getInstance();
        $column = self::TYPE_START === $type ? 'notified_start' : 'notified_end';
        $result = $adb->pquery(
            'UPDATE df_mailmanager_aioutage SET ' . $column . ' = 1 WHERE outageid = ? AND ' . $column . ' = 0',
            [$outageId]
        );

        return $adb->getAffectedRowCount($result) > 0;
    }

    /**
     * @return string[] the address of the AI settings, else the e-mails of the active admins
     */
    private static function getRecipients(): array
    {
        $configured = array_filter(array_map('trim', explode(',', MailManager_ProAiConfig_Model::get(MailManager_ProAiFailover_Model::KEY_EMAIL))));
        $configured = array_values(array_filter($configured, function ($address) {
            return false !== filter_var($address, FILTER_VALIDATE_EMAIL);
        }));

        if ($configured) {
            return $configured;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT email1 FROM vtiger_users WHERE is_admin = 'on' AND status = 'Active' AND deleted = 0 AND email1 <> ''",
            []
        );
        $admins = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            if (filter_var($row['email1'], FILTER_VALIDATE_EMAIL)) {
                $admins[] = strtolower($row['email1']);
            }
        }

        return array_values(array_unique($admins));
    }
}
