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
 * The AI Assistant settings page of Mail Manager Pro: saving the switches and checking
 * an API key with its provider. The values themselves live in MailManager_ProAiConfig_Model.
 */
class MailManager_ProAiSettings_Model
{
    /** Usage-log feature of the test completion of a key check */
    private const USAGE_FEATURE = 'verify';

    /** Deterministic switches that work without an AI key */
    private const DETERMINISTIC_SWITCHES = [
        'ai_feature_spamlearn',
        'ai_feature_autocomment',
        'ai_feature_emailfiling',
        'ai_feature_similar',
        'ai_feature_knowledge',
        'ai_knowledge_faq',
        'ai_knowledge_tickets',
        // the history of the linked records in the prompts (I20) — no call of its own
        MailManager_ProRecordHistory_Model::SWITCH,
    ];

    /** The highest token cap of the records' history the admin can set */
    private const MAX_HISTORY_TOKENS = 1000000;

    /**
     * Returns the fields the settings form sends (purified values; the priority rules
     * and the API key are read raw).
     *
     * @return string[]
     */
    public static function getSettingsFields(): array
    {
        $fields = ['ai_provider', 'ai_enabled', 'ai_model', 'ai_anonymize', 'ai_triage_mode', 'ai_autocreate_limit', 'list_page_size',
            MailManager_ProRecordHistory_Model::TOKEN_CAP];

        foreach (MailManager_ProAiConfig_Model::FEATURES as $feature) {
            $fields[] = 'ai_feature_' . $feature;
        }

        return array_merge($fields, self::DETERMINISTIC_SWITCHES);
    }

