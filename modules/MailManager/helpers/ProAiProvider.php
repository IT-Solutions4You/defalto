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
 * Abstract AI provider driver. Concrete drivers (Claude/OpenAI/Gemini) talk to the
 * provider REST API with the customer's own API key (decision D9). All HTTP goes through
 * request(): fixed timeouts, the key only in headers, and the key redacted from every
 * error message. Mail bodies and keys are never logged.
 */
abstract class MailManager_ProAiProvider_Helper
{
    public const PROVIDER_CLAUDE = 'claude';
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';

    /** Longest wait for one completion */
    public const MAX_TIMEOUT = 90;

    /** Shortest timeout a nearly spent cron budget may ask for */
    public const MIN_TIMEOUT = 10;

    protected string $apiKey;
    protected string $model;

    /**
     * Creates the driver for one API key and model.
     *
     * @param string $apiKey decrypted API key
     * @param string $model  model id, empty = provider default
     */
    public function __construct(string $apiKey, string $model = '')
    {
        $this->apiKey = $apiKey;
        $this->model = '' !== $model ? $model : $this->getDefaultModel();
    }

    /**
     * Returns the fallback model when the customer has not picked one yet.
     *
     * @return string
     */
    abstract public function getDefaultModel(): string;

    /**
     * Returns the provider's cost-effective model family, fast and cheap enough for
     * the triage of every incoming mail, the model the settings recommend.
     *
     * @return string regular expression matching the model ids of the family
     */
    abstract protected function getRecommendedPattern(): string;

    /**
     * @param string $model
     *
     * @return bool the model takes an effort setting through this provider's API
     */
    public static function supportsEffort(string $model): bool
    {
        return false;
    }

    /**
     * Models the full list of the agent form leaves out — old generations of the provider.
     *
     * @return string regular expression, '' = none
     */
    protected function getObsoletePattern(): string
    {
        return '';
    }

    /**
     * The verified models worth offering: no dated snapshots, no old generations and
     * no special-purpose models (code, search, audio, realtime, previews, images…).
     *
     * @param string $provider PROVIDER_*
     * @param array  $models   [['id', 'label'], ...]
     *
     * @return array the same shape, filtered
     */
    public static function filterCurrent(string $provider, array $models): array
    {
        try {
            $obsolete = self::getInstance($provider, '')->getObsoletePattern();
        } catch (Exception $e) {
            return $models;
        }

        return array_values(array_filter($models, static function ($model) use ($obsolete) {
            $id = strtolower((string)($model['id'] ?? ''));

            return '' !== $id
                && !preg_match('/-\d{4}-\d{2}-\d{2}$|-\d{8}$/', $id)
                && !preg_match('/(codex|search|live|audio|realtime|preview|transcribe|tts|image|embed|instruct|-exp|computer-use|deep-research)/', $id)
                && ('' === $obsolete || !preg_match($obsolete, $id));
        }));
    }

    /**
     * Tests the API key with a cheap call and fetches the available models.
     *
     * @return array ['success' => bool, 'models' => [['id' => string, 'label' => string], ...], 'message' => string]
     * @throws Exception on transport error
     */
    abstract public function verify(): array;

    /**
     * Runs a single completion call.
     *
     * @param string $system   system prompt ('' = none)
     * @param array  $messages [['role' => 'user'|'assistant', 'content' => string], ...]
     * @param array  $options  ['max_tokens' => int, 'timeout' => int seconds]
     *
     * @return array ['text' => string (never empty), 'input_tokens' => int, 'output_tokens' => int, 'truncated' => bool]
     * @throws Exception on transport or API error and on an empty answer
     */
    abstract public function complete(string $system, array $messages, array $options = []): array;


    /**
     * Returns the model id this driver uses.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Returns the supported provider codes.
     *
     * @return array
     */
    public static function getProviders(): array
    {
        return [self::PROVIDER_CLAUDE, self::PROVIDER_OPENAI, self::PROVIDER_GEMINI];
    }

    /**
     * Driver factory.
     *
     * @param string $provider claude|openai|gemini
     * @param string $apiKey   decrypted API key
     * @param string $model    model id, empty = provider default
     *
     * @return MailManager_ProAiProvider_Helper
     * @throws Exception on unknown provider
     */
    public static function getInstance(string $provider, string $apiKey, string $model = ''): self
    {
        switch ($provider) {
            case self::PROVIDER_CLAUDE:
                return new MailManager_ProClaudeProvider_Helper($apiKey, $model);
            case self::PROVIDER_OPENAI:
                return new MailManager_ProOpenAiProvider_Helper($apiKey, $model);
            case self::PROVIDER_GEMINI:
                return new MailManager_ProGeminiProvider_Helper($apiKey, $model);
        }

        throw new Exception('MailManager: unknown AI provider "' . $provider . '"');
    }

