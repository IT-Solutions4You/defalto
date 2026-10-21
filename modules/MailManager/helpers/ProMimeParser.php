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
 * Self-contained RFC822/MIME parser for the sync engine — no ext-mailparse.
 * Extracts headers, text/html bodies (decoded to UTF-8) and attachment
 * METADATA only (content of attachment parts is discarded — lazy attachments).
 */
class MailManager_ProMimeParser_Helper
{
    /** Deepest multipart nesting followed */
    private const MAX_DEPTH = 12;

    /** Parts of one message looked at */
    private const MAX_PARTS = 500;

    /** @var int parts walked in the message being parsed */
    private static $partCount = 0;

    /**
     * @param string $raw full or partially fetched message source
     * @return array unified message array:
     *  headers (lowercased name => decoded value), from_email, from_name,
     *  to_addrs, cc_addrs, bcc_addrs (arrays of ['email','name']),
     *  subject, sent_at (UTC Y-m-d H:i:s), header_message_id, header_in_reply_to,
     *  header_references (string), body_plain, body_html, attachments[]
     */
    public static function parse($raw)
    {
        self::$partCount = 0;
        [$headerBlock, $body] = self::splitHeaderBody($raw);
        $headers = self::parseHeaders($headerBlock);
        // Address lists are split BEFORE the encoded words are decoded — a
        // decoded display name may contain the comma ("Novák, Ján")
        $rawHeaders = self::parseHeaders($headerBlock, false);

        $result = [
            'headers' => $headers,
            'subject' => $headers['subject'] ?? '',
            'header_message_id' => self::firstMessageId($headers['message-id'] ?? ''),
            'header_in_reply_to' => self::firstMessageId($headers['in-reply-to'] ?? ''),
            'header_references' => trim($headers['references'] ?? ''),
            'sent_at' => self::parseDate($headers['date'] ?? '', self::getReceivedDate($headerBlock)),
            'body_plain' => '',
            'body_html' => '',
            'attachments' => [],
        ];

        $from = self::parseAddresses($rawHeaders['from'] ?? '');
        $result['from_email'] = $from[0]['email'] ?? '';
        $result['from_name'] = $from[0]['name'] ?? '';
        $result['to_addrs'] = self::parseAddresses($rawHeaders['to'] ?? '');
        $result['cc_addrs'] = self::parseAddresses($rawHeaders['cc'] ?? '');
        $result['bcc_addrs'] = self::parseAddresses($rawHeaders['bcc'] ?? '');
        // where an answer belongs when the sender says so (a web form mails
        // from the site's own address and names the visitor here)
        $replyTo = self::parseAddresses($rawHeaders['reply-to'] ?? '');
        $result['reply_to'] = strtolower((string)($replyTo[0]['email'] ?? ''));

        self::walkPart($headers, $body, '', $result);

        // Inline = a part the HTML body really shows (cid: reference). A
        // Content-ID alone says nothing — Gmail puts one on every ordinary
        // attachment, and such a file must not be hidden as an inline image
        $html = strtolower($result['body_html']);
        foreach ($result['attachments'] as $index => $attachment) {
            $contentId = strtolower((string)$attachment['content_id']);
            $result['attachments'][$index]['is_inline'] = $contentId !== ''
                && strpos($html, 'cid:' . $contentId) !== false ? 1 : 0;
        }

        return $result;
    }

