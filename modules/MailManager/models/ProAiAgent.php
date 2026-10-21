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
 * AI agents (I13): the admin sets up agents — provider, model, effort, extra
 * instructions, input limit — and gives each AI task one agent (config
 * ai_task_<task>). MailManager_ProAiClient_Model::complete() runs every call through
 * the agent of its task. The default agents "Fast" and "Quality" are made from the
 * former single provider and model, so nothing changes until the admin edits them.
 */
class MailManager_ProAiAgent_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_mailmanager_aiagent';
    protected string $tableId = 'agentid';

    /** task => default agent type; the order of the task list in the settings */
    public const TASKS = [
        'triage' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'topics' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'entity' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'translate' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'template' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'draftcheck' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'radar' => MailManager_ProAiCatalog_Model::TIER_FAST,
        // the lines of the Workspace digest (I19)
        'digest' => MailManager_ProAiCatalog_Model::TIER_FAST,
        // the summary of the older comments of a linked record (I20)
        'record_history' => MailManager_ProAiCatalog_Model::TIER_FAST,
        'draft' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'assistant' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'summary' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'knowledge' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'translate_own' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'styleprofile' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        'deep' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
        // the summary on top of the Workspace digest (I19)
        'digest_summary' => MailManager_ProAiCatalog_Model::TIER_QUALITY,
    ];

    /** usage-log feature => task, where the two differ */
    private const FEATURE_TASKS = [
        'language' => 'translate',
        'solution' => 'summary',
    ];

    /** Calls that pick their driver themselves (a key test, an agent test) */
    public const DIRECT_FEATURES = ['verify', 'agenttest'];

    /** Usage-log feature of the triage cascade's second call — the fallback agent */
    public const FEATURE_TRIAGE_FALLBACK = 'triage_strong';

    /** Efforts an agent can take (only where the provider API supports it) */
    public const EFFORTS = ['low', 'medium', 'high'];

    /**
     * The driver defaults before the agents (I13) — an installation with an empty model ran
     * on these; its default agents keep them (the drivers' defaults moved on since)
     */
    private const LEGACY_DEFAULT_MODELS = [
        MailManager_ProAiProvider_Helper::PROVIDER_CLAUDE => 'claude-sonnet-5-5',
        MailManager_ProAiProvider_Helper::PROVIDER_OPENAI => 'gpt-4o',
        MailManager_ProAiProvider_Helper::PROVIDER_GEMINI => 'gemini-2.5-flash',
    ];

    /** MySQL named lock that keeps two first visits from making the default agents twice */
    private const DEFAULTS_LOCK = 'df_mailmanager_aiagent_defaults';

    /** Input limit of a new agent, in tokens */
    public const DEFAULT_MAX_INPUT_TOKENS = 100000;

    /** Usage-log feature of the agent test */
    private const TEST_FEATURE = 'agenttest';

    /** Output budget of the agent test — a reasoning model thinks within it too */
    private const TEST_MAX_TOKENS = 2000;

    /** @var array|null agentid => row, read once per request */
    private static ?array $agents = null;

    /**
     * @return self table accessor of df_mailmanager_aiagent
     */
    public function getAgentTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @return array agentid => row, by sequence
     */
    public static function getAll(): array
    {
        if (null === self::$agents) {
            self::$agents = [];
            $adb = PearDatabase::getInstance();

            if (!Vtiger_Utils::CheckTable('df_mailmanager_aiagent')) {
                return self::$agents;
            }

            $result = $adb->pquery('SELECT * FROM df_mailmanager_aiagent ORDER BY sequence, agentid', []);

            while ($row = $adb->fetchByAssoc($result, -1, false)) {
                self::$agents[(int)$row['agentid']] = $row;
            }
        }

        return self::$agents;
    }

    /**
     * @param int $agentId
     *
     * @return array|null
     */
    public static function getById(int $agentId): ?array
    {
        return self::getAll()[$agentId] ?? null;
    }

    /**
     * Creates or updates an agent.
     *
     * @param array $data name, tier, active, provider, model, effort, instructions, max_input_tokens (+ agentid to update)
     *
     * @return int agentid
     * @throws Exception
     */
    public static function saveAgent(array $data): int
    {
        $provider = (string)($data['provider'] ?? '');
        $tier = (string)($data['tier'] ?? MailManager_ProAiCatalog_Model::TIER_FAST);
        $effort = (string)($data['effort'] ?? '');
        $row = [
            'name' => mb_substr(trim((string)($data['name'] ?? '')), 0, 100),
            'tier' => in_array($tier, MailManager_ProAiCatalog_Model::TIERS, true) ? $tier : MailManager_ProAiCatalog_Model::TIER_FAST,
            'active' => empty($data['active']) ? 0 : 1,
            'provider' => $provider,
            'model' => mb_substr(trim((string)($data['model'] ?? '')), 0, 100),
            'effort' => in_array($effort, self::EFFORTS, true) ? $effort : '',
            'instructions' => mb_substr(trim((string)($data['instructions'] ?? '')), 0, 4000),
            'max_input_tokens' => max(1000, min(1000000, (int)($data['max_input_tokens'] ?? self::DEFAULT_MAX_INPUT_TOKENS))),
            'modifiedtime' => date('Y-m-d H:i:s'),
        ];
        // the backup (I17): '' = the global one, 'none' = none, else a provider other than its own
        $backup = (string)($data['backup_provider'] ?? MailManager_ProAiFailover_Model::AGENT_GLOBAL);

        if (!in_array($backup, array_merge(
            [MailManager_ProAiFailover_Model::AGENT_GLOBAL, MailManager_ProAiFailover_Model::AGENT_NONE],
            MailManager_ProAiConfig_Model::PROVIDERS
        ), true) || $backup === $provider) {
            $backup = MailManager_ProAiFailover_Model::AGENT_GLOBAL;
        }

        $row['backup_provider'] = $backup;
        $row['backup_model'] = mb_substr(trim((string)($data['backup_model'] ?? '')), 0, 100);

        if ('' === $row['name'] || !in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true)) {
            throw new Exception(vtranslate('LBL_AGENT_INVALID', 'Settings:MailManager'));
        }

        $table = (new self())->getAgentTable();
        $agentId = (int)($data['agentid'] ?? 0);

        if ($agentId && self::getById($agentId)) {
            $table->updateData($row, ['agentid' => $agentId]);
        } else {
            $row['createdtime'] = $row['modifiedtime'];
            $row['sequence'] = count(self::getAll()) + 1;
            $agentId = (int)$table->insertData($row);
        }

        self::$agents = null;

        return $agentId;
    }

    /**
     * Deletes an agent; its tasks move to the first other agent.
     *
     * @param int $agentId
     *
     * @return bool false when it is the only agent (every task needs one)
     * @throws Exception
     */
    public static function deleteAgent(int $agentId): bool
    {
        $others = array_diff(array_keys(self::getAll()), [$agentId]);

        if (!self::getById($agentId) || !$others) {
            return false;
        }

        $replacement = (int)reset($others);

        // an active agent takes the tasks over when there is one
        foreach ($others as $otherId) {
            if (!empty(self::getAll()[$otherId]['active'])) {
                $replacement = (int)$otherId;
                break;
            }
        }

        foreach (array_keys(self::TASKS) as $task) {
            if ((int)MailManager_ProAiConfig_Model::get('ai_task_' . $task) === $agentId) {
                MailManager_ProAiConfig_Model::set('ai_task_' . $task, (string)$replacement);
            }
        }

        if ((int)MailManager_ProAiConfig_Model::get('ai_triage_fallback') === $agentId) {
            MailManager_ProAiConfig_Model::set('ai_triage_fallback', '');
        }

        (new self())->getAgentTable()->deleteData(['agentid' => $agentId]);
        self::$agents = null;

        return true;
    }

    /**
     * @param string $feature usage-log feature
     *
     * @return string the task it belongs to, '' for a key or agent test
     */
    public static function getTaskForFeature(string $feature): string
    {
        // a key or agent test belongs to no task
        if (in_array($feature, self::DIRECT_FEATURES, true)) {
            return '';
        }

        if (isset(self::TASKS[$feature])) {
            return $feature;
        }

        return self::FEATURE_TASKS[$feature] ?? ('triage_strong' === $feature ? 'triage' : 'draft');
    }

    /**
     * The task assignment of the settings
     *
     * @return array task => agentid (0 = none)
     */
    public static function getAssignments(): array
    {
        $assignments = [];

        foreach (array_keys(self::TASKS) as $task) {
            $assignments[$task] = (int)MailManager_ProAiConfig_Model::get('ai_task_' . $task);
        }

        return $assignments;
    }

    /**
     * Saves the task assignment of the agents, the triage cascade and its fallback agent
     * (an agent that does not exist is no fallback).
     *
     * @param array $assignments task => agentid
     * @param bool  $cascade     the triage tries the next agent when one fails
     * @param int   $fallback    fallback agent of the triage
     *
     * @return void
     */
    public static function saveTaskSettings(array $assignments, bool $cascade, int $fallback): void
    {
        self::saveAssignments($assignments);
        MailManager_ProAiConfig_Model::set('ai_triage_cascade', $cascade ? '1' : '0');
        MailManager_ProAiConfig_Model::set('ai_triage_fallback', self::getById($fallback) ? (string)$fallback : '');
    }

    /**
     * @param array $assignments task => agentid
     *
     * @return void
     */
    public static function saveAssignments(array $assignments): void
    {
        foreach (array_keys(self::TASKS) as $task) {
            $agentId = (int)($assignments[$task] ?? 0);

            if (self::getById($agentId)) {
                MailManager_ProAiConfig_Model::set('ai_task_' . $task, (string)$agentId);
            }
        }
    }

    /**
     * The agent a call runs on: the one of its task (the cascade asks the fallback
     * agent), an inactive or missing one falls back to the first active agent.
     *
     * @param string $feature usage-log feature
     *
     * @return array|null agent row, null when there is none
     */
    public static function getForFeature(string $feature): ?array
    {
        if (self::FEATURE_TRIAGE_FALLBACK === $feature) {
            $agent = self::getById((int)MailManager_ProAiConfig_Model::get('ai_triage_fallback'));

            if ($agent && $agent['active']) {
                return $agent;
            }
        }

        $agent = self::getById((int)MailManager_ProAiConfig_Model::get('ai_task_' . self::getTaskForFeature($feature)));

        if ($agent && $agent['active']) {
            return $agent;
        }

        foreach (self::getAll() as $candidate) {
            if ($candidate['active']) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array $agent agent row
     *
     * @return MailManager_ProAiProvider_Helper the driver of the agent with its provider's key
     * @throws Exception when the provider has no key
     */
    public static function getProvider(array $agent): MailManager_ProAiProvider_Helper
    {
        $apiKey = MailManager_ProAiConfig_Model::getApiKey((string)$agent['provider']);

        if ('' === $apiKey) {
            throw new Exception(sprintf(vtranslate('LBL_AGENT_NO_KEY', 'Settings:MailManager'), $agent['name']));
        }

        return MailManager_ProAiProvider_Helper::getInstance((string)$agent['provider'], $apiKey, (string)$agent['model']);
    }

    /**
     * The default agents "Fast" and "Quality" from the former provider and model, every
     * task given to the one of its type. Idempotent: an installation with agents keeps
     * them and only gets agents for tasks without one.
     *
     * @return void
     * @throws Exception
     */
    public static function ensureDefaults(): void
    {
        if (!Vtiger_Utils::CheckTable('df_mailmanager_aiagent')) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $lock = $adb->pquery('SELECT GET_LOCK(?, 10) AS got', [self::DEFAULTS_LOCK]);

        if (!(int)$adb->query_result($lock, 0, 'got')) {
            return;
        }

        try {
            self::$agents = null;
            self::createDefaults();
        } finally {
            $adb->pquery('SELECT RELEASE_LOCK(?)', [self::DEFAULTS_LOCK]);
        }
    }

    /**
     * ensureDefaults() under its lock
     *
     * @return void
     * @throws Exception
     */
    private static function createDefaults(): void
    {
        $agents = self::getAll();

        if (!$agents) {
            $provider = MailManager_ProAiConfig_Model::get('ai_provider') ?: MailManager_ProAiProvider_Helper::PROVIDER_CLAUDE;
            // an empty model ran on the driver default of that time — the agents keep it
            $model = MailManager_ProAiConfig_Model::get('ai_model') ?: (self::LEGACY_DEFAULT_MODELS[$provider] ?? '');

            // the single key of the time before the agents belongs to the former provider
            $legacyKey = MailManager_ProAiConfig_Model::getApiKey($provider);

            if ('' !== $legacyKey) {
                MailManager_ProAiConfig_Model::setApiKey($legacyKey, $provider);
            }

            foreach ([MailManager_ProAiCatalog_Model::TIER_FAST => 'LBL_AGENT_DEFAULT_FAST', MailManager_ProAiCatalog_Model::TIER_QUALITY => 'LBL_AGENT_DEFAULT_QUALITY'] as $tier => $label) {
                self::saveAgent([
                    'name' => vtranslate($label, 'Settings:MailManager'),
                    'tier' => $tier,
                    'active' => 1,
                    'provider' => $provider,
                    'model' => $model,
                    'max_input_tokens' => self::DEFAULT_MAX_INPUT_TOKENS,
                ]);
            }

            $agents = self::getAll();
        }

        $byTier = [];

        foreach ($agents as $agentId => $agent) {
            $byTier[$agent['tier']] = $byTier[$agent['tier']] ?? $agentId;
        }

        $first = (int)array_key_first($agents);

        foreach (self::TASKS as $task => $tier) {
            if (!self::getById((int)MailManager_ProAiConfig_Model::get('ai_task_' . $task))) {
                MailManager_ProAiConfig_Model::set('ai_task_' . $task, (string)($byTier[$tier] ?? $first));
            }
        }
    }

    /**
     * Everything the agent settings show: the providers (key stored, verified models
     * without old ones, the catalog per agent type), the agents with how their model
     * fits their type, and the tasks with their agent.
     *
     * @return array ['providers' => provider => [...], 'agents' => [...], 'tasks' => [...]]
     */
    public static function getSettingsData(): array
    {
        $providers = [];

        foreach (MailManager_ProAiConfig_Model::PROVIDERS as $provider) {
            $cached = json_decode(MailManager_ProAiConfig_Model::get('ai_models_' . $provider), true);
            $cached = is_array($cached) ? $cached : [];
            $verified = array_column($cached, 'id');
            $catalog = [];

            foreach (MailManager_ProAiCatalog_Model::TIERS as $tier) {
                $catalog[$tier] = MailManager_ProAiCatalog_Model::getRecommended($provider, $tier, $verified);
            }

            $providers[$provider] = [
                'label' => vtranslate('LBL_PROVIDER_NAME_' . strtoupper($provider), 'Settings:MailManager'),
                'has_key' => MailManager_ProAiConfig_Model::hasApiKey($provider),
                'verified_count' => count($verified),
                'models' => array_column(MailManager_ProAiProvider_Helper::filterCurrent($provider, $cached), 'id'),
                'catalog' => $catalog,
                'effort' => MailManager_ProAiProvider_Helper::PROVIDER_GEMINI !== $provider,
            ];
        }

        $agents = [];

        foreach (self::getAll() as $agentId => $agent) {
            $fit = '' === (string)$agent['model']
                ? 'no_model'
                : MailManager_ProAiCatalog_Model::getFit((string)$agent['provider'], (string)$agent['tier'], (string)$agent['model']);
            $agents[] = $agent + [
                'provider_label' => $providers[$agent['provider']]['label'] ?? $agent['provider'],
                'tier_label' => vtranslate('LBL_AGENT_TIER_' . strtoupper((string)$agent['tier']), 'Settings:MailManager'),
                'fit' => $fit,
                'fit_label' => '' === $fit ? '' : vtranslate('LBL_MODEL_FIT_' . strtoupper($fit), 'Settings:MailManager'),
                'has_key' => $providers[$agent['provider']]['has_key'] ?? false,
            ];
        }

        $tasks = [];
        $assignments = self::getAssignments();

        foreach (array_keys(self::TASKS) as $task) {
            $tasks[] = [
                'task' => $task,
                'label' => vtranslate('LBL_AI_TASK_' . strtoupper($task), 'Settings:MailManager'),
                'agentid' => $assignments[$task],
            ];
        }

        return ['providers' => $providers, 'agents' => $agents, 'tasks' => $tasks];
    }

    /**
     * Tests an agent with a short sorting task (Settings → AI agents): the answer, the
     * model, the time and the tokens. A simulated outage of the provider is not tested.
     *
     * @param array $agent agent row of getById()
     *
     * @return array ['success' => true, 'text', 'model', 'ms', 'input_tokens', 'output_tokens'] or
     *               ['success' => false, 'message']
     */
    public static function testAgent(array $agent): array
    {
        if (MailManager_ProAiFailover_Model::isSimulated((string)$agent['provider'])) {
            return ['success' => false, 'message' => vtranslate('LBL_AGENT_TEST_SIMULATED', 'Settings:MailManager')];
        }

        $started = microtime(true);

        try {
            $result = MailManager_ProAiClient_Model::complete(
                self::getProvider($agent),
                self::TEST_FEATURE,
                0,
                'You sort incoming customer email. Answer with one JSON object only: '
                    . '{"label": "Support"|"Sales"|"Other", "priority": "urgent"|"high"|"normal"|"low", "summary": "<one sentence>"}',
                "From: jana.novak@example.com\nSubject: Invoice 2026-104 missing\n\n"
                    . 'Hello, we paid last week but have not received invoice 2026-104 yet. Could you send it today? Thank you, Jana',
                self::TEST_MAX_TOKENS,
                null,
                $agent
            );

            return [
                'success' => true,
                'text' => mb_substr((string)$result['text'], 0, 1000),
                'model' => (string)$agent['model'],
                'ms' => (int)round((microtime(true) - $started) * 1000),
                'input_tokens' => (int)$result['input_tokens'],
                'output_tokens' => (int)$result['output_tokens'],
            ];
        } catch (Exception $e) {
            $message = MailManager_ProAiFailover_Model::isOpen((string)$agent['provider'])
                ? vtranslate('LBL_AGENT_TEST_OUTAGE', 'Settings:MailManager') . ' ' . $e->getMessage()
                : $e->getMessage();

            return ['success' => false, 'message' => $message];
        }
    }

    /**
     * After a key check: the agents of the provider without a model get the recommended
     * model of their type the verified key can use.
     *
     * @param string $provider PROVIDER_*
     * @param array  $verified model ids the key can use
     *
     * @return int agents filled
     * @throws Exception
     */
    public static function savePrefilledModels(string $provider, array $verified): int
    {
        $filled = 0;

        foreach (self::getAll() as $agentId => $agent) {
            $model = MailManager_ProAiCatalog_Model::getRecommendedModel($provider, (string)$agent['tier'], $verified);

            if ($provider === $agent['provider'] && '' === (string)$agent['model'] && '' !== $model) {
                self::saveAgent(['agentid' => $agentId, 'model' => $model] + $agent);
                $filled++;
            }
        }

        return $filled;
    }

    /**
     * "Use recommended": the agents move to a provider and get the catalog model of their
     * type the provider's verified key can use.
     *
     * @param string $provider PROVIDER_*
     *
     * @return int agents changed
     * @throws Exception
     */
    public static function applyRecommended(string $provider): int
    {
        $verified = array_column((array)json_decode(MailManager_ProAiConfig_Model::get('ai_models_' . $provider), true), 'id');
        $changed = 0;

        foreach (self::getAll() as $agentId => $agent) {
            $model = MailManager_ProAiCatalog_Model::getRecommendedModel($provider, (string)$agent['tier'], $verified);

            if ('' === $model) {
                continue;
            }

            // an effort of another provider's API may not apply here
            self::saveAgent(['agentid' => $agentId, 'provider' => $provider, 'model' => $model, 'effort' => ''] + $agent);
            $changed++;
        }

        return $changed;
    }
}
