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
 * The AI provider refused the request because of the ACCOUNT, not because of
 * what was asked: the credit is used up, the API key is rejected, a limit is
 * reached. Every following request fails the same way until somebody fixes
 * the account, so callers stop their batch instead of failing mail by mail.
 */
class MailManager_ProAiAccountException_Helper extends Exception
{
    /** The credit / quota of the account is used up and has to be topped up */
    public const REASON_CREDIT = 'credit';

    /** The API key is missing, wrong, revoked or lacks the permission */
    public const REASON_AUTH = 'auth';

    /** Too many requests right now, goes away by itself */
    public const REASON_LIMIT = 'limit';

    private string $reason;

    private int $retryAfter;

    /**
     * Creates the exception with the account reason and the provider message.
     *
     * @param string $reason     one of the REASON_* constants
     * @param string $message    the provider's own words (never contains the key)
     * @param int    $status     HTTP status (I17), 0 = not known
     * @param int    $retryAfter seconds a rate limit asks to wait (Retry-After), 0 = not said
     */
    public function __construct(string $reason, string $message, int $status = 0, int $retryAfter = 0)
    {
        parent::__construct($message, $status);
        $this->reason = $reason;
        $this->retryAfter = $retryAfter;
    }

    /**
     * @return int seconds a rate limit asks to wait, 0 = not said
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Returns why the account refused the request.
     *
     * @return string one of the REASON_* constants
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
