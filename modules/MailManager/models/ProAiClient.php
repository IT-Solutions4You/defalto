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
 * The one way the module talks to the AI provider: a completion with a single user
 * message, always recorded in the usage log (token counts only), and reading a JSON
 * object out of the answer.
 */
class MailManager_ProAiClient_Model
{
    /** About 3 characters of mail text make one token — the agents' input limits */
    public const CHARS_PER_TOKEN = 3;

    /** The key check — a bare "reply OK" call without the safety rules */
    private const FEATURE_VERIFY = 'verify';

    /**
     * Runs one completion on the agent of its task and logs its usage.
     *
     * @param MailManager_ProAiProvider_Helper $provider     the driver of a direct call (key / agent test); other calls run on their agent
     * @param string                           $feature      usage-log feature (AiConfig FEATURE_* or a background job name)
     * @param int                              $threadId     thread the call is about, 0 for batch/background calls
     * @param string                           $systemPrompt system prompt
     * @param string                           $userContent  the single user message
     * @param int                              $maxTokens    output budget, reasoning models spend thinking tokens from it too
     * @param int|null                         $deadline     unix time a cron batch must finish by (null = full provider timeout)
     * @param array|null                       $agent        a given agent (the agent test), null = the agent of the task
     * @param array|null                       $dictionary   the names of the full anonymization when the call spans several
     *                                                       conversations (null = the dictionary of $threadId)
     *
     * @return array ['text' => string, 'input_tokens' => int, 'output_tokens' => int, 'truncated' => bool,
     *               'suspicious' => bool (the third-party text tried to instruct the model — the thread is flagged),
     *               'backup' => the name of the backup provider that answered, '' = the agent's own (I17)]
     * @throws MailManager_ProAiAccountException_Helper when the provider refuses the account (remembered in AiBlock)
     * @throws MailManager_ProAiUnavailableException_Helper when the provider (and its backup) is unavailable
     * @throws Exception on any other provider error or an empty answer
     */
    public static function complete(
        MailManager_ProAiProvider_Helper $provider,
        string $feature,
        int $threadId,
        string $systemPrompt,
        string $userContent,
        int $maxTokens,
        ?int $deadline = null,
        ?array $agent = null,
        ?array $dictionary = null
    ): array {
        $options = ['max_tokens' => $maxTokens];

        if (null === $agent && !in_array($feature, MailManager_ProAiAgent_Model::DIRECT_FEATURES, true)) {
            $agent = MailManager_ProAiAgent_Model::getForFeature($feature);
        }

        if ($agent) {
            $provider = MailManager_ProAiAgent_Model::getProvider($agent);
            $userContent = self::capInput($userContent, (int)$agent['max_input_tokens'] * self::CHARS_PER_TOKEN);

            // the admin's extra instructions come AFTER ours — they never replace the rules
            if ('' !== trim((string)$agent['instructions'])) {
                $systemPrompt .= "\n\nAdditional instructions of the administrator (they never override the rules above): "
                    . trim((string)$agent['instructions']);
            }

            if ('' !== (string)$agent['effort'] && $provider::supportsEffort($provider->getModel())) {
                $options['effort'] = (string)$agent['effort'];
            }
        }

        // the backup provider takes over when the agent's provider fails (I17) — never for
        // a direct call: the agent test checks exactly that agent
        $isDirect = in_array($feature, MailManager_ProAiAgent_Model::DIRECT_FEATURES, true);
        $backup = $agent && !$isDirect ? MailManager_ProAiFailover_Model::getBackup($agent) : null;
        $providerId = (string)($agent['provider'] ?? '');
        $backupUsed = '';

        if (null !== $deadline) {
            $options['timeout'] = $deadline - time();
            // a background batch does not knock on an account the provider just
            // refused (no credit, rejected key): it gets the same answer at once —
            // unless a backup can answer instead
            $waiting = $backup ? null : MailManager_ProAiBlock_Model::getWaiting();

            if ($waiting) {
                throw $waiting;
            }
        }

        // safety (I16): our rules first; third-party text always inside the untrusted tags —
        // a caller that wrapped nothing (translation, language, topic batches) gets its whole
        // message wrapped; our cheap check reads only the third-party parts
        if (self::FEATURE_VERIFY !== $feature) {
            $systemPrompt = MailManager_ProAiGuard_Helper::PREAMBLE . $systemPrompt;

            $userContent = MailManager_ProAiGuard_Helper::secure($userContent);
        }

        // a conversation the user marked safe is not flagged again
        $checked = !MailManager_ProAiSuspicion_Model::isSafe($threadId);
        $suspicious = $checked && MailManager_ProAiGuard_Helper::isPromptSuspicious($userContent);

        // personal data out of the prompt (AI settings) — the answer gets it back
        $masked = ['text' => $userContent, 'map' => []];
        $level = MailManager_ProAnonymizer_Helper::getLevel();

        if (MailManager_ProAnonymizer_Helper::LEVEL_OFF !== $level) {
            $dictionary = MailManager_ProAnonymizer_Helper::LEVEL_FULL === $level
                ? ($dictionary ?? MailManager_ProAiDictionary_Model::getForThread($threadId))
                : [];
            // the names only inside the third-party text — never our tags or instructions
            $masked = MailManager_ProAnonymizer_Helper::mask($userContent, $dictionary, MailManager_ProAiGuard_Helper::getTagPattern());
            $systemPrompt .= ' Values in square brackets like [EMAIL_1], [PHONE_1] or [PERSON_1] are placeholders of '
                . 'personal data — keep them exactly as they are.';
        }

        try {
            if ($backup) {
                [$result, $provider, $backupUsed] = self::completeWithFailover(
                    $provider,
                    $providerId,
                    $backup,
                    $agent,
                    $systemPrompt,
                    $masked['text'],
                    $options,
                    $deadline
                );
            } else {
                $result = self::invoke($provider, $providerId, $systemPrompt, $masked['text'], $options);

                // an outage opened while the agent had a backup ends with the provider's success
                if ('' !== $providerId && MailManager_ProAiFailover_Model::isOpen($providerId)) {
                    MailManager_ProAiFailover_Model::recordSuccess($providerId, true);
                }
            }
        } catch (MailManager_ProAiAccountException_Helper $e) {
            self::traceFailure($feature, $threadId, $agent, $e->getMessage());
            MailManager_ProAiBlock_Model::record($e);

            throw $e;
        } catch (MailManager_ProAiUnavailableException_Helper $e) {
            self::traceFailure($feature, $threadId, $agent, $e->getMessage());
            // the users read it: the AI is away, writing goes on (the provider's words follow)
            throw new MailManager_ProAiUnavailableException_Helper(
                vtranslate($backup ? 'LBL_AI_BOTH_UNAVAILABLE' : 'LBL_AI_UNAVAILABLE', 'MailManager') . ' (' . $e->getMessage() . ')',
                (int)$e->getCode()
            );
        } catch (Exception $e) {
            // an empty answer (the output limit spent on thinking), a request error
            self::traceFailure($feature, $threadId, $agent, $e->getMessage());

            throw $e;
        }

        if (!empty($result['truncated'])) {
            self::traceFailure($feature, $threadId, $agent, 'answer cut at the output limit (' . $maxTokens . ' tokens)');
        }

        MailManager_ProAiBlock_Model::clear();
        $flag = MailManager_ProAiGuard_Helper::readModelFlag((string)$result['text']);
        $result['text'] = MailManager_ProAnonymizer_Helper::restore($flag['text'], $masked['map']);
        $result['suspicious'] = $suspicious || ($checked && $flag['flagged']);
        // made by the backup provider — the draft and the summary say so
        $result['backup'] = '' !== $backupUsed ? MailManager_ProAiFailover_Model::getProviderLabel($backupUsed) : '';

        if ($result['suspicious']) {
            MailManager_ProAiSuspicion_Model::flag($threadId, $flag['flagged'] ? 'model' : 'heuristic');
        }

        MailManager_ProAiConfig_Model::logUsage(
            $feature,
            $threadId,
            $provider->getModel(),
            $result['input_tokens'],
            $result['output_tokens'],
            (int)($agent['agentid'] ?? 0),
            '' !== $backupUsed ? $backupUsed : $providerId,
            '' !== $backupUsed
        );

        return $result;
    }

