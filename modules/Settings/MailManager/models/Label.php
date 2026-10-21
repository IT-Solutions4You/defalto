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
 * Email label persistence — df_mailmanager_label
 * (configurable AI triage labels: name, color, description for the AI prompt,
 * suggested record module). A label without an owner is global (admin
 * settings); a user's own labels apply to the mailboxes that user owns
 * personally — a shared mailbox is seen by more people and works with the
 * global labels only
 */
class Settings_MailManager_Label_Model
{
    /** Modules the triage may suggest creating a record in */
    public const SUGGESTED_MODULES = ['Accounts', 'Contacts', 'HelpDesk', 'Leads', 'Potentials'];

    /** Cleanup actions the triage suggests — performed by itself only when the label says so (auto_action) */
    public const SUGGESTED_ACTIONS = ['action:spam', 'action:delete'];

    /**
     * Label colors — the Defalto skin palette (--dt-* tokens of layouts/d1/skins/base and the
     * secondary gray), hex => label key; laid out in rows of five (light, base, dark tones of
     * blue, green, red, yellow and gray). The chip text follows the color (getTextColor)
     */
    public const PALETTE = [
        '#eff3fe' => 'LBL_COLOR_LIGHT_BLUE', '#cffcdb' => 'LBL_COLOR_LIGHT_GREEN', '#fcdee7' => 'LBL_COLOR_LIGHT_RED',
        '#fefaef' => 'LBL_COLOR_LIGHT_YELLOW', '#f5f5fa' => 'LBL_COLOR_LIGHT_GRAY',
        '#5e81f4' => 'LBL_COLOR_BLUE', '#5ce573' => 'LBL_COLOR_GREEN', '#f5587d' => 'LBL_COLOR_RED',
        '#f4cf5e' => 'LBL_COLOR_YELLOW', '#8181a5' => 'LBL_COLOR_GRAY',
        '#475eab' => 'LBL_COLOR_DARK_BLUE', '#47ab5e' => 'LBL_COLOR_DARK_GREEN', '#ab475e' => 'LBL_COLOR_DARK_RED',
    ];

    /** Color of a label saved without a valid one */
    public const DEFAULT_COLOR = '#eff3fe';

    /**
     * Returns the palette color closest to the given color, so every label uses the Defalto palette.
     *
     * @param string $color '#rrggbb'
     *
     * @return string palette color
     */
    public static function getPaletteColor($color)
    {
        $color = strtolower(trim((string)$color));

        if (isset(self::PALETTE[$color])) {
            return $color;
        }

        if (!preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/', $color, $match)) {
            return self::DEFAULT_COLOR;
        }

        $closest = self::DEFAULT_COLOR;
        $closestDistance = PHP_INT_MAX;

        foreach (array_keys(self::PALETTE) as $paletteColor) {
            $distance = 0;

            foreach ([1, 3, 5] as $offset) {
                $distance += (hexdec($match[(int)(($offset + 1) / 2)]) - hexdec(substr($paletteColor, $offset, 2))) ** 2;
            }

            if ($distance < $closestDistance) {
                $closest = $paletteColor;
                $closestDistance = $distance;
            }
        }

        return $closest;
    }

    /** Luminance (0-255) below which a label chip gets a light text */
    private const DARK_COLOR_LUMINANCE = 150;

    /**
     * Text color readable on a label color — dark on the light tones, white
     * on the dark ones (the palette and the custom picker offer both)
     *
     * @param string $color '#rrggbb'
     * @return string '#rrggbb'
     */
    public static function getTextColor($color)
    {
        if (!preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', trim((string)$color), $match)) {
            return '#444444';
        }
        $luminance = 0.299 * hexdec($match[1]) + 0.587 * hexdec($match[2]) + 0.114 * hexdec($match[3]);

        return $luminance < self::DARK_COLOR_LUMINANCE ? '#ffffff' : '#444444';
    }

    /**
     * Global labels ordered by sequence (inactive included — the admin list
     * shows them unchecked)
     *
     * @return array
     */
    public static function getGlobal()
    {
        return self::fetchRows(
            'SELECT * FROM df_mailmanager_label WHERE owner_userid IS NULL ORDER BY sequence, labelid',
            []
        );
    }