    /**
     * JSON REST call via cURL. API keys travel in headers only, never in the URL;
     * the key is redacted from transport errors.
     *
     * @param string     $method  GET|POST
     * @param string     $url     endpoint URL
     * @param array      $headers ['Header: value', ...]
     * @param array|null $payload JSON body for POST
     * @param int        $timeout seconds, a cron batch passes what is left of its budget
     *
     * @return array ['status' => int, 'body' => array, 'retry_after' => seconds of a Retry-After header, 0 = none]
     * @throws MailManager_ProAiUnavailableException_Helper on a transport error (network, timeout)
     */
    protected function request(string $method, string $url, array $headers, ?array $payload = null, int $timeout = self::MAX_TIMEOUT): array
    {
        $headers[] = 'Content-Type: application/json';

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => max(self::MIN_TIMEOUT, min(self::MAX_TIMEOUT, $timeout)),
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        // how long an overloaded provider asks to wait (I17) — seconds only, a date is ignored
        $retryAfter = 0;
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, static function ($handle, $header) use (&$retryAfter) {
            if (preg_match('/^retry-after:\s*(\d+)\s*$/i', trim($header), $match)) {
                $retryAfter = (int)$match[1];
            }

            return strlen($header);
        });

        if ('POST' === $method) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if (false === $body) {
            throw new MailManager_ProAiUnavailableException_Helper('Connection failed: ' . $this->redact($error));
        }

        return ['status' => $status, 'body' => json_decode((string)$body, true) ?: [], 'retry_after' => $retryAfter];
    }

    /**
     * Removes the API key from a text that may be shown or logged.
     *
     * @param string $text any message
     *
     * @return string
     */
    protected function redact(string $text): string
    {
        if ('' === $this->apiKey) {
            return $text;
        }

        return str_replace($this->apiKey, '***', $text);
    }

    /**
     * Builds the one shape every driver returns from complete(): the same keys and
     * the same exception for an empty answer (refusal, blocked prompt, budget spent
     * on thinking), whichever provider is configured.
     *
     * @param string $text         answer text
     * @param int    $inputTokens  prompt tokens
     * @param int    $outputTokens answer tokens
     * @param bool   $truncated    the answer was cut off at max_tokens
     * @param string $stopReason   provider stop/finish reason, for the error message
     *
     * @return array ['text' => string, 'input_tokens' => int, 'output_tokens' => int, 'truncated' => bool]
     * @throws Exception when the answer is empty
     */
    protected function buildResult(string $text, int $inputTokens, int $outputTokens, bool $truncated, string $stopReason): array
    {
        if ('' === trim($text)) {
            throw new Exception(get_class($this) . ': empty answer (stop reason: ' . ('' !== $stopReason ? $stopReason : '?') . ')');
        }

        return [
            'text' => $text,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'truncated' => $truncated,
        ];
    }

    /**
     * Returns the request timeout of complete() options.
     *
     * @param array $options complete() options
     *
     * @return int seconds
     */
    protected function timeout(array $options): int
    {
        return (int)($options['timeout'] ?? self::MAX_TIMEOUT);
    }

    /**
     * Extracts a human readable error message from a provider error response.
     *
     * @param array $response result of request()
     *
     * @return string
     */
    protected function getErrorMessage(array $response): string
    {
        $body = $response['body'];
        $message = $body['error']['message'] ?? ($body['message'] ?? '');
        $message = is_string($message) ? $message : '';

        return $this->redact('HTTP ' . $response['status'] . ('' !== $message ? ': ' . $message : ''));
    }

    /**
     * Returns the exception of a refused completion. A refusal of the ACCOUNT (credit,
     * key, limit) comes back as MailManager_ProAiAccountException_Helper: the same
     * request would fail for every mail, callers stop instead of going on.
     *
     * @param string $providerName for the message, e.g. "Claude"
     * @param array  $response     request() result with a non-200 status
     *
     * @return Exception
     */
    protected function apiError(string $providerName, array $response): Exception
    {
        $message = $providerName . ' API error - ' . $this->getErrorMessage($response);
        $reason = $this->getAccountReason($response);
        $status = (int)$response['status'];
        $retryAfter = (int)($response['retry_after'] ?? 0);

        if ('' !== $reason) {
            return new MailManager_ProAiAccountException_Helper($reason, $message, $status, $retryAfter);
        }

        // the provider is down or overloaded (529) — the backup may take over (I17)
        if ($status >= 500 || 0 === $status) {
            return new MailManager_ProAiUnavailableException_Helper($message, $status, $retryAfter);
        }

        return new Exception($message, $status);
    }

    /**
     * Returns whether the refusal is about the account. The providers word it alike:
     * the wording decides first (Anthropic reports a spent credit as HTTP 400,
     * OpenAI as 429), the status after it. A driver with its own signs overrides this.
     *
     * @param array $response request() result with a non-200 status
     *
     * @return string MailManager_ProAiAccountException_Helper::REASON_* or '' (an ordinary error)
     */
    protected function getAccountReason(array $response): string
    {
        $status = (int)$response['status'];
        $text = strtolower((string)json_encode($response['body']));
        // not the bare word "billing": ordinary rate-limit texts point at the billing page too
        $creditWords = '/credit balance|insufficient_quota|payment required/';
        $keyWords = '/invalid[ _]api[ _-]?key|api key not valid|authentication_error|permission_denied/';

        if (402 === $status || preg_match($creditWords, $text)) {
            return MailManager_ProAiAccountException_Helper::REASON_CREDIT;
        }

        if (401 === $status || 403 === $status || preg_match($keyWords, $text)) {
            return MailManager_ProAiAccountException_Helper::REASON_AUTH;
        }

        if (429 === $status) {
            return MailManager_ProAiAccountException_Helper::REASON_LIMIT;
        }

        return '';
    }
}