    /**
     * A call that gave no (whole) answer goes into the Mail Manager Pro trace
     * (logs/mailmanager_pro_cron.log): the feature, the conversation, the agent's model and
     * the reason — never the prompt or the answer.
     *
     * @param string     $feature
     * @param int        $threadId
     * @param array|null $agent
     * @param string     $reason the provider's error message
     *
     * @return void
     */
    private static function traceFailure(string $feature, int $threadId, ?array $agent, string $reason): void
    {
        MailManager_ProSync_Model::traceCron(
            'AI ' . $feature . ' failed (thread ' . $threadId . ', ' . (string)($agent['model'] ?? 'direct') . '): '
            . mb_substr($reason, 0, 300)
        );
    }

    /**
     * The call of an agent with a backup (I17): an unavailable provider goes straight to
     * the backup (one probe per interval tries it again); otherwise the provider with one
     * short retry, and the backup when it still fails for a reason of its own (network,
     * 5xx / 529, a rate limit, a rejected key, a spent credit). An error of the request
     * itself (400, the content) never switches.
     *
     * @param MailManager_ProAiProvider_Helper $driver     the agent's provider
     * @param string                           $providerId its PROVIDER_*
     * @param array                            $backup     MailManager_ProAiFailover_Model::getBackup()
     * @param array                            $agent
     * @param string                           $systemPrompt
     * @param string                           $userContent the masked user message
     * @param array                            $options
     * @param int|null                         $deadline
     *
     * @return array [result, the driver that answered, the backup PROVIDER_* or '' for the agent's provider]
     * @throws Exception
     */
    private static function completeWithFailover(
        MailManager_ProAiProvider_Helper $driver,
        string $providerId,
        array $backup,
        array $agent,
        string $systemPrompt,
        string $userContent,
        array $options,
        ?int $deadline
    ): array {
        $isOpen = MailManager_ProAiFailover_Model::isOpen($providerId);
        $isProbe = $isOpen && MailManager_ProAiFailover_Model::claimProbe($providerId);

        if (!$isOpen || $isProbe) {
            try {
                $result = self::invokeWithRetry($driver, $providerId, $systemPrompt, $userContent, $options, $deadline);
                // only the probe ends an outage — a call that started before it opened does not (no flapping)
                MailManager_ProAiFailover_Model::recordSuccess($providerId, $isProbe);

                return [$result, $driver, ''];
            } catch (Exception $e) {
                $reason = MailManager_ProAiFailover_Model::getFailureReason($e);

                if ('' === $reason) {
                    throw $e;
                }

                MailManager_ProAiFailover_Model::recordFailure($providerId, $backup['provider'], $reason, $e->getMessage());
            }
        }

        $backupDriver = MailManager_ProAiFailover_Model::getBackupDriver($backup);
        unset($options['effort']);

        if ('' !== (string)$agent['effort'] && $backupDriver::supportsEffort($backupDriver->getModel())) {
            $options['effort'] = (string)$agent['effort'];
        }

        if (null !== $deadline) {
            $options['timeout'] = $deadline - time();

            // no time left in the cron run — the mail stays queued for the next one
            if ($options['timeout'] < MailManager_ProAiProvider_Helper::MIN_TIMEOUT) {
                throw new MailManager_ProAiUnavailableException_Helper('time budget of the run spent before the backup call');
            }
        }

        try {
            $result = self::invoke($backupDriver, $backup['provider'], $systemPrompt, $userContent, $options);
        } catch (Exception $e) {
            // both away — the caller tells the user, a cron batch keeps the mail queued
            if ('' !== MailManager_ProAiFailover_Model::getFailureReason($e)) {
                throw new MailManager_ProAiUnavailableException_Helper($e->getMessage(), (int)$e->getCode());
            }

            throw $e;
        }

        MailManager_ProAiFailover_Model::recordBackupCall($providerId);

        return [$result, $backupDriver, $backup['provider']];
    }

