<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'include/utils/encryption.php';

/**
 * Global AI assistant configuration of Mail Manager Pro. Values live in df_mailmanager_config
 * (name/value, through MailManager_ProConfig_Model), usage accounting in df_mailmanager_ailog.
 * AI needs only the Pro edition (Membership) and an own provider key (decisions D9, D22);
 * the Installer "AI" extension entitlement is never checked. The class keeps the static
 * get()/set() API of the ZIP, so it does not extend Core_DatabaseData_Model (whose get() is
 * an instance method).
 */
class MailManager_ProAiConfig_Model
{
    public const FEATURE_SUMMARY = 'summary';
    public const FEATURE_DRAFT = 'draft';
    public const FEATURE_TRIAGE = 'triage';
    public const FEATURE_ENTITY = 'entity';
    public const FEATURE_TRANSLATE = 'translate';
    public const FEATURE_DRAFTCHECK = 'draftcheck';
    public const FEATURE_RADAR = 'radar';

    public const FEATURES = [
        self::FEATURE_SUMMARY,
        self::FEATURE_DRAFT,
        self::FEATURE_TRIAGE,
        self::FEATURE_ENTITY,
        self::FEATURE_TRANSLATE,
        self::FEATURE_DRAFTCHECK,
        self::FEATURE_RADAR,
    ];

    public const PROVIDERS = [
        MailManager_ProAiProvider_Helper::PROVIDER_CLAUDE,
        MailManager_ProAiProvider_Helper::PROVIDER_OPENAI,
        MailManager_ProAiProvider_Helper::PROVIDER_GEMINI,
    ];

    private const KEY_API_KEY = 'ai_api_key';

    /**
     * Defaults of the ZIP AiConfig (decision D15): AI off, triage only suggests,
     * auto-create limited to 20 records per mailbox and 24 hours.
     */
    private const DEFAULTS = [
        'ai_enabled' => '0',
        'ai_provider' => 'claude',
        'ai_model' => '',
        // a sorting the triage agent is unsure of is repeated by the fallback agent
        'ai_triage_cascade' => '0',
        'ai_triage_fallback' => '',
        // the admin's prices per 1M tokens, JSON {model: {in, out}} (the usage overview)
        'ai_prices' => '',
        'ai_feature_summary' => '1',
        'ai_feature_draft' => '1',
        'ai_feature_triage' => '1',
        'ai_feature_entity' => '1',
        // translation of a received message, on a click only (Translation model)
        'ai_feature_translate' => '1',
        // I15 — off until the admin switches them on
        'ai_feature_draftcheck' => '0',
        'ai_feature_radar' => '0',
        // personal data replaced by placeholders before every AI call
        'ai_anonymize' => '0',
        'ai_triage_mode' => 'suggest',
        // Auto-create mode: max records created per mailbox in 24 hours
        'ai_autocreate_limit' => '20',
        // Deterministic spam learning, works without an API key, therefore
        // NOT part of FEATURES (isFeatureEnabled requires the assistant)
        'ai_feature_spamlearn' => '1',
        // Deterministic: new inbound mail of a thread with a linked case is logged as a comment
        'ai_feature_autocomment' => '0',
        // Deterministic: new mail of a linked conversation is filed as an ITS4YouEmails record
        'ai_feature_emailfiling' => '1',
        // Similar solved cases in drafts (topic tags come from the triage call)
        'ai_feature_similar' => '1',
        // Knowledge in drafts: files, FAQ and solved tickets (Knowledge model)
        'ai_feature_knowledge' => '1',
        'ai_knowledge_faq' => '1',
        'ai_knowledge_tickets' => '1',
        // the description, solution and comments of the linked records in the prompts (I20);
        // the token cap of that history, '' = a share of the agent's input limit
        'ai_feature_recordhistory' => '1',
        'ai_recordhistory_tokens' => '',
        // the admin's own priority rules for the triage (free text, '' = the built-in ones only)
        'ai_priority_rules' => '',
        // not AI: conversations per page of the inbox list
        'list_page_size' => '25',
    ];

    private const AI_LOG_TABLE = 'df_mailmanager_ailog';
    private const AI_LOG_TABLE_ID = 'logid';

    /** @var array<string, string|null> per-request cache of read values */
    private static array $cache = [];

