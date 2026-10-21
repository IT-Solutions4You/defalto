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
 * A conversation whose mail tried to instruct the AI (I16): df_mailmanager_thread
 * ai_suspicious — the conversation shows "Suspicious content for AI", the automatic
 * actions stay off and drafts carry a warning. The history tells when and by which
 * feature it was noticed, never the content. A user who checked it marks it safe.
 */
class MailManager_ProAiSuspicion_Model
{
    public const STATE_NONE = 0;
    public const STATE_SUSPICIOUS = 1;
    /** a user checked the conversation and marked it safe — it is not flagged again */
    public const STATE_SAFE = 2;

    /**
     * Flags the conversation once (an entry in the history).
     *
     * @param int    $threadId
     * @param string $reason 'heuristic' (our check before the call) or 'model' (the model reported it)
     *
     * @return void
     */
    public static function flag(int $threadId, string $reason): void
    {
        if ($threadId <= 0) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'UPDATE df_mailmanager_thread SET ai_suspicious = ?, ai_suspicious_at = ? WHERE threadid = ? AND ai_suspicious = ?',
            [self::STATE_SUSPICIOUS, date('Y-m-d H:i:s'), $threadId, self::STATE_NONE]
        );

        if ($adb->getAffectedRowCount($result)) {
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_AI_SUSPICIOUS,
                MailManager_ProHistory_Model::SOURCE_AI,
                null,
                vtranslate('model' === $reason ? 'LBL_AI_SUSPICIOUS_BY_MODEL' : 'LBL_AI_SUSPICIOUS_BY_CHECK', 'MailManager')
            );
        }
    }

    /**
     * @param int $threadId
     *
     * @return bool the conversation is flagged (automatic actions stay off)
     */
    public static function isFlagged(int $threadId): bool
    {
        if ($threadId <= 0) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT ai_suspicious FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);

        return $adb->num_rows($result) && self::STATE_SUSPICIOUS === (int)$adb->query_result($result, 0, 'ai_suspicious');
    }

    /**
     * @param int $threadId
     *
     * @return bool a user marked the conversation safe — it is not checked any more
     */
    public static function isSafe(int $threadId): bool
    {
        if ($threadId <= 0) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT ai_suspicious FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);

        return $adb->num_rows($result) && self::STATE_SAFE === (int)$adb->query_result($result, 0, 'ai_suspicious');
    }

    /**
     * The user checked the conversation — the flag goes, the history keeps who did it.
     *
     * @param int $threadId
     *
     * @return void
     */
    public static function markSafe(int $threadId): void
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'UPDATE df_mailmanager_thread SET ai_suspicious = ? WHERE threadid = ? AND ai_suspicious = ?',
            [self::STATE_SAFE, $threadId, self::STATE_SUSPICIOUS]
        );

        if ($adb->getAffectedRowCount($result)) {
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_AI_SUSPICIOUS,
                MailManager_ProHistory_Model::SOURCE_USER,
                null,
                vtranslate('LBL_AI_MARKED_SAFE', 'MailManager')
            );
        }
    }
}
