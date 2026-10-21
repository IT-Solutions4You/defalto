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
 * Anthropic (Claude) driver, Messages API.
 */
class MailManager_ProClaudeProvider_Helper extends MailManager_ProAiProvider_Helper
{
    private const API_BASE = 'https://api.anthropic.com/v1';
    private const API_VERSION = '2023-06-01';

    /** Default model for drafting, assistant and translation */
    public const DEFAULT_MODEL = 'claude-sonnet-5-5';

    /** Cost-effective model recommended for the triage of every mail */
    public const DEFAULT_FAST_MODEL = 'claude-haiku-5-5';

    /**
     * Returns the Haiku family pattern.
     *
     * @return string
     */
    protected function getRecommendedPattern(): string
    {
        return '/^claude-haiku-/';
    }

    /**
     * @param string $model
     *
     * @return bool the model takes output_config.effort (Claude API documentation, October 2026)
     */
    public static function supportsEffort(string $model): bool
    {
        return (bool)preg_match('/^claude-(fable|mythos|opus-5|sonnet-5|haiku-5|opus-4-[5-8]|sonnet-4-6)/', $model);
    }

    /**
     * Old generations left out of the full model list of the agent form.
     *
     * @return string
     */
    protected function getObsoletePattern(): string
    {
        return '/^claude-(instant|1|2|3)[.-]/';
    }

    /**
     * Returns the default Claude model.
     *
     * @return string
     */
    public function getDefaultModel(): string
    {
        return self::DEFAULT_MODEL;
    }

    /**
     * Tests the key and lists the available Claude models.
     *
     * @return array
     * @throws Exception
     */
    public function verify(): array
    {
        $response = $this->request('GET', self::API_BASE . '/models?limit=100', $this->getHeaders());

        if (200 !== $response['status']) {
            return ['success' => false, 'models' => [], 'message' => $this->getErrorMessage($response)];
        }

        $models = [];

        foreach ($response['body']['data'] ?? [] as $model) {
            $models[] = ['id' => $model['id'], 'label' => $model['display_name'] ?? $model['id']];
        }

        return ['success' => true, 'models' => $models, 'message' => ''];
    }

    /**
     * Runs one Messages API completion.
     *
     * @param string $system   system prompt ('' = none)
     * @param array  $messages conversation messages
     * @param array  $options  ['max_tokens' => int, 'timeout' => int]
     *
     * @return array
     * @throws Exception
     */
    public function complete(string $system, array $messages, array $options = []): array
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => (int)($options['max_tokens'] ?? 1024),
            'messages' => $messages,
        ];

        if ('' !== $system) {
            $payload['system'] = $system;
        }

        // effort of the agent (output_config.effort, no beta header) — only the models
        // that take it; it also steers how much the model thinks within max_tokens
        if (!empty($options['effort']) && self::supportsEffort($this->model)) {
            $payload['output_config'] = ['effort' => (string)$options['effort']];
        }

        $response = $this->request('POST', self::API_BASE . '/messages', $this->getHeaders(), $payload, $this->timeout($options));

        if (200 !== $response['status']) {
            throw $this->apiError('Claude', $response);
        }

        $body = $response['body'];
        // content is a list of typed blocks (thinking blocks may precede text)
        $text = '';

        foreach ($body['content'] ?? [] as $block) {
            if ('text' === ($block['type'] ?? '')) {
                $text .= $block['text'];
            }
        }

        // empty text: refusal, or max_tokens spent on thinking - buildResult() throws
        $stopReason = (string)($body['stop_reason'] ?? '');

        return $this->buildResult(
            $text,
            (int)($body['usage']['input_tokens'] ?? 0),
            (int)($body['usage']['output_tokens'] ?? 0),
            'max_tokens' === $stopReason,
            $stopReason
        );
    }

    /**
     * Returns the authentication headers.
     *
     * @return array
     */
    private function getHeaders(): array
    {
        return [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: ' . self::API_VERSION,
        ];
    }
}