    /**
     * Returns the table accessor for the usage log df_mailmanager_ailog.
     *
     * @return Core_DatabaseData_Model
     */
    protected static function getAiLogTable(): Core_DatabaseData_Model
    {
        return Core_DatabaseData_Model::getTableInstance(self::AI_LOG_TABLE, self::AI_LOG_TABLE_ID);
    }

    /**
     * Returns the shared config storage model.
     *
     * @return MailManager_ProConfig_Model
     */
    protected static function getStore(): MailManager_ProConfig_Model
    {
        return new MailManager_ProConfig_Model();
    }

    /**
     * Returns the stored raw value of a setting, or null when it is not stored.
     *
     * @param string $name setting name
     *
     * @return string|null
     */
    protected static function getStoredValue(string $name): ?string
    {
        if (!array_key_exists($name, self::$cache)) {
            self::$cache[$name] = self::getStore()->getValue($name);
        }

        return self::$cache[$name];
    }

    /**
     * Returns the default values of all AI settings.
     *
     * @return array name => value
     */
    public static function getDefaults(): array
    {
        return self::DEFAULTS;
    }

    /**
     * Returns all AI config values with defaults applied (the API key is excluded).
     *
     * @return array name => value
     */
    public static function getAll(): array
    {
        $config = self::DEFAULTS;

        foreach (array_keys(self::DEFAULTS) as $name) {
            $value = self::getStoredValue($name);

            if (null !== $value) {
                $config[$name] = $value;
            }
        }

        return $config;
    }

    /**
     * Returns one config value, falling back to the built-in default.
     *
     * @param string $name    setting name
     * @param string $default used when neither a stored value nor a built-in default exists
     *
     * @return string
     */
    public static function get(string $name, string $default = ''): string
    {
        $value = self::getStoredValue($name);

        if (null !== $value) {
            return $value;
        }

        return (string)(self::DEFAULTS[$name] ?? $default);
    }

    /**
     * Stores one config value.
     *
     * @param string $name  setting name
     * @param string $value setting value
     *
     * @return void
     */
    public static function set(string $name, string $value): void
    {
        self::getStore()->saveValue($name, $value);
        self::$cache[$name] = $value;
    }