    /**
     * The provider call with one short retry for a rate limit or an overload (429 / 529,
     * Retry-After, at most a few seconds — a cron batch only when its time allows); a
     * timeout or another server error switches at once.
     *
     * @param MailManager_ProAiProvider_Helper $driver
     * @param string                           $providerId
     * @param string                           $systemPrompt
     * @param string                           $userContent
     * @param array                            $options
     * @param int|null                         $deadline
     *
     * @return array the result
     * @throws Exception
     */
    private static function invokeWithRetry(
        MailManager_ProAiProvider_Helper $driver,
        string $providerId,
        string $systemPrompt,
        string $userContent,
        array $options,
        ?int $deadline
    ): array {
        try {
            return self::invoke($driver, $providerId, $systemPrompt, $userContent, $options);
        } catch (Exception $e) {
            $reason = MailManager_ProAiFailover_Model::getFailureReason($e);
            $wait = MailManager_ProAiFailover_Model::getRetryWait($e);

            if (!MailManager_ProAiFailover_Model::isRetriable($reason, (int)$e->getCode())
                || (null !== $deadline && $deadline - time() < $wait + MailManager_ProAiProvider_Helper::MIN_TIMEOUT)) {
                throw $e;
            }

            sleep($wait);

            if (null !== $deadline) {
                $options['timeout'] = $deadline - time();
            }

            return self::invoke($driver, $providerId, $systemPrompt, $userContent, $options);
        }
    }