    /**
     * The labels of one user (inactive included)
     *
     * @param int $userId
     * @return array
     */
    public static function getOwn($userId)
    {
        return self::fetchRows(
            'SELECT * FROM df_mailmanager_label WHERE owner_userid = ? ORDER BY sequence, labelid',
            [(int)$userId]
        );
    }

    /**
     * The labels a mailbox works with — the global ones and, for a personal
     * mailbox, its owner's own ones
     *
     * @param int $mailboxId
     * @param bool $activeOnly
     * @return array ordered global first, then own, each by sequence
     */
    public static function getForMailbox($mailboxId, $activeOnly = true)
    {
        list($scopeSql, $scopeParams) = self::mailboxScopeSql('l', $mailboxId);

        $labels = self::fetchRows(
            'SELECT l.* FROM df_mailmanager_label l
             WHERE ' . $scopeSql . ($activeOnly ? ' AND l.active = 1' : '') . '
             ORDER BY l.owner_userid IS NOT NULL, l.sequence, l.labelid',
            $scopeParams
        );
        foreach ($labels as $index => $label) {
            $labels[$index] = self::applyMailboxOverride($label, $mailboxId);
        }

        return $labels;
    }

    /** @var array mailboxid => owner of the personal mailbox (0 = a shared one) */
    private static $personalOwners = [];

    /** @var array userid => [labelid => override row] */
    private static $overrides = [];

    /**
     * @param int $mailboxId
     * @return int the owner of a personal mailbox, 0 for a shared one — the
     *             team of a shared mailbox works with one setting
     */
    private static function getPersonalOwner($mailboxId)
    {
        $mailboxId = (int)$mailboxId;
        if (!isset(self::$personalOwners[$mailboxId])) {
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                "SELECT owner_userid FROM df_mailmanager_mailbox WHERE mailboxid = ? AND mailbox_type = 'personal'",
                [$mailboxId]
            );
            self::$personalOwners[$mailboxId] = $adb->num_rows($result)
                ? (int)$adb->query_result($result, 0, 'owner_userid')
                : 0;
        }