    /**
     * Recursively walks a MIME part, filling bodies and attachment metadata
     *
     * @param array $headers parsed part headers
     * @param string $body raw (still encoded) part body
     * @param string $partPath IMAP-style part index path ('' = message root,
     *                         children of the root multipart are "1".."n", nested "1.2"…)
     * @param array $result accumulator
     * @return void
     */
    private static function walkPart(array $headers, $body, $partPath, array &$result)
    {
        // Real mail nests a handful of levels and carries tens of parts. A
        // mail built to nest thousands deep holds a copy of the rest of the
        // message per level and takes the whole sync down with a memory
        // fatal nothing can catch — what lies beyond the caps is ignored
        if (substr_count($partPath, '.') >= self::MAX_DEPTH || ++self::$partCount > self::MAX_PARTS) {
            return;
        }

        $contentType = strtolower(trim(strstr(($headers['content-type'] ?? 'text/plain') . ';', ';', true)));
        $disposition = strtolower(trim(strstr(($headers['content-disposition'] ?? '') . ';', ';', true)));
        $filename = self::headerParam($headers['content-disposition'] ?? '', 'filename')
            ?: self::headerParam($headers['content-type'] ?? '', 'name');

        if (strpos($contentType, 'multipart/') === 0) {
            $boundary = self::headerParam($headers['content-type'] ?? '', 'boundary');
            if ($boundary === '') {
                return;
            }
            $index = 0;
            foreach (self::splitMultipart($body, $boundary) as $subRaw) {
                $index++;
                [$subHeaderBlock, $subBody] = self::splitHeaderBody($subRaw);
                $subHeaders = self::parseHeaders($subHeaderBlock);
                $childPath = $partPath === '' ? (string)$index : $partPath . '.' . $index;
                self::walkPart($subHeaders, $subBody, $childPath, $result);
            }

            return;
        }

        if ($partPath === '') {
            $partPath = '1';
        }

        if ($contentType === 'message/rfc822' && $disposition !== 'attachment') {
            // Embedded message — treat as attachment metadata
            $filename = $filename ?: 'message.eml';
        }

        $isAttachment = $disposition === 'attachment' || ($filename !== '' && $contentType !== 'text/plain' && $contentType !== 'text/html');
        $contentId = trim($headers['content-id'] ?? '', " \t<>");

        if (!$isAttachment && $contentType === 'text/plain' && $result['body_plain'] === '') {
            $result['body_plain'] = self::decodeBody($body, $headers);

            return;
        }
        if (!$isAttachment && $contentType === 'text/html' && $result['body_html'] === '') {
            $result['body_html'] = self::decodeBody($body, $headers);

            return;
        }

        // Attachment / inline image — metadata only, content is discarded
        $encoding = strtolower(trim($headers['content-transfer-encoding'] ?? ''));
        $sizeEstimate = $encoding === 'base64' ? (int)(strlen($body) * 0.74) : strlen($body);
        $result['attachments'][] = [
            'remote_part' => $partPath,
            'filename' => $filename !== '' ? $filename : ($contentType . ' part'),
            'mime_type' => $contentType,
            'size_bytes' => $sizeEstimate,
            // decided once the whole message is read (see parse())
            'is_inline' => 0,
            'content_id' => $contentId,
        ];
    }

    /**
     * @param string $raw
     * @return array [headerBlock, body]
     */
    private static function splitHeaderBody($raw)
    {
        $pos = strpos($raw, "\r\n\r\n");
        if ($pos === false) {
            $pos = strpos($raw, "\n\n");

            return $pos === false ? [$raw, ''] : [substr($raw, 0, $pos), substr($raw, $pos + 2)];
        }

        return [substr($raw, 0, $pos), substr($raw, $pos + 4)];
    }

    /**
     * @param string $headerBlock
     * @param bool $decode decode encoded words (false keeps them for address splitting)
     * @return array lowercased header name => value (last occurrence wins,
     *               except received which is ignored)
     */
    private static function parseHeaders($headerBlock, $decode = true)
    {
        // Unfold continuation lines
        $headerBlock = preg_replace('/\r?\n[ \t]+/', ' ', $headerBlock);

        $headers = [];
        foreach (preg_split('/\r?\n/', $headerBlock) as $line) {
            $pos = strpos($line, ':');
            if ($pos === false) {
                continue;
            }
            $name = strtolower(trim(substr($line, 0, $pos)));
            if ($name === 'received') {
                continue;
            }
            $value = trim(substr($line, $pos + 1));
            // an encoded word may decode to CR / LF / NUL — a header value is one line
            $headers[$name] = $decode
                ? preg_replace('/[\x00-\x08\x0A-\x1F\x7F]+/', ' ', self::toUtf8(self::decodeMimeWords($value)))
                : $value;
        }

        return $headers;
    }

