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
 * The backup AI provider (I17). When the provider of an agent fails (network, timeout,
 * HTTP 5xx / 529, a rate limit after the one short retry, a rejected key or a spent
 * credit) the call runs at the backup provider. A circuit breaker per provider: after
 * FAILURES_TO_OPEN failures within FAILURE_WINDOW the provider counts as unavailable,
 * calls go straight to the backup and one call per PROBE_INTERVAL tries the provider
 * again; its success ends the outage. The state lives in df_mailmanager_config
 * (ai_outage_<provider>), every change of it under one named lock; the outages are
 * kept in df_mailmanager_aioutage. The administrator gets one e-mail when an outage
 * starts and one when it ends; users see a bar in the Mail Manager.
 */
class MailManager_ProAiFailover_Model extends Core_DatabaseData_Model
{
    /** Failures within FAILURE_WINDOW that make a provider unavailable */
    public const FAILURES_TO_OPEN = 3;
    public const FAILURE_WINDOW = 300;

    /** An unavailable provider is tried again once per this many seconds */
    public const PROBE_INTERVAL = 300;

    /** The one short retry before the switch waits at most this long */
    public const RETRY_WAIT_MAX = 5;

    /** "Simulate an outage" lasts this long */
    public const SIMULATION_SECONDS = 900;

    /** Settings: the backup provider of all agents ('' = none), the admin's confirmation, the notice address */
    public const KEY_PROVIDER = 'ai_failover_provider';
    public const KEY_CONFIRMED = 'ai_failover_confirmed';
    public const KEY_EMAIL = 'ai_failover_email';

    /** An agent without a backup of its own takes the global one; NONE = never a backup */
    public const AGENT_GLOBAL = '';
    public const AGENT_NONE = 'none';

    /** Why a provider failed */
    public const REASON_NETWORK = 'network';
    public const REASON_SERVER = 'server';
    public const REASON_LIMIT = 'limit';
    public const REASON_AUTH = 'auth';
    public const REASON_CREDIT = 'credit';
    public const REASON_SIMULATED = 'simulated';

    private const KEY_STATE = 'ai_outage_';
    private const KEY_SIMULATE = 'ai_outage_simulate_';
    private const LOCK = 'df_mailmanager_ai_failover';
    private const HISTORY_LIMIT = 30;

    /**
     * @return self table accessor of df_mailmanager_aioutage
     */
    public function getOutageTable(): self
    {
        return $this->getTable('df_mailmanager_aioutage', 'outageid');
    }

    /**
     * The backup of an agent: its own choice, else the global one — only after the
     * admin's confirmation, never the agent's own provider, only with a key.
     *
     * @param array $agent agent row
     *
     * @return array|null ['provider' => PROVIDER_*, 'model' => model id]
     */
    public static function getBackup(array $agent): ?array
    {
        if ('1' !== MailManager_ProAiConfig_Model::get(self::KEY_CONFIRMED)) {
            return null;
        }

        $provider = (string)($agent['backup_provider'] ?? self::AGENT_GLOBAL);

        if (self::AGENT_NONE === $provider) {
            return null;
        }

        if (self::AGENT_GLOBAL === $provider) {
            $provider = MailManager_ProAiConfig_Model::get(self::KEY_PROVIDER);
        }

        if ('' === $provider || $provider === (string)$agent['provider']
            || !in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true)
            || !MailManager_ProAiConfig_Model::hasApiKey($provider)) {
            return null;
        }

        $model = trim((string)($agent['backup_model'] ?? ''));
        $verified = json_decode(MailManager_ProAiConfig_Model::get('ai_models_' . $provider), true);
        $verified = is_array($verified) ? array_column($verified, 'id') : [];

        // a model picked for another provider (the global backup changed since) is not sent
        if ('' !== $model && !self::isModelOf($provider, $model, $verified)) {
            $model = '';
        }

        if ('' === $model) {
            // the catalog model of the agent's type the key really offers ('' = the provider's default)
            $model = MailManager_ProAiCatalog_Model::getRecommendedModel(
                $provider,
                (string)($agent['tier'] ?? MailManager_ProAiCatalog_Model::TIER_FAST),
                $verified
            );
        }

