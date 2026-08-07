<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_Install_Model extends Core_Install_Model
{
    private const LEGACY_MODULE = 'ITS4You2FA';

    public array $registerSettingsLinks = [
        ['2FA', 'index.php?module=TwoFactorAuthentication&parent=Settings&view=Index', 'LBL_USER_MANAGEMENT'],
        ['My 2FA', 'index.php?module=TwoFactorAuthentication&parent=Settings&view=Manage', 'LBL_MY_PREFERENCES'],
    ];

    public function addCustomLinks(): void
    {
        $this->updateSettingsLinks();
        Core_Modifiers_Model::registerModifier(
            'Users',
            'TwoFactorAuthentication',
            'LoginAction',
            'TwoFactorAuthentication_LoginAction_Modifier'
        );
    }

    public function deleteCustomLinks(): void
    {
        $this->updateSettingsLinks(false);
        Core_Modifiers_Model::deregisterModifier('Users', 'TwoFactorAuthentication', 'LoginAction');
    }

    public function getBlocks(): array
    {
        return [];
    }

    public function getTables(): array
    {
        return [
            'df_two_factor_config',
            'df_two_factor_user',
            'df_two_factor_backup_code',
            'df_two_factor_email_template',
        ];
    }

    /**
     * @throws Exception
     */
    public function installTables(): void
    {
        $this->migrateLegacyModuleMetadata();

        $this->getTable('df_two_factor_config', null)
            ->createTable('id', 'TINYINT UNSIGNED NOT NULL')
            ->createColumn('enforce_mode', "VARCHAR(20) NOT NULL DEFAULT 'off'")
            ->createColumn('allow_email', 'TINYINT(1) NOT NULL DEFAULT 1')
            ->createColumn('allow_totp', 'TINYINT(1) NOT NULL DEFAULT 0')
            ->createColumn('modifiedtime', 'DATETIME DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`id`)');

        $this->getTable('df_two_factor_user', null)
            ->createTable('userid', 'INT(19) NOT NULL')
            ->createColumn('method', "VARCHAR(20) NOT NULL DEFAULT ''")
            ->createColumn('secret', 'VARCHAR(255) DEFAULT NULL')
            ->createColumn('exempt', 'TINYINT(1) NOT NULL DEFAULT 0')
            ->createColumn('enrolled', 'TINYINT(1) NOT NULL DEFAULT 0')
            ->createColumn('totp_last_step', 'BIGINT DEFAULT NULL')
            ->createColumn('failed_attempts', 'SMALLINT UNSIGNED NOT NULL DEFAULT 0')
            ->createColumn('locked_until', 'DATETIME DEFAULT NULL')
            ->createColumn('createdtime', 'DATETIME DEFAULT NULL')
            ->createColumn('modifiedtime', 'DATETIME DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`userid`)')
            ->createKey('CONSTRAINT `fk_df_two_factor_user` FOREIGN KEY IF NOT EXISTS (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE');

        $this->getTable('df_two_factor_backup_code', 'id')
            ->createTable()
            ->createColumn('userid', 'INT(19) NOT NULL')
            ->createColumn('code_hash', 'VARCHAR(255) NOT NULL')
            ->createColumn('used', 'TINYINT(1) NOT NULL DEFAULT 0')
            ->createColumn('createdtime', 'DATETIME DEFAULT NULL')
            ->createKey('KEY IF NOT EXISTS `idx_df_two_factor_backup_user` (`userid`)')
            ->createKey('CONSTRAINT `fk_df_two_factor_backup_user` FOREIGN KEY IF NOT EXISTS (`userid`) REFERENCES `vtiger_users` (`id`) ON DELETE CASCADE');

        $this->getTable('df_two_factor_email_template', null)
            ->createTable('template_key', 'VARCHAR(40) NOT NULL')
            ->createColumn('subject', "VARCHAR(255) NOT NULL DEFAULT ''")
            ->createColumn('body', 'TEXT')
            ->createColumn('modifiedtime', 'DATETIME DEFAULT NULL')
            ->createKey('PRIMARY KEY IF NOT EXISTS (`template_key`)');

        $this->migrateLegacyData();

        $result = $this->db->pquery('SELECT id FROM df_two_factor_config WHERE id = ?', [1]);

        if (!$this->db->num_rows($result)) {
            $this->db->pquery(
                "INSERT INTO df_two_factor_config (id, enforce_mode, allow_email, allow_totp, modifiedtime)
                 VALUES (?, 'off', 1, 0, NOW())",
                [1]
            );
        }
    }

    private function migrateLegacyData(): void
    {
        $tables = [
            'its4you_2fa_config' => [
                'target' => 'df_two_factor_config',
                'columns' => 'id, enforce_mode, allow_email, allow_totp, modifiedtime',
            ],
            'its4you_2fa_user' => [
                'target' => 'df_two_factor_user',
                'columns' => 'userid, method, secret, exempt, enrolled, totp_last_step, failed_attempts, locked_until, createdtime, modifiedtime',
            ],
            'its4you_2fa_backup_code' => [
                'target' => 'df_two_factor_backup_code',
                'columns' => 'id, userid, code_hash, used, createdtime',
            ],
            'its4you_2fa_email_template' => [
                'target' => 'df_two_factor_email_template',
                'columns' => 'template_key, subject, body, modifiedtime',
            ],
        ];

        foreach ($tables as $legacyTable => $migration) {
            if (!Vtiger_Utils::checkTable($legacyTable)) {
                continue;
            }

            $this->db->pquery(
                sprintf(
                    'INSERT IGNORE INTO %s (%s) SELECT %s FROM %s',
                    $migration['target'],
                    $migration['columns'],
                    $migration['columns'],
                    $legacyTable
                )
            );
        }
    }

    private function migrateLegacyModuleMetadata(): void
    {
        $this->db->pquery(
            'DELETE FROM vtiger_links
             WHERE linktype = ? AND (linklabel IN (?, ?) OR linkurl LIKE ?)',
            [
                'HEADERSCRIPT',
                'ITS4You2FA_HS_Js',
                'ITS4You2FAMenu',
                '%/modules/ITS4You2FA/%',
            ]
        );
        $this->db->pquery(
            'DELETE FROM vtiger_settings_field WHERE linkto LIKE ? OR name IN (?, ?, ?)',
            [
                '%module=' . self::LEGACY_MODULE . '%',
                self::LEGACY_MODULE,
                'Two-Factor Authentication',
                'My Two-Factor Authentication',
            ]
        );

        $legacyResult = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', [self::LEGACY_MODULE]);
        $currentResult = $this->db->pquery('SELECT tabid FROM vtiger_tab WHERE name = ?', ['TwoFactorAuthentication']);

        if (!$this->db->num_rows($legacyResult) || $this->db->num_rows($currentResult)) {
            return;
        }

        $this->db->pquery(
            'UPDATE vtiger_tab SET name = ?, tablabel = ?, parent = ? WHERE name = ?',
            ['TwoFactorAuthentication', '2FA', 'Settings', self::LEGACY_MODULE]
        );
        $this->db->pquery(
            'UPDATE vtiger_ws_entity SET name = ? WHERE name = ?',
            ['TwoFactorAuthentication', self::LEGACY_MODULE]
        );
    }
}