    /**
     * Returns whether an API key is stored — for one provider, or for any.
     *
     * @param string $provider PROVIDER_*, '' = any provider
     *
     * @return bool
     */
    public static function hasApiKey(string $provider = ''): bool
    {
        foreach ('' !== $provider ? [$provider] : self::PROVIDERS as $candidate) {
            if ('' !== self::getStoredKey($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stores the API key of a provider encrypted with the Defalto Encryption engine
     * (decision D16) — every provider has its own (I13). An empty key removes it.
     *
     * @param string $plainKey API key in plain text
     * @param string $provider PROVIDER_*, '' = the main provider
     *
     * @return void
     */
    public static function setApiKey(string $plainKey, string $provider = ''): void
    {
        $provider = '' !== $provider ? $provider : self::get('ai_provider');
        $value = '' === $plainKey ? '' : (string)(new Encryption())->encrypt($plainKey);
        self::set(self::KEY_API_KEY . '_' . $provider, $value);
    }

    /**
     * Returns the decrypted API key of a provider ('' when not configured). Never log
     * or show the result.
     *
     * @param string $provider PROVIDER_*, '' = the main provider
     *
     * @return string
     */
    public static function getApiKey(string $provider = ''): string
    {
        $encrypted = self::getStoredKey('' !== $provider ? $provider : self::get('ai_provider'));

        return '' === $encrypted ? '' : (string)(new Encryption())->decrypt($encrypted);
    }

    /**
     * @param string $provider
     *
     * @return string the encrypted key of the provider; the single key of the time
     *                before I13 belongs to the provider it was set up with
     */
    private static function getStoredKey(string $provider): string
    {
        $encrypted = (string)self::getStoredValue(self::KEY_API_KEY . '_' . $provider);

        if ('' === $encrypted && $provider === self::get('ai_provider')) {
            $encrypted = (string)self::getStoredValue(self::KEY_API_KEY);
        }

        return $encrypted;
    }

    /**
     * Returns whether the AI assistant is switched on and has a key.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return '1' === self::get('ai_enabled') && self::hasApiKey();
    }

    /**
     * Returns whether one AI feature is enabled (the assistant must be enabled too).
     *
     * @param string $feature summary|draft|triage|entity|translate|similar|knowledge
     *
     * @return bool
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        return self::isEnabled() && '1' === self::get('ai_feature_' . $feature);
    }

    /**
     * Returns the triage mode: suggest (default) or auto (records are created automatically).
     *
     * @return string suggest|auto
     */
    public static function getTriageMode(): string
    {
        return 'auto' === self::get('ai_triage_mode') ? 'auto' : 'suggest';
    }

    /**
     * Returns the max records the Auto-create mode creates per mailbox in 24 hours
     * (0 = none, everything stays a suggestion).
     *
     * @return int
     */
    public static function getAutoCreateLimit(): int
    {
        return max(0, (int)self::get('ai_autocreate_limit'));
    }

    /**
     * The driver for the assistant's calls, or null when the assistant is disabled or
     * has no key. MailManager_ProAiClient_Model::complete() runs each call through the
     * agent of its task; this one only tells the callers the assistant is ready.
     *
     * @return MailManager_ProAiProvider_Helper|null
     * @throws Exception on unknown provider
     */
    public static function getProvider(): ?MailManager_ProAiProvider_Helper
    {
        if (!self::isEnabled()) {
            return null;
        }

        $provider = self::get('ai_provider');

        // a key of another provider only (the agents moved there)
        if (!self::hasApiKey($provider)) {
            foreach (self::PROVIDERS as $candidate) {
                if (self::hasApiKey($candidate)) {
                    $provider = $candidate;
                    break;
                }
            }
        }

        return MailManager_ProAiProvider_Helper::getInstance(
            $provider,
            self::getApiKey($provider),
            $provider === self::get('ai_provider') ? self::get('ai_model') : ''
        );
    }

    /**
     * Token use per month, task, agent and model with the cost from the admin's prices
     * (the usage overview of the AI settings).
     *
     * @param int $months the current month and as many before it
     *
     * @return array of ['month', 'feature', 'task', 'feature_label', 'agent', 'model', 'calls', tokens, '*_display', 'cost_display']
     */
    public static function getUsageSummary(int $months = 3): array
    {
        $adb = PearDatabase::getInstance();
        $since = date('Y-m-01 00:00:00', strtotime('first day of -' . max(0, $months - 1) . ' month'));
        $result = $adb->pquery(
            "SELECT DATE_FORMAT(l.createdtime, '%Y-%m') AS month, l.feature, l.model, a.name AS agent, COUNT(*) AS calls,
                    SUM(l.input_tokens) AS input_tokens, SUM(l.output_tokens) AS output_tokens,
                    SUM(l.is_backup) AS backup_calls
             FROM " . self::AI_LOG_TABLE . ' l
             LEFT JOIN df_mailmanager_aiagent a ON a.agentid = l.agentid
             WHERE l.createdtime >= ?
             GROUP BY month, l.feature, l.model, a.name
             ORDER BY month DESC, input_tokens DESC',
            [$since]
        );
        $prices = self::getPrices();
        $rows = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $price = $prices[(string)$row['model']] ?? null;
            $row['task'] = MailManager_ProAiAgent_Model::getTaskForFeature((string)$row['feature']);
            // a key check or an agent test belongs to no task — it has a name of its own
            $row['feature_label'] = '' !== $row['task']
                ? vtranslate('LBL_AI_TASK_' . strtoupper($row['task']), 'Settings:MailManager')
                : vtranslate('LBL_AI_USAGE_FEATURE_' . strtoupper((string)$row['feature']), 'Settings:MailManager');
            $row['input_display'] = number_format((int)$row['input_tokens'], 0, ',', ' ');
            $row['output_display'] = number_format((int)$row['output_tokens'], 0, ',', ' ');
            $row['cost_display'] = $price
                ? self::formatCost(((int)$row['input_tokens'] * $price['in'] + (int)$row['output_tokens'] * $price['out']) / 1000000)
                : '';
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * A cost in the user's number format — a small one with four decimals, not 0,00, a tiny one
     * as "< 0,0001".
     *
     * @param float $cost
     *
     * @return string
     */
    private static function formatCost(float $cost): string
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $decimal = (string)($user ? $user->get('currency_decimal_separator') : '') ?: ',';
        $grouping = (string)($user ? $user->get('currency_grouping_separator') : '');
        $decimal = decode_html($decimal);

        // a cost that four decimals still show as nothing
        if ($cost > 0 && round($cost, 4) < 0.0001) {
            return '< 0' . $decimal . '0001';
        }

        $decimals = $cost > 0 && round($cost, 2) < 0.01 ? 4 : 2;

        return number_format($cost, $decimals, $decimal, decode_html($grouping));
    }

    /**
     * @return array model => ['in' => price per 1M input tokens, 'out' => per 1M output tokens,
     *               'source' => '' (the admin's own) | 'openrouter', 'date' => Y-m-d of an indicative price]
     */
    public static function getPrices(): array
    {
        $prices = json_decode(self::get('ai_prices'), true);
        $clean = [];

        foreach (is_array($prices) ? $prices : [] as $model => $price) {
            $clean[(string)$model] = [
                'in' => max(0, (float)($price['in'] ?? 0)),
                'out' => max(0, (float)($price['out'] ?? 0)),
                'source' => (string)($price['source'] ?? ''),
                'date' => (string)($price['date'] ?? ''),
            ];
        }

        return $clean;
    }

    /**
     * The models the price table lists: those of the usage, the priced ones and the models
     * of the agents (with their backups) — a new installation can price them before use.
     *
     * @param array|null $usage getUsageSummary(), null = read it
     *
     * @return string[]
     */
    public static function getPriceModels(?array $usage = null): array
    {
        $models = array_merge(
            array_column($usage ?? self::getUsageSummary(), 'model'),
            array_keys(self::getPrices())
        );

        foreach (MailManager_ProAiAgent_Model::getAll() as $agent) {
            $models[] = (string)$agent['model'];
            $backup = MailManager_ProAiFailover_Model::getBackup($agent);

            if ($backup) {
                $models[] = $backup['model'];
            }
        }

        return array_values(array_unique(array_filter($models)));
    }

    /**
     * Saves the prices the admin edited. A price left as it was keeps where it came from
     * (the catalog and its date); a model without a price is dropped.
     *
     * @param array $edited model => ['in', 'out'] as typed (decimal comma allowed)
     *
     * @return void
     */
    public static function saveEditedPrices(array $edited): void
    {
        $prices = [];
        $stored = self::getPrices();

        foreach ($edited as $model => $price) {
            $in = (float)str_replace(',', '.', (string)($price['in'] ?? ''));
            $out = (float)str_replace(',', '.', (string)($price['out'] ?? ''));

            if (preg_match('/^[\w.:-]{1,100}$/', (string)$model) && ($in > 0 || $out > 0)) {
                $prices[(string)$model] = ['in' => $in, 'out' => $out, 'source' => '', 'date' => ''];
                $before = $stored[(string)$model] ?? null;

                if ($before && abs($before['in'] - $in) < 0.00001 && abs($before['out'] - $out) < 0.00001) {
                    $prices[(string)$model]['source'] = $before['source'];
                    $prices[(string)$model]['date'] = $before['date'];
                }
            }
        }

        self::savePrices($prices);
    }

    /**
     * @param array $prices model => ['in', 'out', 'source', 'date'] (getPrices())
     *
     * @return void
     */
    public static function savePrices(array $prices): void
    {
        self::set('ai_prices', $prices ? (string)json_encode($prices) : '');
    }

    /**
     * Records one AI call (token counts only, never content) for the usage overview.
     *
     * @param string   $feature      summary|draft|triage|entity|... or a background job name
     * @param int|null $threadId     thread the call was about
     * @param string   $model        model id
     * @param int      $inputTokens  prompt tokens
     * @param int      $outputTokens answer tokens
     * @param int      $agentId      the agent that made the call, 0 = none
     * @param string   $provider     the provider that answered (PROVIDER_*), '' = not known
     * @param bool     $isBackup     the backup provider stood in for the agent's one (I17)
     *
     * @return void
     */
    public static function logUsage(
        string $feature,
        ?int $threadId,
        string $model,
        int $inputTokens,
        int $outputTokens,
        int $agentId = 0,
        string $provider = '',
        bool $isBackup = false
    ): void {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        self::getAiLogTable()->insertData([
            'userid' => (int)($currentUser ? $currentUser->getId() : 0),
            'threadid' => $threadId ?: null,
            'feature' => substr($feature, 0, 20),
            'model' => substr($model, 0, 50),
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'createdtime' => date('Y-m-d H:i:s'),
            'agentid' => $agentId ?: null,
            // which provider answered, and whether it stood in for the agent's one (I17)
            'provider' => '' !== $provider ? substr($provider, 0, 20) : null,
            'is_backup' => $isBackup ? 1 : 0,
        ]);
    }
}