        return ['provider' => $provider, 'model' => $model];
    }

    /**
     * @param string   $provider
     * @param string   $model
     * @param string[] $verified the models the provider's key listed
     *
     * @return bool the model is one of the provider (its key's list or the catalog)
     */
    private static function isModelOf(string $provider, string $model, array $verified): bool
    {
        if (in_array($model, $verified, true)) {
            return true;
        }

        foreach (MailManager_ProAiCatalog_Model::TIERS as $tier) {
            if (in_array($model, array_column(MailManager_ProAiCatalog_Model::getRecommended($provider, $tier), 'id'), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Saves the backup provider settings (I17): the provider (with a stored key, the
     * admin confirmed the data transfer), the notice addresses. No provider ends the
     * open outages.
     *
     * @param string $provider  PROVIDER_*, '' = no backup
     * @param bool   $confirmed the admin confirmed sending the mail to the backup
     * @param string $emails    comma separated notice addresses
     *
     * @return string the error for the admin, '' when saved
     */
    public static function saveSettings(string $provider, bool $confirmed, string $emails): string
    {
        if ('' !== $provider
            && (!in_array($provider, MailManager_ProAiConfig_Model::PROVIDERS, true) || !MailManager_ProAiConfig_Model::hasApiKey($provider) || !$confirmed)) {
            return vtranslate('LBL_AI_FAILOVER_INVALID', 'Settings:MailManager');
        }

        $emails = array_filter(array_map('trim', explode(',', $emails)));

        foreach ($emails as $email) {
            if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return vtranslate('LBL_AI_FAILOVER_EMAIL_INVALID', 'Settings:MailManager');
            }
        }

        if ('' === $provider) {
            self::endAll();
        }

        MailManager_ProAiConfig_Model::set(self::KEY_PROVIDER, $provider);
        MailManager_ProAiConfig_Model::set(self::KEY_CONFIRMED, $confirmed ? '1' : '0');
        MailManager_ProAiConfig_Model::set(self::KEY_EMAIL, implode(', ', $emails));

        return '';
    }

    /**
     * The backup is switched off: open outages end without a notice (nothing runs on a
     * backup any more), so neither the bar nor the history keeps saying it does.
     *
     * @return void
     */
    public static function endAll(): void
    {
        foreach (MailManager_ProAiConfig_Model::PROVIDERS as $provider) {
            self::withLock(function () use ($provider) {
                $state = self::readState($provider);
                self::writeState($provider, []);

                if (!empty($state['outageid'])) {
                    PearDatabase::getInstance()->pquery(
                        'UPDATE df_mailmanager_aioutage SET ended_at = ? WHERE outageid = ? AND ended_at IS NULL',
                        [date('Y-m-d H:i:s'), (int)$state['outageid']]
                    );
                }

                return null;
            });
        }
    }

    /**
     * @param string $provider
     *
     * @return bool a backup can stand in for the provider — "Simulate an outage" makes sense
     */
    public static function canSimulate(string $provider): bool
    {
        $backup = MailManager_ProAiConfig_Model::get(self::KEY_PROVIDER);

        return '1' === MailManager_ProAiConfig_Model::get(self::KEY_CONFIRMED)
            && '' !== $backup && $backup !== $provider && MailManager_ProAiConfig_Model::hasApiKey($backup);
    }

    /**
     * @param array $backup getBackup()
     *
     * @return MailManager_ProAiProvider_Helper
     * @throws Exception
     */
    public static function getBackupDriver(array $backup): MailManager_ProAiProvider_Helper
    {
        return MailManager_ProAiProvider_Helper::getInstance(
            $backup['provider'],
            MailManager_ProAiConfig_Model::getApiKey($backup['provider']),
            $backup['model']
        );
    }

    /**
     * @param Throwable $error what the provider call threw
     *
     * @return string REASON_* when the provider is at fault, '' for an error of the request itself (400, content)
     */
    public static function getFailureReason(Throwable $error): string
    {
        if ($error instanceof MailManager_ProAiUnavailableException_Helper) {
            if (self::REASON_SIMULATED === $error->getMessage()) {
                return self::REASON_SIMULATED;
            }

            return 0 === (int)$error->getCode() ? self::REASON_NETWORK : self::REASON_SERVER;
        }

        if ($error instanceof MailManager_ProAiAccountException_Helper) {
            return $error->getReason();
        }

        return '';
    }

    /**
     * @param string $reason REASON_*
     * @param int    $status HTTP status of the failure
     *
     * @return bool one short retry may help: a rate limit (429) or an overloaded provider (529) —
     *              a timeout or another server error switches at once (no long waits)
     */
    public static function isRetriable(string $reason, int $status = 0): bool
    {
        return self::REASON_LIMIT === $reason || (self::REASON_SERVER === $reason && 529 === $status);
    }

    /**
     * @param Throwable $error
     *
     * @return int seconds to wait before the one retry (Retry-After, at most RETRY_WAIT_MAX)
     */
    public static function getRetryWait(Throwable $error): int
    {
        $wait = method_exists($error, 'getRetryAfter') ? (int)$error->getRetryAfter() : 0;

        return max(1, min(self::RETRY_WAIT_MAX, $wait ?: 2));
    }

    /**
     * @param string $provider
     *
     * @return bool the provider counts as unavailable (calls go to the backup)
     */
    public static function isOpen(string $provider): bool
    {
        return !empty(self::readState($provider)['open']);
    }

    /**
     * One call per PROBE_INTERVAL of an unavailable provider tries it again — the first
     * caller after the interval wins the probe, the others go to the backup.
     *
     * @param string $provider
     *
     * @return bool this call tries the provider
     */
    public static function claimProbe(string $provider): bool
    {
        return (bool)self::withLock(function () use ($provider) {
            $state = self::readState($provider);

            if (empty($state['open']) || time() - (int)($state['last_probe'] ?? 0) < self::PROBE_INTERVAL) {
                return false;
            }

            $state['last_probe'] = time();
            self::writeState($provider, $state);

            return true;
        });
    }

    /**
     * A failed call of the provider. The third failure within the window opens the outage
     * (the history row, the e-mail to the administrator).
     *
     * @param string $provider
     * @param string $backup   the backup provider that takes over
     * @param string $reason   REASON_*
     * @param string $message  the provider's words (never the key, never mail content)
     *
     * @return void
     */
    public static function recordFailure(string $provider, string $backup, string $reason, string $message): void
    {
        $opened = self::withLock(function () use ($provider, $backup, $reason, $message) {
            $now = time();
            $state = self::readState($provider);
            $failures = array_values(array_filter((array)($state['failures'] ?? []), function ($time) use ($now) {
                return $now - (int)$time < self::FAILURE_WINDOW;
            }));
            $failures[] = $now;
            $state['failures'] = array_slice($failures, -10);
            $state['error'] = mb_substr($message, 0, 250);
            $state['reason'] = $reason;
            $opened = null;

            // a rejected key or a spent credit fails every call alike — no need to wait for three
            $opens = count($failures) >= self::FAILURES_TO_OPEN || in_array($reason, [self::REASON_AUTH, self::REASON_CREDIT], true);

            if (empty($state['open']) && $opens) {
                $table = (new self())->getOutageTable();
                $state['open'] = true;
                $state['since'] = $now;
                $state['last_probe'] = $now;
                $state['backup'] = $backup;
                $state['outageid'] = (int)$table->insertData([
                    'provider' => $provider,
                    'backup_provider' => $backup,
                    'started_at' => date('Y-m-d H:i:s', $now),
                    'reason' => $reason,
                    'last_error' => $state['error'],
                    'failures' => count($failures),
                    'backup_calls' => 0,
                ]);
                $opened = $state;
            } elseif (!empty($state['open']) && !empty($state['outageid'])) {
                $adb = PearDatabase::getInstance();
                $adb->pquery(
                    'UPDATE df_mailmanager_aioutage SET failures = failures + 1, last_error = ?, reason = ? WHERE outageid = ?',
                    [$state['error'], $reason, (int)$state['outageid']]
                );
            }

            self::writeState($provider, $state);

            return $opened;
        });

        if ($opened) {
            MailManager_ProAiFailoverNotice_Model::send(MailManager_ProAiFailoverNotice_Model::TYPE_START, $provider, $opened);
        }
    }

    /**
     * A successful call of the provider: the failures are forgotten; an open outage ends
     * (the history row, the e-mail to the administrator) only on the probe's success.
     *
     * @param string $provider
     * @param bool   $closes   the call was the probe (or ran without a backup)
     *
     * @return void
     */
    public static function recordSuccess(string $provider, bool $closes = false): void
    {
        $state = self::readState($provider);

        if (empty($state['open']) && empty($state['failures'])) {
            return;
        }

        $closed = self::withLock(function () use ($provider, $closes) {
            $state = self::readState($provider);

            if (!empty($state['open']) && !$closes) {
                return null;
            }

            self::writeState($provider, []);

            if (empty($state['open'])) {
                return null;
            }

            if (!empty($state['outageid'])) {
                PearDatabase::getInstance()->pquery(
                    'UPDATE df_mailmanager_aioutage SET ended_at = ? WHERE outageid = ? AND ended_at IS NULL',
                    [date('Y-m-d H:i:s'), (int)$state['outageid']]
                );
            }

            return $state;
        });

        if ($closed) {
            MailManager_ProAiFailoverNotice_Model::send(MailManager_ProAiFailoverNotice_Model::TYPE_END, $provider, $closed);
        }
    }

    /**
     * Counts a call the backup made during an outage of the provider.
     *
     * @param string $provider
     *
     * @return void
     */
    public static function recordBackupCall(string $provider): void
    {
        $outageId = (int)(self::readState($provider)['outageid'] ?? 0);

        if ($outageId) {
            PearDatabase::getInstance()->pquery(
                'UPDATE df_mailmanager_aioutage SET backup_calls = backup_calls + 1 WHERE outageid = ?',
                [$outageId]
            );
        }
    }

    /**
     * "Simulate an outage" (AI settings): for SIMULATION_SECONDS every call of the
     * provider fails as if it were down — the switch, the bar and the e-mails can be tried.
     *
     * @param string $provider
     * @param bool   $on       false ends the simulation (the next call ends the outage)
     *
     * @return void
     */
    public static function simulate(string $provider, bool $on): void
    {
        MailManager_ProAiConfig_Model::set(self::KEY_SIMULATE . $provider, $on ? (string)(time() + self::SIMULATION_SECONDS) : '');

        if (!$on && self::isOpen($provider)) {
            // the provider is fine — the next probe may come at once
            self::withLock(function () use ($provider) {
                $state = self::readState($provider);
                $state['last_probe'] = 0;
                self::writeState($provider, $state);

                return null;
            });
        }
    }

    /**
     * @param string $provider
     *
     * @return bool every call of the provider fails on purpose now
     */
    public static function isSimulated(string $provider): bool
    {
        return time() < (int)(new MailManager_ProConfig_Model())->getValue(self::KEY_SIMULATE . $provider);
    }

    /**
     * The bar of the Mail Manager: the first provider that is unavailable now.
     *
     * @return array|null ['text' => "Main AI (Claude) is unavailable since 12:40 – the backup (OpenAI) runs."]
     */
    public static function describeOutage(): ?array
    {
        foreach (MailManager_ProAiConfig_Model::PROVIDERS as $provider) {
            $state = self::readState($provider);

            if (empty($state['open'])) {
                continue;
            }

            return [
                'provider' => $provider,
                'text' => sprintf(
                    vtranslate('LBL_AI_OUTAGE_BAR', 'MailManager'),
                    self::getProviderLabel($provider),
                    self::formatTime((int)$state['since']),
                    self::getProviderLabel((string)($state['backup'] ?? ''))
                ),
            ];
        }

        return null;
    }

    /**
     * The Status tab of the AI settings: each provider with a key.
     *
     * @return array of ['provider', 'label', 'open', 'since', 'failures', 'error', 'simulated', 'backup_label']
     */
    public static function getStatus(): array
    {
        $status = [];

        foreach (MailManager_ProAiConfig_Model::PROVIDERS as $provider) {
            if (!MailManager_ProAiConfig_Model::hasApiKey($provider)) {
                continue;
            }

            $state = self::readState($provider);
            $recent = array_filter((array)($state['failures'] ?? []), function ($time) {
                return time() - (int)$time < self::FAILURE_WINDOW;
            });
            $status[] = [
                'provider' => $provider,
                'label' => self::getProviderLabel($provider),
                'open' => !empty($state['open']),
                'since' => !empty($state['open']) ? self::formatTime((int)$state['since']) : '',
                'failures' => count($recent),
                'error' => (string)($state['error'] ?? ''),
                'reason_label' => '' !== (string)($state['reason'] ?? '')
                    ? vtranslate('LBL_AI_OUTAGE_REASON_' . strtoupper((string)$state['reason']), 'Settings:MailManager')
                    : '',
                'simulated' => self::isSimulated($provider),
                'can_simulate' => self::canSimulate($provider),
                'backup_label' => self::getProviderLabel((string)($state['backup'] ?? '')),
            ];
        }

        return $status;
    }

    /**
     * @return array the latest outages, newest first, with their duration
     */
    public static function getHistory(): array
    {
        if (!Vtiger_Utils::CheckTable('df_mailmanager_aioutage')) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_aioutage ORDER BY outageid DESC LIMIT ' . self::HISTORY_LIMIT,
            []
        );
        $history = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $start = strtotime((string)$row['started_at']);
            $end = $row['ended_at'] ? strtotime((string)$row['ended_at']) : time();
            $minutes = max(1, (int)round(($end - $start) / 60));
            $row['provider_label'] = self::getProviderLabel((string)$row['provider']);
            $row['backup_label'] = self::getProviderLabel((string)$row['backup_provider']);
            $row['started_display'] = self::formatTime($start);
            $row['ended_display'] = $row['ended_at'] ? self::formatTime($end) : '';
            $row['duration_display'] = sprintf(vtranslate('LBL_AI_OUTAGE_MINUTES', 'Settings:MailManager'), $minutes);
            $row['reason_label'] = vtranslate('LBL_AI_OUTAGE_REASON_' . strtoupper((string)$row['reason']), 'Settings:MailManager');
            $history[] = $row;
        }

        return $history;
    }

    /**
     * @param string $provider PROVIDER_*
     *
     * @return string its name ("Claude"), '' for none
     */
    public static function getProviderLabel(string $provider): string
    {
        return '' !== $provider ? vtranslate('LBL_PROVIDER_NAME_' . strtoupper($provider), 'Settings:MailManager') : '';
    }

    /**
     * @param int        $time unix time
     * @param Users|null $user whose zone and format (null = the current user)
     *
     * @return string the time in the user's zone and format
     */
    public static function formatTime(int $time, $user = null): string
    {
        // the stored times are the server's (UTC) — the helper shows them in the user's zone
        return Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $time), $user);
    }

    /**
     * @param string $provider
     *
     * @return array the breaker state, read from the database (never the request cache)
     */
    private static function readState(string $provider): array
    {
        $state = json_decode((string)(new MailManager_ProConfig_Model())->getValue(self::KEY_STATE . $provider), true);

        return is_array($state) ? $state : [];
    }

    /**
     * @param string $provider
     * @param array  $state
     *
     * @return void
     */
    private static function writeState(string $provider, array $state): void
    {
        MailManager_ProAiConfig_Model::set(self::KEY_STATE . $provider, $state ? (string)json_encode($state) : '');
    }

    /**
     * Runs a state change under the named lock — cron and users fail at the same time
     * and must open (and announce) one outage, not two.
     *
     * @param callable $change
     *
     * @return mixed what $change returns, null when the lock was not got
     */
    private static function withLock(callable $change)
    {
        $adb = PearDatabase::getInstance();
        $lock = $adb->pquery('SELECT GET_LOCK(?, 5) AS got', [self::LOCK]);

        if (!(int)$adb->query_result($lock, 0, 'got')) {
            return null;
        }

        try {
            return $change();
        } finally {
            $adb->pquery('SELECT RELEASE_LOCK(?)', [self::LOCK]);
        }
    }
}