    /**
     * The provider the settings are saved for – the provider and the model belong to
     * the agents now (I13); an old form still sends them.
     *
     * @param array $values sent fields of getSettingsFields()
     *
     * @return string the provider, '' when it is not a known one
     */
    public static function getSettingsProvider(array $values): string
    {
        $provider = empty($values['ai_provider']) ? MailManager_ProAiConfig_Model::get('ai_provider') : (string)$values['ai_provider'];

        return in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true) ? $provider : '';
    }

    /**
     * Saves the AI Assistant settings. The API key is stored encrypted and only when the
     * administrator typed a new one.
     *
     * @param string $provider      of getSettingsProvider()
     * @param array  $values        sent fields of getSettingsFields(), a missing one is ''
     * @param string $priorityRules raw priority rules
     * @param string $apiKey        raw typed API key, '' = keep the current one
     *
     * @return void
     */
    public static function saveSettings(string $provider, array $values, string $priorityRules, string $apiKey): void
    {
        $value = function (string $field) use ($values) {
            return $values[$field] ?? '';
        };

        // new settings may be the fix of whatever the provider refused (another
        // key, another provider) - the next call finds out, nothing waits it out
        MailManager_ProAiBlock_Model::clear();
        MailManager_ProAiConfig_Model::set('ai_enabled', '1' === $value('ai_enabled') ? '1' : '0');
        MailManager_ProAiConfig_Model::set('ai_provider', $provider);

        if (isset($values['ai_model'])) {
            MailManager_ProAiConfig_Model::set('ai_model', trim((string)$values['ai_model']));
        }

        // personal data replaced by placeholders before every AI call
        $anonymize = (string)$value('ai_anonymize');
        MailManager_ProAiConfig_Model::set(
            'ai_anonymize',
            in_array($anonymize, MailManager_ProAnonymizer_Helper::LEVELS, true) ? $anonymize : MailManager_ProAnonymizer_Helper::LEVEL_OFF
        );
        // auto-creation of Leads/Tickets only after the admin switches the mode (decision D15)
        MailManager_ProAiConfig_Model::set('ai_triage_mode', 'auto' === $value('ai_triage_mode') ? 'auto' : 'suggest');
        MailManager_ProAiConfig_Model::set(
            'ai_autocreate_limit',
            (string)max(0, min(1000, (int)$value('ai_autocreate_limit')))
        );

        // the radar's start — the triage only asks for request_type from now on (I19 digest)
        if ('1' === $value('ai_feature_' . MailManager_ProAiConfig_Model::FEATURE_RADAR)
            && !MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_RADAR)) {
            MailManager_ProAiConfig_Model::set(MailManager_ProRadar_Model::SINCE_KEY, date('Y-m-d H:i:s'));
        }

        foreach (MailManager_ProAiConfig_Model::FEATURES as $feature) {
            MailManager_ProAiConfig_Model::set(
                'ai_feature_' . $feature,
                '1' === $value('ai_feature_' . $feature) ? '1' : '0'
            );
        }

        MailManager_ProAiConfig_Model::set('ai_priority_rules', trim($priorityRules));
        // conversations per page of the inbox list
        $pageSize = (int)$value('list_page_size');

        if (in_array($pageSize, MailManager_ProThreadList_Model::PAGE_SIZES, true)) {
            MailManager_ProAiConfig_Model::set(MailManager_ProThreadList_Model::CONFIG_PAGE_SIZE, (string)$pageSize);
        }

        foreach (self::DETERMINISTIC_SWITCHES as $switch) {
            MailManager_ProAiConfig_Model::set($switch, '1' === $value($switch) ? '1' : '0');
        }

        // the token cap of the records' history, empty = a share of the agent's input limit
        $historyTokens = (int)$value(MailManager_ProRecordHistory_Model::TOKEN_CAP);
        MailManager_ProAiConfig_Model::set(
            MailManager_ProRecordHistory_Model::TOKEN_CAP,
            $historyTokens > 0 ? (string)min(self::MAX_HISTORY_TOKENS, $historyTokens) : ''
        );

        // Triage never classifies mail older than the moment it was enabled
        // (token budget guard); remember that moment on the first enable
        if ('1' === $value('ai_enabled')
            && '1' === $value('ai_feature_triage')
            && '' === MailManager_ProAiConfig_Model::get('ai_triage_since')) {
            MailManager_ProAiConfig_Model::set('ai_triage_since', date('Y-m-d H:i:s'));
        }

        // The key is stored only when the admin typed a new one (empty = keep current)
        $apiKey = trim($apiKey);

        if ('' !== $apiKey) {
            MailManager_ProAiConfig_Model::setApiKey($apiKey, $provider);
        }
    }

    /**
     * Tests an API key with the provider and returns the available models. The verified
     * model list is cached per provider (it fills the model list after a reload), a typed
     * key is stored and the agents of the provider without a model get the recommended one.
     *
     * @param string $provider PROVIDER_*
     * @param string $typedKey typed key, '' = verify the stored key
     *
     * @return array ['success', 'models', 'recommended', 'prefilled', 'message'] or ['success' => false, 'message']
     */
    public static function verifyApiKey(string $provider, string $typedKey): array
    {
        $typedKey = trim($typedKey);
        // Empty key field = verify the stored key
        $apiKey = '' !== $typedKey ? $typedKey : MailManager_ProAiConfig_Model::getApiKey($provider);

        if ('' === $apiKey) {
            return ['success' => false, 'message' => vtranslate('LBL_MISSING_API_KEY', 'Settings:MailManager')];
        }

        try {
            $result = MailManager_ProAiProvider_Helper::getInstance($provider, $apiKey)->verify();
            $verified = array_column($result['models'], 'id');
            // the test completion runs on the recommended fast model the key can use
            $testModel = MailManager_ProAiCatalog_Model::getRecommendedModel($provider, MailManager_ProAiCatalog_Model::TIER_FAST, $verified);
            $driver = MailManager_ProAiProvider_Helper::getInstance($provider, $apiKey, $testModel);

            if ($result['success']) {
                // The model list endpoint is free, only a real completion reveals
                // billing problems. Reasoning models think from the output budget,
                // a tiny one would end in an empty answer and a false "invalid"
                try {
                    MailManager_ProAiClient_Model::complete($driver, self::USAGE_FEATURE, 0, '', 'Reply with the single word OK.', 1000);
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            }

            $prefilled = 0;

            if ($result['success']) {
                MailManager_ProAiConfig_Model::set('ai_models_' . $provider, json_encode($result['models'], JSON_UNESCAPED_UNICODE));

                if ('' !== $typedKey) {
                    MailManager_ProAiConfig_Model::setApiKey($typedKey, $provider);
                }

                $prefilled = MailManager_ProAiAgent_Model::savePrefilledModels($provider, $verified);
            }

            return [
                'success' => $result['success'],
                'models' => MailManager_ProAiProvider_Helper::filterCurrent($provider, $result['models']),
                'recommended' => [
                    MailManager_ProAiCatalog_Model::TIER_FAST => MailManager_ProAiCatalog_Model::getRecommended($provider, MailManager_ProAiCatalog_Model::TIER_FAST, $verified),
                    MailManager_ProAiCatalog_Model::TIER_QUALITY => MailManager_ProAiCatalog_Model::getRecommended($provider, MailManager_ProAiCatalog_Model::TIER_QUALITY, $verified),
                    MailManager_ProAiCatalog_Model::TIER_DEEP => MailManager_ProAiCatalog_Model::getRecommended($provider, MailManager_ProAiCatalog_Model::TIER_DEEP, $verified),
                ],
                'prefilled' => $prefilled,
                'message' => $result['message'],
            ];
        } catch (Exception $e) {
            // a transport error of the provider is shown to the admin; the key is redacted by the driver
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Everything the AI Assistant settings page shows. An installation from before the
     * agents gets its two default agents on the first visit.
     *
     * @param int  $radarDays period of the request radar asked for, another value = the default one
     * @param bool $radarOpen a period link opens the radar tab
     *
     * @return array template variables
     * @throws Exception
     */
    public static function getSettingsViewData(int $radarDays, bool $radarOpen): array
    {
        $config = MailManager_ProAiConfig_Model::getAll();
        // an installation from before the agents gets its two default agents on the first visit
        MailManager_ProAiAgent_Model::ensureDefaults();
        $agentData = MailManager_ProAiAgent_Model::getSettingsData();
        $usage = MailManager_ProAiConfig_Model::getUsageSummary();
        $radarDays = in_array($radarDays, MailManager_ProRadar_Model::PERIODS, true)
            ? $radarDays
            : MailManager_ProRadar_Model::PERIODS[1];

        return [
            'CONFIG' => $config,
            'PAGE_SIZE' => MailManager_ProThreadList_Model::getPageSize(),
            'PAGE_SIZES' => MailManager_ProThreadList_Model::PAGE_SIZES,
            'HAS_API_KEY' => MailManager_ProAiConfig_Model::hasApiKey(),
            // AI agents (I13): keys per provider, the agents and the task assignment
            'AI_PROVIDERS' => $agentData['providers'],
            'AI_AGENTS' => $agentData['agents'],
            'AI_TASKS' => $agentData['tasks'],
            'AI_AGENTS_JSON' => json_encode([
                'providers' => $agentData['providers'],
                'agents' => $agentData['agents'],
                'efforts' => MailManager_ProAiAgent_Model::EFFORTS,
                'tiers' => MailManager_ProAiCatalog_Model::TIERS,
                'backup_provider' => MailManager_ProAiConfig_Model::get(MailManager_ProAiFailover_Model::KEY_PROVIDER),
            ], JSON_UNESCAPED_UNICODE),
            'AI_USAGE' => $usage,
            // the request radar — a period link opens its tab
            'RADAR_OPEN' => $radarOpen,
            'RADAR_PERIODS' => MailManager_ProRadar_Model::PERIODS,
            'RADAR_DAYS' => $radarDays,
            'RADAR' => MailManager_ProRadar_Model::getRecurring($radarDays),
            'AI_ANONYMIZE_LEVELS' => MailManager_ProAnonymizer_Helper::LEVELS,
            // the backup provider and the outages (I17)
            'AI_FAILOVER' => [
                'provider' => MailManager_ProAiConfig_Model::get(MailManager_ProAiFailover_Model::KEY_PROVIDER),
                'confirmed' => '1' === MailManager_ProAiConfig_Model::get(MailManager_ProAiFailover_Model::KEY_CONFIRMED),
                'email' => MailManager_ProAiConfig_Model::get(MailManager_ProAiFailover_Model::KEY_EMAIL),
            ],
            'AI_OUTAGE_STATUS' => MailManager_ProAiFailover_Model::getStatus(),
            'AI_OUTAGE_HISTORY' => MailManager_ProAiFailover_Model::getHistory(),
            'AI_PRICES' => MailManager_ProAiConfig_Model::getPrices(),
            'AI_PRICE_MODELS' => MailManager_ProAiConfig_Model::getPriceModels($usage),
            'LABELS' => Settings_MailManager_Label_Model::getGlobal(),
            'LABEL_PALETTE' => Settings_MailManager_Label_Model::PALETTE,
            'SUGGESTED_OPTIONS' => Settings_MailManager_Label_Model::getSuggestedOptions(),
            'LABEL_STRINGS_JSON' => Settings_MailManager_Label_Model::getEditorStringsJson(),
            'KNOWLEDGE_FILES_JSON' => json_encode(
                MailManager_ProKnowledge_Model::getFiles(MailManager_ProKnowledge_Model::SCOPE_GENERAL, 0),
                JSON_UNESCAPED_UNICODE
            ),
        ];
    }
}
