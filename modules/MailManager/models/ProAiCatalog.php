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
 * The models we recommend (I13): per provider and agent type the recommended model
 * first, then the alternatives, each with a short translated description and an
 * indicative price (1–3). Updated with the module version — names checked against
 * the providers' model lists and documentation (October 2026). A key that has no access
 * to the first model gets the first listed alternative.
 */
class MailManager_ProAiCatalog_Model
{
    /** Agent types */
    public const TIER_FAST = 'fast';
    public const TIER_QUALITY = 'quality';
    public const TIER_DEEP = 'deep';
    public const TIERS = [self::TIER_FAST, self::TIER_QUALITY, self::TIER_DEEP];

    /**
     * provider => tier => [[model, description label, price 1-3], ...], recommended first
     */
    private const CATALOG = [
        MailManager_ProAiProvider_Helper::PROVIDER_CLAUDE => [
            self::TIER_FAST => [
                ['claude-haiku-5-5', 'LBL_CATALOG_CLAUDE_HAIKU', 1],
            ],
            self::TIER_QUALITY => [
                ['claude-sonnet-5-5', 'LBL_CATALOG_CLAUDE_SONNET', 2],
                ['claude-sonnet-5', 'LBL_CATALOG_CLAUDE_SONNET_PREV', 2],
            ],
            self::TIER_DEEP => [
                ['claude-opus-5-5', 'LBL_CATALOG_CLAUDE_OPUS', 3],
            ],
        ],
        MailManager_ProAiProvider_Helper::PROVIDER_OPENAI => [
            self::TIER_FAST => [
                ['gpt-6-luna', 'LBL_CATALOG_GPT_LUNA', 1],
                ['gpt-5.4-mini', 'LBL_CATALOG_GPT_MINI', 1],
                ['gpt-5.4-nano', 'LBL_CATALOG_GPT_NANO', 1],
            ],
            self::TIER_QUALITY => [
                ['gpt-6-sol', 'LBL_CATALOG_GPT_SOL', 2],
                ['gpt-6.1-sol', 'LBL_CATALOG_GPT_SOL', 2],
                ['gpt-5.5', 'LBL_CATALOG_GPT_55', 2],
            ],
            self::TIER_DEEP => [
                ['gpt-6-astra', 'LBL_CATALOG_GPT_ASTRA', 3],
            ],
        ],
        MailManager_ProAiProvider_Helper::PROVIDER_GEMINI => [
            self::TIER_FAST => [
                ['gemini-2.5-flash', 'LBL_CATALOG_GEMINI_FLASH', 1],
                ['gemini-2.5-flash-lite', 'LBL_CATALOG_GEMINI_FLASH_LITE', 1],
            ],
            self::TIER_QUALITY => [
                ['gemini-2.5-pro', 'LBL_CATALOG_GEMINI_PRO', 2],
            ],
            self::TIER_DEEP => [
                ['gemini-2.5-pro', 'LBL_CATALOG_GEMINI_PRO', 2],
            ],
        ],
    ];

    /**
     * The catalog entries of an agent type the key can use, recommended first.
     *
     * @param string   $provider PROVIDER_*
     * @param string   $tier     TIER_*
     * @param string[] $verified model ids the key's verification listed ([] = not verified yet: all)
     *
     * @return array of ['id', 'description', 'price']
     */
    public static function getRecommended(string $provider, string $tier, array $verified = []): array
    {
        $entries = [];

        foreach (self::CATALOG[$provider][$tier] ?? [] as [$model, $label, $price]) {
            if (!$verified || in_array($model, $verified, true)) {
                $entries[] = [
                    'id' => $model,
                    'description' => vtranslate($label, 'Settings:MailManager'),
                    'price' => $price,
                ];
            }
        }

        return $entries;
    }

    /**
     * @param string   $provider
     * @param string   $tier
     * @param string[] $verified
     *
     * @return string the model to pre-fill, '' when the key has none of the catalog
     */
    public static function getRecommendedModel(string $provider, string $tier, array $verified = []): string
    {
        return (string)(self::getRecommended($provider, $tier, $verified)[0]['id'] ?? '');
    }

    /**
     * How a model fits an agent type — the badge of a pick outside the recommendations.
     *
     * @param string $provider
     * @param string $tier  the agent's type
     * @param string $model
     *
     * @return string '' (recommended), 'expensive' (a stronger tier) or 'not_recommended'
     */
    public static function getFit(string $provider, string $tier, string $model): string
    {
        foreach (self::CATALOG[$provider] ?? [] as $catalogTier => $entries) {
            if (!in_array($model, array_column($entries, 0), true)) {
                continue;
            }

            if ($catalogTier === $tier) {
                return '';
            }

            return array_search($catalogTier, self::TIERS, true) > array_search($tier, self::TIERS, true)
                ? 'expensive'
                : 'not_recommended';
        }

        return 'not_recommended';
    }
}