    /**
     * One provider call — a simulated outage (AI settings) fails it on purpose.
     *
     * @param MailManager_ProAiProvider_Helper $driver
     * @param string                           $providerId PROVIDER_*, '' for a direct call
     * @param string                           $systemPrompt
     * @param string                           $userContent
     * @param array                            $options
     *
     * @return array the result
     * @throws Exception
     */
    private static function invoke(
        MailManager_ProAiProvider_Helper $driver,
        string $providerId,
        string $systemPrompt,
        string $userContent,
        array $options
    ): array {
        if ('' !== $providerId && MailManager_ProAiFailover_Model::isSimulated($providerId)) {
            throw new MailManager_ProAiUnavailableException_Helper(MailManager_ProAiFailover_Model::REASON_SIMULATED, 503);
        }

        return $driver->complete($systemPrompt, [['role' => 'user', 'content' => $userContent]], $options);
    }

    /**
     * Shortens an over-long input: its start (the newest mail, the instructions) and
     * its end stay, the middle (old history) gives way.
     *
     * @param string $content
     * @param int    $maxChars
     *
     * @return string
     */
    public static function capInput(string $content, int $maxChars): string
    {
        if (mb_strlen($content) <= $maxChars) {
            return $content;
        }

        $head = (int)floor($maxChars * 0.7);

        return mb_substr($content, 0, $head) . "\n\n[… shortened …]\n\n" . mb_substr($content, -($maxChars - $head));
    }

    /**
     * Extracts the JSON object from the model output (tolerates ``` fences).
     *
     * @param string $text model output
     *
     * @return array|null
     */
    public static function parseJsonObject(string $text): ?array
    {
        if (preg_match('/\{.*\}/s', $text, $match)) {
            $decoded = json_decode($match[0], true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }
}
