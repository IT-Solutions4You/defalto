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
 * OpenAI driver, Chat Completions API.
 */
class MailManager_ProOpenAiProvider_Helper extends MailManager_ProAiProvider_Helper
{
    private const API_BASE = 'https://api.openai.com/v1';

    /**
     * Returns the "mini" family pattern.
     *
     * @return string
     */
    protected function getRecommendedPattern(): string
    {
        return '/^gpt-[\\d.]+-mini$/';
    }

    /**
     * @param string $model
     *
     * @return bool a reasoning model that takes reasoning_effort (not a chat-tuned variant)
     */
    public static function supportsEffort(string $model): bool
    {
        return (bool)preg_match('/^(o\d|gpt-[5-9])/', $model) && !str_contains($model, 'chat');
    }

    /**
     * Old generations left out of the full model list of the agent form.
     *
     * @return string
     */
    protected function getObsoletePattern(): string
    {
        return '/^(gpt-3\\.5|gpt-4$|gpt-4-|o1|chatgpt)|chat-latest$|-pro$/';
    }

    /**
     * Returns the default OpenAI model.
     *
     * @return string
     */
    public function getDefaultModel(): string
    {
        // listed by the API verification (models of the account), October 2026
        return 'gpt-5.5';
    }

    /**
     * Tests the key and lists the chat-capable OpenAI models.
     *
     * @return array
     * @throws Exception
     */
    public function verify(): array
    {
        $response = $this->request('GET', self::API_BASE . '/models', $this->getHeaders());

        if (200 !== $response['status']) {
            return ['success' => false, 'models' => [], 'message' => $this->getErrorMessage($response)];
        }

        $models = [];

        foreach ($response['body']['data'] ?? [] as $model) {
            $id = (string)($model['id'] ?? '');

            // chat-capable models only - drop embeddings, audio, image and other specialized ids
            if (!preg_match('/^(gpt-|o\d)/', $id)
                || preg_match('/(embed|audio|realtime|tts|whisper|transcribe|image|dall|moderation)/', $id)) {
                continue;
            }

            $models[] = ['id' => $id, 'label' => $id];
        }

        usort($models, static function (array $a, array $b): int {
            return strcmp($b['id'], $a['id']);
        });

        return ['success' => true, 'models' => $models, 'message' => ''];
    }

    /**
     * Runs one Chat Completions call.
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
        if ('' !== $system) {
            array_unshift($messages, ['role' => 'system', 'content' => $system]);
        }

        $payload = [
            'model' => $this->model,
            'max_completion_tokens' => (int)($options['max_tokens'] ?? 1024),
            'messages' => $messages,
        ];

        // reasoning effort of the task (low for the sorting, medium for extraction) —
        // only the reasoning models take it, a chat model would refuse the call
        if (!empty($options['effort']) && self::supportsEffort($this->model)) {
            $payload['reasoning_effort'] = (string)$options['effort'];
        }
        $response = $this->request('POST', self::API_BASE . '/chat/completions', $this->getHeaders(), $payload, $this->timeout($options));

        if (200 !== $response['status']) {
            throw $this->apiError('OpenAI', $response);
        }

        $body = $response['body'];
        $finishReason = (string)($body['choices'][0]['finish_reason'] ?? '');

        return $this->buildResult(
            (string)($body['choices'][0]['message']['content'] ?? ''),
            (int)($body['usage']['prompt_tokens'] ?? 0),
            (int)($body['usage']['completion_tokens'] ?? 0),
            'length' === $finishReason,
            $finishReason
        );
    }

    /**
     * Returns the authentication headers.
     *
     * @return array
     */
    private function getHeaders(): array
    {
        return ['Authorization: Bearer ' . $this->apiKey];
    }
}
