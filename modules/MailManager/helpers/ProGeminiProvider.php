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
 * Google (Gemini) driver, Generative Language API.
 */
class MailManager_ProGeminiProvider_Helper extends MailManager_ProAiProvider_Helper
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Gemini answers a spent quota and a passing rate limit alike: HTTP 429
     * RESOURCE_EXHAUSTED, told apart only by time, so it counts as a limit
     * (the sorting simply tries again later).
     *
     * @param array $response request() result with a non-200 status
     *
     * @return string
     */
    protected function getAccountReason(array $response): string
    {
        return 429 === (int)$response['status']
            ? MailManager_ProAiAccountException_Helper::REASON_LIMIT
            : parent::getAccountReason($response);
    }

    /**
     * Returns the Flash family pattern.
     *
     * @return string
     */
    protected function getRecommendedPattern(): string
    {
        return '/^gemini-[\\d.]+-flash$/';
    }

    /**
     * Old generations left out of the full model list of the agent form.
     *
     * @return string
     */
    protected function getObsoletePattern(): string
    {
        return '/^(gemini-1\\.|gemini-2\\.0|gemma|aqa)/';
    }

    /**
     * Returns the default Gemini model.
     *
     * @return string
     */
    public function getDefaultModel(): string
    {
        return 'gemini-2.5-flash';
    }

    /**
     * Tests the key and lists the Gemini models that support generateContent.
     *
     * @return array
     * @throws Exception
     */
    public function verify(): array
    {
        $response = $this->request('GET', self::API_BASE . '/models?pageSize=200', $this->getHeaders());

        if (200 !== $response['status']) {
            return ['success' => false, 'models' => [], 'message' => $this->getErrorMessage($response)];
        }

        $models = [];

        foreach ($response['body']['models'] ?? [] as $model) {
            $name = (string)($model['name'] ?? '');

            if (0 !== strpos($name, 'models/gemini')
                || !in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true)) {
                continue;
            }

            $models[] = [
                'id' => substr($name, strlen('models/')),
                'label' => $model['displayName'] ?? substr($name, strlen('models/')),
            ];
        }

        return ['success' => true, 'models' => $models, 'message' => ''];
    }

    /**
     * Runs one generateContent call.
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
        $contents = [];

        foreach ($messages as $message) {
            $contents[] = [
                'role' => 'assistant' === $message['role'] ? 'model' : 'user',
                'parts' => [['text' => $message['content']]],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => ['maxOutputTokens' => (int)($options['max_tokens'] ?? 1024)],
        ];

        if ('' !== $system) {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }

        $response = $this->request(
            'POST',
            self::API_BASE . '/models/' . rawurlencode($this->model) . ':generateContent',
            $this->getHeaders(),
            $payload,
            $this->timeout($options)
        );

        if (200 !== $response['status']) {
            throw $this->apiError('Gemini', $response);
        }

        $body = $response['body'];
        $text = '';

        foreach ($body['candidates'][0]['content']['parts'] ?? [] as $part) {
            $text .= $part['text'] ?? '';
        }

        // a blocked prompt carries no candidate at all, only promptFeedback
        $finishReason = (string)($body['candidates'][0]['finishReason'] ?? ($body['promptFeedback']['blockReason'] ?? ''));

        return $this->buildResult(
            $text,
            (int)($body['usageMetadata']['promptTokenCount'] ?? 0),
            (int)($body['usageMetadata']['candidatesTokenCount'] ?? 0),
            'MAX_TOKENS' === $finishReason,
            $finishReason
        );
    }

    /**
     * Returns the authentication headers (the key travels in a header, never in the URL).
     *
     * @return array
     */
    private function getHeaders(): array
    {
        return ['x-goog-api-key: ' . $this->apiKey];
    }
}
