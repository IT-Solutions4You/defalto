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
 * Indicative model prices from the public OpenRouter catalog (USD per 1M tokens) for the
 * cost column of the AI usage. The server reads https://openrouter.ai/api/v1/models with
 * a bare GET — no key, no data of ours — at most once a day (cached in df_mailmanager_config
 * ai_prices_openrouter). Our model ids map to the catalog by exact id only (a ":batch" or
 * "-pro" variant is another price): "claude-haiku-5-5" → "anthropic/claude-haiku-5.5".
 */
class MailManager_ProAiPriceCatalog_Model
{
    public const SOURCE = 'openrouter';

    private const URL = 'https://openrouter.ai/api/v1/models';
    private const CACHE_KEY = 'ai_prices_openrouter';
    private const CACHE_SECONDS = 86400;
    private const TIMEOUT = 15;

    /** model id pattern => OpenRouter provider prefix */
    private const PREFIXES = [
        '/^claude-/' => 'anthropic/',
        '/^(gpt-|o\d)/' => 'openai/',
        '/^gemini-/' => 'google/',
    ];

    /**
     * Fills the prices of the models from the catalog: models without a price and those
     * whose price came from the catalog before; a price the admin entered only with $overwrite.
     *
     * @param string[] $models    our model ids
     * @param bool     $overwrite replace the admin's own prices too
     *
     * @return array ['filled' => [model, ...], 'missing' => [model not in the catalog, ...], 'date' => Y-m-d]
     * @throws Exception when the catalog cannot be read (nothing is changed then)
     */
    public static function apply(array $models, bool $overwrite): array
    {
        $catalog = self::getCatalog();
        $prices = MailManager_ProAiConfig_Model::getPrices();
        $filled = [];
        $missing = [];

        foreach (array_unique(array_filter(array_map('strval', $models))) as $model) {
            $price = self::getPrice($catalog['prices'], $model);

            if (null === $price) {
                $missing[] = $model;

                continue;
            }

            $own = isset($prices[$model]) && self::SOURCE !== ($prices[$model]['source'] ?? '');

            if ($own && !$overwrite) {
                continue;
            }

            $prices[$model] = $price + ['source' => self::SOURCE, 'date' => $catalog['date']];
            $filled[] = $model;
        }

        MailManager_ProAiConfig_Model::savePrices($prices);

        return ['filled' => $filled, 'missing' => $missing, 'date' => $catalog['date']];
    }

    /**
     * @param array  $catalog OpenRouter id => ['in', 'out']
     * @param string $model   our model id
     *
     * @return array|null ['in' => USD / 1M input tokens, 'out' => USD / 1M output tokens]
     */
    private static function getPrice(array $catalog, string $model): ?array
    {
        foreach (self::PREFIXES as $pattern => $prefix) {
            if (!preg_match($pattern, $model)) {
                continue;
            }

            // "claude-haiku-5-5" is "claude-haiku-5.5" there, "gpt-6-luna" stays
            foreach ([$model, (string)preg_replace('/-(\d+)-(\d+)$/', '-$1.$2', $model)] as $candidate) {
                if (isset($catalog[$prefix . $candidate])) {
                    return $catalog[$prefix . $candidate];
                }
            }
        }

        return null;
    }

    /**
     * @return array ['date' => Y-m-d of the reading, 'prices' => OpenRouter id => ['in', 'out']]
     * @throws Exception when the catalog cannot be read
     */
    private static function getCatalog(): array
    {
        $cached = json_decode(MailManager_ProAiConfig_Model::get(self::CACHE_KEY), true);

        if (is_array($cached) && time() - (int)($cached['time'] ?? 0) < self::CACHE_SECONDS && !empty($cached['prices'])) {
            return $cached;
        }

        $catalog = ['time' => time(), 'date' => date('Y-m-d'), 'prices' => self::fetch()];
        MailManager_ProAiConfig_Model::set(self::CACHE_KEY, (string)json_encode($catalog));

        return $catalog;
    }

    /**
     * The bare GET of the public catalog — no key, no data of ours.
     *
     * @return array OpenRouter id => ['in', 'out'] per 1M tokens
     * @throws Exception
     */
    private static function fetch(): array
    {
        $curl = curl_init(self::URL);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        $data = false !== $body && 200 === $status ? json_decode((string)$body, true) : null;

        if (!is_array($data) || !isset($data['data']) || !is_array($data['data'])) {
            throw new Exception(vtranslate('LBL_AI_PRICES_FETCH_FAILED', 'Settings:MailManager'));
        }

        $prices = [];

        foreach ($data['data'] as $entry) {
            $id = (string)($entry['id'] ?? '');
            $in = $entry['pricing']['prompt'] ?? null;
            $out = $entry['pricing']['completion'] ?? null;

            // a variant (":batch", ":free") is not the price of the model itself
            if ('' === $id || false !== strpos($id, ':') || !is_numeric($in) || !is_numeric($out) || $in < 0 || $out < 0) {
                continue;
            }

            $prices[$id] = ['in' => round((float)$in * 1000000, 4), 'out' => round((float)$out * 1000000, 4)];
        }

        if (!$prices) {
            throw new Exception(vtranslate('LBL_AI_PRICES_FETCH_FAILED', 'Settings:MailManager'));
        }

        return $prices;
    }
}
