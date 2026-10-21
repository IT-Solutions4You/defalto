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
 * The NEW part of a reply — the text above the quoted history the mail
 * client appends. Deterministic: quote markers of the common clients (HTML
 * and plain text), then the text of an earlier message of the thread found
 * inside the reply. newText() is pure, forMessage() reads the stored rows
 */
class MailManager_ProReplyText_Model
{
    /** HTML the common clients start the quoted history with */
    private const HTML_QUOTE_MARKERS = [
        '<blockquote',
        'class="gmail_quote',
        'id="divRplyFwdMsg"',
        'id="appendonsend"',
        'class="moz-cite-prefix"',
        'id="mail-editor-reference-message-container"',
        'class="yahoo_quoted"',
    ];

    /** Plain-text lines that start the quoted history */
    private const TEXT_QUOTE_PATTERNS = [
        '/^[ \t]*-{2,}[ \t]*(Original Message|Pôvodná správa|Původní zpráva|Ursprüngliche Nachricht|'
        . 'Forwarded message|Preposlaná správa|Přeposlaná zpráva)[ \t]*-{2,}/imu',
        '/^[ \t]*(On|Dňa|Dne|Am|Le|El|Il)[ \t].{4,200}(wrote|napísal|napísala|napsal|napsala|schrieb|'
        . 'a écrit|escribió|ha scritto)[ \t]*:/imu',
        // Outlook header block: a From line followed by a Sent/Date line
        '/^[ \t]*(From|Od|Von|De)[ \t]*:.*\n(?:.*\n){0,3}?[ \t]*(Sent|Date|Odoslané|Odesláno|Gesendet|'
        . 'Datum|Dátum|Envoyé)[ \t]*:/imu',
        '/^[ \t]*>/m',
    ];

    /** Words of an earlier message used to recognize it inside the reply */
    private const FINGERPRINT_WORDS = 8;

    /**
     * The new part of a stored message — what the sender wrote NOW; the
     * quoted history is already known from the earlier messages
     *
     * @param int $messageId df_mailmanager_message id
     * @param string $fallback text used when the row cannot be read
     * @return string
     */
    public static function forMessage($messageId, $fallback = '')
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT threadid, sent_at, body_html, body_plain FROM df_mailmanager_message WHERE messageid = ?',
            [$messageId]
        );
        if (!$adb->num_rows($result)) {
            return $fallback;
        }
        $message = $adb->raw_query_result_rowdata($result, 0);

        $previous = [];
        $earlier = $adb->pquery(
            'SELECT body_plain FROM df_mailmanager_message
             WHERE threadid = ? AND messageid != ? AND is_draft = 0 AND sent_at <= ?
             ORDER BY sent_at DESC LIMIT 3',
            [$message['threadid'], $messageId, $message['sent_at']]
        );
        for ($i = 0, $count = $adb->num_rows($earlier); $i < $count; $i++) {
            $previous[] = (string)$adb->raw_query_result_rowdata($earlier, $i)['body_plain'];
        }

        return self::newText($message['body_html'], $message['body_plain'], $previous);
    }

    /**
     * @param string $bodyHtml sanitized HTML part ('' when none)
     * @param string $bodyPlain stored plain text
     * @param array $previousTexts plain texts of earlier messages of the thread
     * @return string the new text; the whole text when no quote was found
     *                or the quote starts at the very top (bottom posting)
     */
    public static function newText($bodyHtml, $bodyPlain, array $previousTexts = [])
    {
        // what the reader sees — hidden parts never reach a translation or a prompt (I16)
        $bodyHtml = MailManager_ProAiGuard_Helper::stripHidden((string)$bodyHtml)['html'];
        $full = MailManager_ProMailText_Model::messageText($bodyPlain, $bodyHtml);
        $text = $full;

        $html = self::cutHtml($bodyHtml);
        if ($html !== $bodyHtml) {
            $text = MailManager_ProMailText_Model::tightenDoubleSpacing(
                MailManager_ProMailText_Model::htmlToText($html)
            );
        }

        $cut = mb_strlen($text);
        foreach (self::TEXT_QUOTE_PATTERNS as $pattern) {
            if (preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE)) {
                $cut = min($cut, mb_strlen(substr($text, 0, $match[0][1])));
            }
        }
        foreach ($previousTexts as $previous) {
            $offset = self::findEarlierMessage($text, (string)$previous);
            if ($offset !== null) {
                $cut = min($cut, $offset);
            }
        }

        $new = trim(mb_substr($text, 0, $cut));

        return $new !== '' ? $new : $full;
    }

    /**
     * @param string $html
     * @return string the HTML up to the first quote marker
     */
    private static function cutHtml($html)
    {
        $cut = strlen($html);
        foreach (self::HTML_QUOTE_MARKERS as $marker) {
            $position = stripos($html, $marker);
            if ($position === false) {
                continue;
            }
            // an attribute marker cuts at the start of its tag
            if ($marker[0] !== '<') {
                $tagStart = strrpos(substr($html, 0, $position), '<');
                $position = $tagStart !== false ? $tagStart : $position;
            }
            $cut = min($cut, $position);
        }

        return substr($html, 0, $cut);
    }

    /**
     * Where an earlier message starts inside the reply — its first words
     * matched with any whitespace in between (clients re-wrap the quote)
     *
     * @param string $text reply text
     * @param string $previous earlier message text
     * @return int|null character offset
     */
    private static function findEarlierMessage($text, $previous)
    {
        $words = preg_split('/\s+/u', trim($previous), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) < self::FINGERPRINT_WORDS) {
            return null;
        }
        $pattern = '/' . implode('\s+', array_map(function ($word) {
            return preg_quote($word, '/');
        }, array_slice($words, 0, self::FINGERPRINT_WORDS))) . '/u';

        if (!preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        return mb_strlen(substr($text, 0, $match[0][1]));
    }
}
