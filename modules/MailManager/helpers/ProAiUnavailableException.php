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
 * The AI provider is not reachable or not working right now (I17): a timeout or a
 * network error, HTTP 5xx or 529 (overloaded). Nothing is wrong with what was asked —
 * the same request may work a moment later or at the backup provider.
 */
class MailManager_ProAiUnavailableException_Helper extends Exception
{
    private int $retryAfter;

    /**
     * @param string $message    the provider's words (never the key)
     * @param int    $status     HTTP status, 0 for a network error or a timeout
     * @param int    $retryAfter seconds the provider asked to wait (Retry-After), 0 = not said
     */
    public function __construct(string $message, int $status = 0, int $retryAfter = 0)
    {
        parent::__construct($message, $status);
        $this->retryAfter = $retryAfter;
    }

    /**
     * @return int seconds the provider asked to wait, 0 = not said
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
