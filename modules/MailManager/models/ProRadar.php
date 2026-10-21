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
 * Request radar (I15): the triage names what each sender wants
 * (df_mailmanager_thread.request_type); the admin sees which requests keep coming —
 * the ground for templates, FAQ and workflows.
 */
class MailManager_ProRadar_Model
{
    /** Periods the overview offers, in days */
    public const PERIODS = [30, 90, 365];

    /** When the radar was switched on (df_mailmanager_config) — the triage asks for request_type since then */
    public const SINCE_KEY = 'ai_radar_since';

    /**
     * The radar was on before its start was recorded (I19) — the start is now.
     *
     * @return void
     */
    public static function ensureSince(): void
    {
        if ('1' === MailManager_ProAiConfig_Model::get('ai_feature_' . MailManager_ProAiConfig_Model::FEATURE_RADAR)
            && '' === MailManager_ProAiConfig_Model::get(self::SINCE_KEY)) {
            MailManager_ProAiConfig_Model::set(self::SINCE_KEY, date('Y-m-d H:i:s'));
        }
    }

    /**
     * @param mixed $value the model's request_type
     *
     * @return string|null lowercase, at most 80 characters, null for none
     */
    public static function normalize($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', strip_tags($value))));

        return '' === $value || 'null' === $value ? null : mb_substr($value, 0, 80);
    }

    /**
     * The requests that came most often in a period, with examples
     *
     * @param int $days  the period
     * @param int $limit groups at most
     *
     * @return array of ['request_type', 'conversations', 'last_at_display', 'examples' => string[]]
     */
    public static function getRecurring(int $days, int $limit = 30): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT request_type, COUNT(*) AS conversations, MAX(last_message_at) AS last_at,
                    SUBSTRING_INDEX(GROUP_CONCAT(subject ORDER BY last_message_at DESC SEPARATOR '\\n'), '\\n', 3) AS examples
             FROM df_mailmanager_thread
             WHERE request_type IS NOT NULL AND last_message_at >= UTC_TIMESTAMP() - INTERVAL ? DAY
             GROUP BY request_type
             ORDER BY conversations DESC, last_at DESC
             LIMIT " . $limit,
            [$days]
        );
        $rows = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $rows[] = [
                'request_type' => (string)$row['request_type'],
                'conversations' => (int)$row['conversations'],
                'last_at_display' => MailManager_ProMailFormat_Model::formatDateTime((string)$row['last_at']),
                'examples' => array_filter(array_map('decode_html', explode("\n", (string)$row['examples']))),
            ];
        }

        return $rows;
    }
}
