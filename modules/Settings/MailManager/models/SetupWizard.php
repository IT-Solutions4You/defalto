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
 * State and data of the administrator setup wizard shown on the first Pro start (task T14).
 * The wizard only orchestrates: every value is saved by its owning model.
 */
class Settings_MailManager_SetupWizard_Model
{
    public const COMPLETED_SETTING = 'setup_completed_at';
    public const COMPLETED_BY_SETTING = 'setup_completed_by';
    public const SKIPPED_SETTING = 'setup_skipped_at';

    /**
     * Config keys of the CRM filing flags the wizard edits, with their defaults (ZIP AiConfig defaults).
     */
    public const FILING_SETTINGS = [
        'ai_feature_emailfiling' => '1',
        'ai_feature_autocomment' => '0',
    ];

    /**
     * Returns true when the wizard was neither completed nor skipped yet.
     *
     * @return bool
     */
    public static function isSetupPending(): bool
    {
        $config = new MailManager_ProConfig_Model();

        return null === $config->getValue(self::COMPLETED_SETTING) && null === $config->getValue(self::SKIPPED_SETTING);
    }

    /**
     * Returns true when the wizard should open for the current user: an administrator and
     * a pending setup.
     *
     * @return bool
     */
    public static function isShownToCurrentUser(): bool
    {
        return Users_Record_Model::getCurrentUserModel()->isAdminUser() && self::isSetupPending();
    }

    /**
     * Returns the data for the wizard steps.
     *
     * @return array
     */
    public static function getWizardData(): array
    {
        $config = new MailManager_ProConfig_Model();
        $filing = [];

        foreach (self::FILING_SETTINGS as $name => $default) {
            $filing[$name] = $config->getValue($name) ?? $default;
        }

        return [
            'MAILBOXES' => Settings_MailManager_Mailbox_Model::getAll(),
            'LABELS' => Settings_MailManager_Label_Model::getGlobal(),
            'FILING' => $filing,
            'SYNC_FROM_DAYS' => $config->getValue(MailManager_ProMailbox_Model::SYNC_FROM_DAYS_SETTING) ?? (string)MailManager_ProMailbox_Model::DEFAULT_SYNC_FROM_DAYS,
        ];
    }

    /**
     * Saves the wizard values and marks the setup completed.
     *
     * @param array $values ['sync_from_days' => int, 'ai_feature_emailfiling' => bool, 'ai_feature_autocomment' => bool]
     *
     * @return void
     */
    public static function saveCompleted(array $values): void
    {
        $config = new MailManager_ProConfig_Model();
        $days = max(0, (int)($values['sync_from_days'] ?? MailManager_ProMailbox_Model::DEFAULT_SYNC_FROM_DAYS));
        $config->saveValue(MailManager_ProMailbox_Model::SYNC_FROM_DAYS_SETTING, (string)$days);

        foreach (array_keys(self::FILING_SETTINGS) as $name) {
            $config->saveValue($name, !empty($values[$name]) ? '1' : '0');
        }

        $config->saveValue(self::COMPLETED_SETTING, date('Y-m-d H:i:s'));
        $config->saveValue(self::COMPLETED_BY_SETTING, (string)Users_Record_Model::getCurrentUserModel()->getId());
    }

    /**
     * Marks the setup skipped; Pro keeps working with the defaults.
     *
     * @return void
     */
    public static function saveSkipped(): void
    {
        (new MailManager_ProConfig_Model())->saveValue(self::SKIPPED_SETTING, date('Y-m-d H:i:s'));
    }

    /**
     * Opens the wizard again ("Run setup again" in Settings).
     *
     * @return void
     */
    public static function clearState(): void
    {
        $config = new MailManager_ProConfig_Model();
        $config->saveValue(self::COMPLETED_SETTING, null);
        $config->saveValue(self::SKIPPED_SETTING, null);
    }
}