        return self::$personalOwners[$mailboxId];
    }

    /**
     * A user's own settings of the global labels
     *
     * @param int $userId
     * @return array labelid => ['ai_description', 'suggested_module', 'auto_action', 'ai_enabled']
     */
    public static function getOverrides($userId)
    {
        $userId = (int)$userId;
        if (!isset(self::$overrides[$userId])) {
            self::$overrides[$userId] = [];
            foreach (self::fetchRows('SELECT * FROM df_mailmanager_labeluser WHERE userid = ?', [$userId]) as $row) {
                self::$overrides[$userId][(int)$row['labelid']] = $row;
            }
        }

        return self::$overrides[$userId];
    }

    /**
     * A global label as a user set it up for himself: what it suggests,
     * whether it acts by itself and what the AI sorts under it. Switched off
     * for the AI = no description — the triage never offers such a label,
     * it stays for labeling by hand ('customized' marks a changed label)
     *
     * @param array $label label row
     * @param int $userId
     * @return array
     */
    public static function applyUserOverride(array $label, $userId)
    {
        $override = $label['owner_userid'] === null || $label['owner_userid'] === ''
            ? (self::getOverrides($userId)[(int)$label['labelid']] ?? null)
            : null;
        if (!$override) {
            return $label + ['customized' => false, 'ai_enabled' => 1];
        }
        $aiEnabled = (int)$override['ai_enabled'] === 1;

        return array_merge($label, [
            'ai_description' => $aiEnabled ? (string)$override['ai_description'] : '',
            'own_description' => (string)$override['ai_description'],
            'suggested_module' => $override['suggested_module'],
            'auto_action' => (int)$override['auto_action'],
            'ai_enabled' => $aiEnabled ? 1 : 0,
            'customized' => true,
        ]);
    }

    /**
     * @param array $label label row
     * @param int $mailboxId
     * @return array the label as the mailbox works with it (its owner's own
     *               setting in a personal mailbox, the common one otherwise)
     */
    public static function applyMailboxOverride(array $label, $mailboxId)
    {
        $ownerId = self::getPersonalOwner($mailboxId);

        return $ownerId ? self::applyUserOverride($label, $ownerId) : $label;
    }

    /**
     * @param int $labelId
     * @param int $mailboxId
     * @return array|null the label as the mailbox works with it
     */
    public static function getEffective($labelId, $mailboxId)
    {
        $label = self::getById($labelId);

        return $label ? self::applyMailboxOverride($label, $mailboxId) : null;
    }

    /**
     * @param int $mailboxId
     * @return int[] labels that throw mail away (Move to Spam / Trash) in this mailbox
     */
    public static function getCleanupLabelIds($mailboxId)
    {
        $ids = [];
        foreach (self::getForMailbox($mailboxId, false) as $label) {
            if (in_array((string)$label['suggested_module'], self::SUGGESTED_ACTIONS, true)) {
                $ids[] = (int)$label['labelid'];
            }
        }

        return $ids;
    }

    /**
     * Saves a user's own setting of a global label
     *
     * @param int $labelId
     * @param int $userId
     * @param array $data ['ai_description', 'suggested_module', 'auto_action' => bool, 'ai_enabled' => bool]
     * @return void
     * @throws Exception when the label is not a global one
     */
    public static function saveOverride($labelId, $userId, array $data)
    {
        if (!self::getOwned($labelId, null)) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
        $suggestedModule = $data['suggested_module'] ?? '';
        if (!in_array($suggestedModule, self::getSuggestedOptions(), true)) {
            $suggestedModule = null;
        }
        // only a cleanup action (Move to Spam / Trash) can run by itself
        $autoAction = !empty($data['auto_action']) && in_array($suggestedModule, self::SUGGESTED_ACTIONS, true) ? 1 : 0;
        $description = trim((string)($data['ai_description'] ?? ''));
        $aiEnabled = !empty($data['ai_enabled']) ? 1 : 0;
        $now = date('Y-m-d H:i:s');

        PearDatabase::getInstance()->pquery(
            'INSERT INTO df_mailmanager_labeluser
                (labelid, userid, ai_description, suggested_module, auto_action, ai_enabled, modifiedtime)
             VALUES (?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE ai_description = ?, suggested_module = ?, auto_action = ?, ai_enabled = ?,
                modifiedtime = ?',
            [
                (int)$labelId, (int)$userId, $description, $suggestedModule, $autoAction, $aiEnabled, $now,
                $description, $suggestedModule, $autoAction, $aiEnabled, $now,
            ]
        );
        unset(self::$overrides[(int)$userId]);
    }

    /**
     * Back to the common setting of the label
     *
     * @param int $labelId
     * @param int $userId
     * @return void
     */
    public static function deleteOverride($labelId, $userId)
    {
        PearDatabase::getInstance()->pquery(
            'DELETE FROM df_mailmanager_labeluser WHERE labelid = ? AND userid = ?',
            [(int)$labelId, (int)$userId]
        );
        unset(self::$overrides[(int)$userId]);
    }

    /**
     * The global labels as a user has them — the list of My Settings
     *
     * @param int $userId
     * @return array active global labels with the user's own settings applied
     */
    public static function getGlobalForUser($userId)
    {
        $labels = [];
        foreach (self::getGlobal() as $label) {
            if ((int)$label['active'] === 1) {
                $labels[] = self::applyUserOverride($label, $userId);
            }
        }

        return $labels;
    }

    /**
     * SQL condition limiting labels to the ones of a mailbox
     *
     * @param string $alias label table alias
     * @param int $mailboxId
     * @return array [sql, params]
     */
    public static function mailboxScopeSql($alias, $mailboxId)
    {
        return [
            '(' . $alias . '.owner_userid IS NULL OR ' . $alias . '.owner_userid = (
                SELECT mb.owner_userid FROM df_mailmanager_mailbox mb
                WHERE mb.mailboxid = ? AND mb.mailbox_type = \'personal\'))',
            [(int)$mailboxId],
        ];
    }

    /**
     * @param int $labelId
     * @param int $mailboxId
     * @return bool the label may be put on the threads of the mailbox
     */
    public static function isAvailableForMailbox($labelId, $mailboxId)
    {
        foreach (self::getForMailbox($mailboxId, false) as $label) {
            if ((int)$label['labelid'] === (int)$labelId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $labelId
     * @return array|null
     */
    public static function getById($labelId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_label WHERE labelid = ?', [$labelId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * A label of the given owner (null = a global label)
     *
     * @param int $labelId
     * @param int|null $ownerUserId
     * @return array|null
     */
    public static function getOwned($labelId, $ownerUserId)
    {
        $label = self::getById($labelId);
        if (!$label) {
            return null;
        }
        $owner = $label['owner_userid'] === null || $label['owner_userid'] === '' ? null : (int)$label['owner_userid'];

        return $owner === ($ownerUserId === null ? null : (int)$ownerUserId) ? $label : null;
    }

    /**
     * Inserts or updates a label of an owner (null = global)
     *
     * @param array $data ['labelid' => int (0 = new), 'name', 'color', 'ai_description',
     *                     'suggested_module', 'active' => bool, 'workspace' => bool]
     * @param int|null $ownerUserId
     * @return int labelid
     * @throws Exception on validation error or a label of another owner
     */
    public static function save(array $data, $ownerUserId = null)
    {
        $adb = PearDatabase::getInstance();

        $name = trim($data['name'] ?? '');
        if ($name === '') {
            throw new Exception(vtranslate('LBL_LABEL_NAME_REQUIRED', 'Settings:MailManager'));
        }
        $labelId = (int)($data['labelid'] ?? 0);
        if ($labelId > 0 && !self::getOwned($labelId, $ownerUserId)) {
            throw new Exception(vtranslate('LBL_LABEL_NOT_FOUND', 'Settings:MailManager'));
        }
        // two labels of one name would make the AI (and the user) pick blindly
        $conflicts = self::findNameConflicts($name, $labelId, $ownerUserId);
        if ($conflicts) {
            throw new Exception(self::nameConflictMessage($conflicts, $ownerUserId));
        }

        $color = self::getPaletteColor($data['color'] ?? '');

        $suggestedModule = $data['suggested_module'] ?? '';
        if (!in_array($suggestedModule, array_merge(self::SUGGESTED_MODULES, self::SUGGESTED_ACTIONS), true)) {
            $suggestedModule = null;
        }

        $description = trim($data['ai_description'] ?? '');
        $active = !empty($data['active']) ? 1 : 0;
        $workspace = !empty($data['workspace']) ? 1 : 0;
        // only a cleanup action (Move to Spam / Trash) can run by itself
        $autoAction = !empty($data['auto_action']) && in_array($suggestedModule, self::SUGGESTED_ACTIONS, true)
            ? 1
            : 0;

        if ($labelId > 0) {
            $adb->pquery(
                'UPDATE df_mailmanager_label
                 SET name = ?, color = ?, ai_description = ?, suggested_module = ?, active = ?, workspace = ?,
                     auto_action = ?
                 WHERE labelid = ?',
                [$name, $color, $description, $suggestedModule, $active, $workspace, $autoAction, $labelId]
            );

            return $labelId;
        }

        $sequenceResult = $adb->pquery(
            'SELECT COALESCE(MAX(sequence), 0) + 1 AS seq FROM df_mailmanager_label',
            []
        );
        $sequence = (int)$adb->query_result($sequenceResult, 0, 'seq');

        $adb->pquery(
            'INSERT INTO df_mailmanager_label
                (name, color, ai_description, suggested_module, active, workspace, auto_action, sequence, owner_userid)
             VALUES (?,?,?,?,?,?,?,?,?)',
            [$name, $color, $description, $suggestedModule, $active, $workspace, $autoAction, $sequence, $ownerUserId]
        );

        return (int)$adb->getLastInsertID();
    }

    /**
     * Deletes a label of an owner (null = global) and clears it from the
     * threads it was assigned to
     *
     * @param int $labelId
     * @param int|null $ownerUserId
     * @return bool false when the label is not the owner's
     */
    public static function delete($labelId, $ownerUserId = null)
    {
        if (!self::getOwned($labelId, $ownerUserId)) {
            return false;
        }
        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE df_mailmanager_thread SET labelid = NULL WHERE labelid = ?', [$labelId]);
        $adb->pquery('DELETE FROM df_mailmanager_labelrule WHERE labelid = ?', [$labelId]);
        $adb->pquery('DELETE FROM df_mailmanager_label WHERE labelid = ?', [$labelId]);
        $adb->pquery('DELETE FROM df_mailmanager_labeluser WHERE labelid = ?', [$labelId]);

        return true;
    }

    /**
     * @return string[] every suggested action the label modal offers
     */
    public static function getSuggestedOptions()
    {
        return array_merge(self::SUGGESTED_MODULES, self::SUGGESTED_ACTIONS);
    }

    /**
     * Messages of the shared label editor (LabelEditor.js) — the admin page
     * and My Settings run under different modules, so the page JS strings
     * would not hold them in both
     *
     * @return string JSON for the editor's data-strings attribute
     */
    public static function getEditorStringsJson()
    {
        $language = Vtiger_Language_Handler::getLanguage();
        $strings = [];
        $keys = ['JS_LABEL_SAVED', 'JS_LABEL_DELETED', 'JS_DELETE_LABEL_CONFIRM', 'JS_FILL_REQUIRED', 'JS_SAVE_ERROR'];
        foreach ($keys as $key) {
            $strings[$key] = Vtiger_Language_Handler::getJSTranslatedString(
                $language,
                $key,
                'Settings:MailManager'
            );
        }

        return json_encode($strings, JSON_UNESCAPED_UNICODE);
    }

    /**
     * A saved label for the editor's table row — with its action translated
     *
     * @param array $label label row
     * @return array
     */
    public static function describe(array $label)
    {
        $action = (string)$label['suggested_module'];
        $label['action_label'] = $action !== ''
            ? vtranslate('LBL_ACTION_' . str_replace(':', '_', $action), 'Settings:MailManager')
            : '';

        return $label;
    }

    /**
     * Labels that already carry the name: a user's own label must differ
     * from the global ones and the user's other own; a global label from
     * every label there is
     *
     * @param string $name
     * @param int $exceptLabelId the label being edited
     * @param int|null $ownerUserId null = a global label is saved
     * @return array owner user ids of the conflicting labels, null = a global label
     */
    private static function findNameConflicts($name, $exceptLabelId, $ownerUserId)
    {
        $params = [$name, (int)$exceptLabelId];
        $ownerSql = '';
        if ($ownerUserId !== null) {
            $ownerSql = ' AND (owner_userid IS NULL OR owner_userid = ?)';
            $params[] = (int)$ownerUserId;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT owner_userid FROM df_mailmanager_label
             WHERE LOWER(name) = LOWER(?) AND labelid != ?' . $ownerSql,
            $params
        );

        $owners = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $owner = $adb->query_result($result, $i, 'owner_userid');
            $owners[] = $owner === null || $owner === '' ? null : (int)$owner;
        }

        return $owners;
    }

    /**
     * Why the name is refused. The admin saving a global label learns whose
     * own label blocks it (to rename it in that mailbox's detail); a user
     * never learns about other users' labels
     *
     * @param array $conflicts owner user ids, null = a global label
     * @param int|null $ownerUserId null = a global label is saved
     * @return string
     */
    private static function nameConflictMessage(array $conflicts, $ownerUserId)
    {
        $qualifiedModule = 'Settings:MailManager';
        if ($ownerUserId !== null) {
            return vtranslate('LBL_LABEL_NAME_EXISTS', $qualifiedModule);
        }
        if (in_array(null, $conflicts, true)) {
            return vtranslate('LBL_GLOBAL_LABEL_NAME_EXISTS', $qualifiedModule);
        }
        $users = array_unique(array_map(function ($userId) {
            return trim((string)getUserFullName($userId));
        }, $conflicts));

        return sprintf(vtranslate('LBL_LABEL_NAME_USED_BY', $qualifiedModule), implode(', ', $users));
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array rows
     */
    private static function fetchRows($sql, array $params)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($sql, $params);

        $rows = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $rows[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $rows;
    }
}