    /**
     * Valid UTF-8 for the database and json_encode — raw 8-bit header bytes
     * (senders skipping the encoded-word syntax) otherwise empty the whole
     * recipient list or fail the INSERT in strict SQL mode
     *
     * @param string $value
     * @return string
     */
    private static function toUtf8($value)
    {
        return mb_check_encoding($value, 'UTF-8') ? $value : mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /**
     * Decodes =?charset?B/Q?...?= encoded words to UTF-8
     *
     * @param string $value
     * @return string
     */
    private static function decodeMimeWords($value)
    {
        if (strpos($value, '=?') === false) {
            return $value;
        }
        $decoded = @iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return $decoded !== false ? $decoded : $value;
    }

    /**
     * @param string $header full header value
     * @param string $param parameter name (boundary, filename, name, charset)
     * @return string
     */
    private static function headerParam($header, $param)
    {
        // the parameter name must stand alone — "name" is not "filename"
        $name = '(?:^|[;\s])' . preg_quote($param, '/');

        // RFC 2231 (Thunderbird, Outlook for non-ASCII names):
        // filename*=UTF-8''%C5%A1ek.pdf and the continuations filename*0*=…; filename*1*=…
        if (preg_match_all('/' . $name . '\*(\d*)(\*?)=\s*("([^"]*)"|[^;\s]*)/i', $header, $matches, PREG_SET_ORDER)) {
            $segments = [];
            $charset = 'UTF-8';
            foreach ($matches as $m) {
                $index = $m[1] === '' ? 0 : (int)$m[1];
                $value = $m[3] !== '' && $m[3][0] === '"' ? $m[4] : $m[3];
                // "param*=" is always percent-encoded; a continuation only with its own "*"
                $encoded = $m[1] === '' || $m[2] === '*';
                if ($encoded && $index === 0 && preg_match("/^([^']*)'[^']*'(.*)$/s", $value, $cm)) {
                    $charset = $cm[1] !== '' ? $cm[1] : $charset;
                    $value = $cm[2];
                }
                $segments[$index] = $encoded ? rawurldecode($value) : $value;
            }
            ksort($segments);
            $joined = implode('', $segments);
            if (strtoupper($charset) !== 'UTF-8') {
                $converted = @iconv($charset, 'UTF-8//TRANSLIT//IGNORE', $joined);
                if ($converted !== false) {
                    $joined = $converted;
                }
            }

            return self::toUtf8($joined);
        }
        if (preg_match('/' . $name . '=\s*"([^"]*)"/i', $header, $m)) {
            return self::decodeMimeWords($m[1]);
        }
        if (preg_match('/' . $name . '=\s*([^;\s]+)/i', $header, $m)) {
            return self::decodeMimeWords(trim($m[1], "'\""));
        }

        return '';
    }

    /**
     * @param string $body
     * @param string $boundary
     * @return string[] raw sub-parts
     */
    private static function splitMultipart($body, $boundary)
    {
        $parts = explode('--' . $boundary, $body);
        // Drop the preamble and the epilogue (after --boundary--)
        array_shift($parts);
        $result = [];
        foreach ($parts as $part) {
            if (strpos($part, '--') === 0) {
                break;
            }
            $result[] = ltrim($part, "\r\n");
        }

        return $result;
    }

    /**
     * Decodes the transfer encoding and converts the charset to UTF-8
     *
     * @param string $body
     * @param array $headers part headers
     * @return string
     */
    private static function decodeBody($body, array $headers)
    {
        $encoding = strtolower(trim($headers['content-transfer-encoding'] ?? ''));
        if ($encoding === 'base64') {
            $body = base64_decode($body, false) ?: '';
        } elseif ($encoding === 'quoted-printable') {
            $body = quoted_printable_decode($body);
        }

        $charset = strtoupper(self::headerParam($headers['content-type'] ?? '', 'charset') ?: 'UTF-8');
        if ($charset !== 'UTF-8' && $charset !== 'US-ASCII') {
            $converted = @iconv($charset, 'UTF-8//TRANSLIT//IGNORE', $body);
            if ($converted !== false) {
                $body = $converted;
            }
        }

        // Guard against broken declarations — the DB expects valid UTF-8
        if (!mb_check_encoding($body, 'UTF-8')) {
            $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');
        }

        // stored bodies carry plain \n only — every consumer (prefills,
        // prompts, regexes) reasons in \n
        return self::normalizeLineEndings($body);
    }

    /**
     * Line endings to plain \n. Broken generators double-encode a line
     * ending (=0A plus the hard CRLF) — decoded, ONE break reads "\n\r\n";
     * the lookbehind collapses exactly that while a real blank line
     * ("\r\n\r\n") stays intact
     *
     * @param string $text
     * @return string
     */
    public static function normalizeLineEndings($text)
    {
        $text = preg_replace('/(?<![\r\n])\n\r\n/', "\n", (string)$text);

        return str_replace(["\r\n", "\r"], "\n", $text);
    }

    /**
     * @param string $value address list header, encoded words NOT decoded yet
     * @return array of ['email' => string, 'name' => string]
     */
    private static function parseAddresses($value)
    {
        $addresses = [];
        if (trim($value) === '') {
            return $addresses;
        }

        // Split on commas outside quotes
        foreach (preg_split('/,(?=(?:[^"]*"[^"]*")*[^"]*$)/', $value) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            if (preg_match('/^"?(.*?)"?\s*<([^<>@\s]+@[^<>\s]+)>$/', $chunk, $m)) {
                $addresses[] = [
                    'email' => strtolower($m[2]),
                    'name' => trim(self::toUtf8(self::decodeMimeWords(trim($m[1])))),
                ];
            } elseif (preg_match('/([^\s<>@]+@[^\s<>]+)/', $chunk, $m)) {
                $addresses[] = ['email' => strtolower(trim($m[1], '<>')), 'name' => ''];
            }
        }

        return $addresses;
    }

    /**
     * Returns the CRM link key of a mail (its4you_emails.mail_message_key): md5 of the
     * Message-ID without angle brackets, the same value Basic MailManager and the mail
     * scanner compute from the Webklex message_id attribute.
     *
     * @param string $messageId Message-ID header value, with or without brackets
     *
     * @return string empty when the mail has no Message-ID
     */
    public static function getMessageKey(string $messageId): string
    {
        $messageId = trim($messageId, " \t\r\n<>");

        return '' === $messageId ? '' : md5($messageId);
    }

    /**
     * @param string $value Message-ID style header
     * @return string first <id> found, with brackets
     */
    private static function firstMessageId($value)
    {
        return preg_match('/<[^<>]+>/', $value, $m) ? $m[0] : trim($value);
    }

    /**
     * Converts the Date header to UTC. Comments such as "(Central European Summer Time)" are
     * removed first because strtotime() rejects them; an unreadable header falls back to the
     * date the receiving server added (Received) and only then to the current time.
     *
     * @param string $value        Date header
     * @param string $receivedDate date part of the topmost Received header ('' when missing)
     *
     * @return string UTC datetime Y-m-d H:i:s
     */
    private static function parseDate($value, $receivedDate = '')
    {
        foreach ([$value, $receivedDate] as $candidate) {
            $timestamp = self::getTimestamp((string)$candidate);

            if (null !== $timestamp) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }

        return gmdate('Y-m-d H:i:s');
    }

    /**
     * @param string $value RFC 2822 date, possibly with comments
     *
     * @return int|null Unix time, null when the value is not a date
     */
    private static function getTimestamp($value)
    {
        $value = trim(preg_replace('/\([^()]*\)/', ' ', $value));
        $value = preg_replace('/\s+/', ' ', $value);
        // the weekday is redundant; a wrong one would move strtotime() to another week
        $value = preg_replace('/^[a-z]+,\s*/i', '', $value);

        if ('' === $value) {
            return null;
        }

        $timestamp = strtotime($value);

        return false === $timestamp ? null : $timestamp;
    }

    /**
     * Returns the date part (after the last semicolon) of the topmost Received header.
     *
     * @param string $headerBlock raw header block
     *
     * @return string
     */
    private static function getReceivedDate($headerBlock)
    {
        if (!preg_match('/^Received:(.*(?:\r?\n[ \t].*)*)/mi', (string)$headerBlock, $match)) {
            return '';
        }

        $received = preg_replace('/\r?\n[ \t]+/', ' ', $match[1]);
        $position = strrpos($received, ';');

        return false === $position ? '' : trim(substr($received, $position + 1));
    }
}
