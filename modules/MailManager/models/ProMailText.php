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
 * Mail body text handling — HTML to readable text, the reliable text of a
 * message, line-break normalization, HTML sanitizing and the frame wrapper.
 * Pure functions without database access; shared by the sync, the inbox
 * views, the AI layer and the CRM logging
 */
class MailManager_ProMailText_Model
{
    /**
     * Readable plain text from an HTML mail body: structural tags become
     * line breaks, list items bullets; entities decoded, whitespace collapsed
     *
     * @param string $html
     * @return string
     */
    public static function htmlToText($html)
    {
        // CRLF of the HTML source would survive into the text — every stored
        // body must carry plain \n line endings
        $html = MailManager_ProMimeParser_Helper::normalizeLineEndings((string)$html);
        $html = preg_replace('#<(script|style|head|title)\b[^>]*>.*?</\1>#is', ' ', (string)$html);
        // A <br> right before a block close is presentation noise — the block
        // close breaks the line itself (mail generators wrap every line in
        // <div>text<br></div>, doubling each break). A deliberate empty line
        // (<div><br></div>) still survives: its own block close remains
        $html = preg_replace('#<br\s*/?\s*>\s*(?=</(p|div|tr|li|h[1-6]|blockquote)\b)#i', '', $html);
        $html = preg_replace('#<br\s*/?\s*>#i', "\n", $html);
        $html = preg_replace('#<li\b[^>]*>#i', "\n- ", $html);
        $html = preg_replace('#</(p|div|tr|table|ul|ol|li|h[1-6]|blockquote)\s*>#i', "\n", $html);

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);

        // Bare CSS rules living directly in text parts (portal / newsletter
        // generators): "selector, selector { ... }" — removed with their
        // selectors; repeated for nested blocks (@media). The pattern is
        // deliberately unambiguous (no nested quantifiers) — an ambiguous one
        // hits the PCRE backtrack limit on long mails and silently returns null
        for ($i = 0; $i < 5 && strpos($text, '{') !== false; $i++) {
            // selector lines chain ONLY through a trailing comma — prose lines
            // above a CSS block are never consumed
            $stripped = preg_replace(
                '/[^\s{},][^{}\n]*+(?:,\s*\n[^\s{}][^{}\n]*+)*+\s*+\{[^{}]*+\}/u',
                ' ',
                $text
            );
            if ($stripped === null || $stripped === $text) {
                break;
            }
            $text = $stripped;
        }
        $text = (string)preg_replace('/@(media|import|font-face)[^\n{]*/i', ' ', $text);

        $text = preg_replace("/[ \t]+/u", ' ', $text);
        $text = preg_replace("/[ \t]*\n[ \t]*/u", "\n", $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }

    /**
     * The reliable text of a message: generator "plain" parts often carry raw
     * HTML/CSS — when the message has an HTML part, that one converts cleanly
     * and wins; the plain part is only a fallback
     *
     * @param string $bodyPlain
     * @param string $bodyHtml
     * @return string
     */
    public static function messageText($bodyPlain, $bodyHtml)
    {
        $plain = trim((string)$bodyPlain);
        if ((string)$bodyHtml !== ''
            && ($plain === ''
                || self::looksLikeMarkup($plain)
                // unfilled template stubs ("TEXT_FORMAT_BODY") — a whole plain
                // part without a single whitespace is never a real mail body
                || preg_match('/\s/u', $plain) !== 1)) {
            return self::tightenDoubleSpacing(self::htmlToText($bodyHtml));
        }

        // a plain part transliterated to ASCII (ITS4YouEmails sends "Dobry den" next to the
        // HTML "Dobrý deň") — the HTML keeps the real text
        if ((string)$bodyHtml !== '' && preg_match('/[^\x00-\x7F]/', $plain) !== 1) {
            $htmlText = self::htmlToText($bodyHtml);

            if (preg_match('/[^\x00-\x7F]/u', $htmlText) === 1) {
                return self::tightenDoubleSpacing($htmlText);
            }
        }

        return self::tightenDoubleSpacing(self::cleanBodyText($plain));
    }

    /**
     * Text for a vtiger core column (utf8mb3): 4-byte characters (emoji) would
     * make MySQL cut the value silently at the first one. HTML keeps them as
     * numeric entities (the browser renders them), plain text drops them
     *
     * @param string $text
     * @param bool $isHtml
     * @return string
     */
    public static function toCoreCharset($text, $isHtml = false)
    {
        return (string)preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function ($match) use ($isHtml) {
            return $isHtml ? '&#' . mb_ord($match[0], 'UTF-8') . ';' : '';
        }, (string)$text);
    }

    /**
     * Halves the line breaks of a uniformly double-spaced text (some plain
     * parts and generators double EVERY break — the mail then reads twice as
     * tall as its HTML rendering). Mixed spacing (real paragraphs between
     * single-broken lines) is left untouched; the >= 3 guard protects short
     * genuine two-paragraph mails
     *
     * @param string $text
     * @return string
     */
    public static function tightenDoubleSpacing($text)
    {
        // Bodies cached before the parser normalized line endings still
        // carry CRLF and the "\n\r\n" double-encoded artifact
        $text = MailManager_ProMimeParser_Helper::normalizeLineEndings($text);

        if (!preg_match('/(?<!\n)\n(?!\n)/u', $text) && substr_count($text, "\n\n") >= 3) {
            $text = str_replace("\n\n", "\n", $text);
        }

        // an artifact blank line leaves a triple break behind
        return preg_replace("/\n{3,}/", "\n\n", $text);
    }

    /**
     * Guard for "plain" mail parts that in reality carry HTML/CSS (portal and
     * newsletter generators) — converted to text, real plain text untouched.
     * Prefer messageText() whenever the HTML part is at hand
     *
     * @param string $text
     * @return string
     */
    public static function cleanBodyText($text)
    {
        $text = (string)$text;

        // real plain text may still carry literal entities ("&iacute;") from
        // the broken-save era — decode, AI prompts copy them verbatim otherwise
        return self::looksLikeMarkup($text)
            ? self::htmlToText($text)
            : trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Elements a mail has no business carrying — removed with their content */
    private const ACTIVE_ELEMENTS = 'script|iframe|frame|frameset|object|embed|applet|form|svg|math|noscript|template';

    /** Tags removed on their own: the same ones left open or stray, and the void ones */
    private const ACTIVE_TAGS = self::ACTIVE_ELEMENTS . '|link|meta|base';

    /** Passes over a mail before the sanitizer gives up and drops its HTML */
    private const SANITIZE_PASSES = 20;

    /**
     * Server-side HTML sanitization of a mail body for the cache. The inbox
     * shows bodies in a sandboxed frame that runs no scripts; this is the
     * second layer. It is NOT enough for HTML leaving the module — whatever
     * goes into core records passes purifyHtml() as well
     *
     * @param string $html
     * @return string
     */
    public static function sanitizeHtml($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }

        // <style> stays — mails (Outlook p.MsoNormal, newsletters) rely on it
        // for their spacing/layout; the sandboxed frame runs no scripts anyway.
        // Removing a tag can assemble a new one out of what stood around it
        // ("<scr<link>ipt>") — repeat until a pass changes nothing
        for ($pass = 0; $pass < self::SANITIZE_PASSES; $pass++) {
            $clean = preg_replace('#<(' . self::ACTIVE_ELEMENTS . ')\b[^>]*>.*?</\1\s*>#is', '', $html);
            $clean = preg_replace('#</?(' . self::ACTIVE_TAGS . ')\b[^>]*>?#i', '', (string)$clean);
            // event handlers — also glued to the previous attribute (src="x"onerror=…)
            $clean = preg_replace(
                '/(?<=[\s"\'\/])on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i',
                '',
                (string)$clean
            );
            $clean = preg_replace_callback(
                '/\b(href|src|action|formaction|background|poster|xlink:href)(\s*=\s*)("[^"]*"|\'[^\']*\'|[^\s>]+)/i',
                function ($match) {
                    return self::isScriptUrl(trim($match[3], '"\'')) ? $match[1] . $match[2] . '"#"' : $match[0];
                },
                (string)$clean
            );
            if ($clean === null) {
                // the regex engine gave up on a pathological mail — no HTML at all
                return '';
            }
            if ($clean === $html) {
                return $html;
            }
            $html = $clean;
        }

        // still changing after all passes: built to fool the sanitizer
        return '';
    }

    /**
     * @param string $url attribute value as written in the mail
     * @return bool it would run script (the scheme may hide behind entities,
     *              whitespace or control characters: "jav&#97;script:", "&#9;javascript:")
     */
    private static function isScriptUrl($url)
    {
        $plain = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = strtolower(preg_replace('/[\x00-\x20\x7f]+/', '', $plain));

        return strpos($plain, 'javascript:') === 0
            || strpos($plain, 'vbscript:') === 0
            || strpos($plain, 'data:text/html') === 0
            || strpos($plain, 'data:image/svg') === 0;
    }

    /**
     * Mail HTML on its way OUT of the module — into a core record (the Emails
     * description) the CRM renders without a sandbox. Parsed and rebuilt by
     * the core HTML Purifier: only what it knows to be harmless survives,
     * no regex guessing
     *
     * @param string $html
     * @return string
     */
    public static function purifyHtml($html)
    {
        $html = (string)$html;
        if ($html === '') {
            return '';
        }
        // the purifier keeps the TEXT of a <style> / <title> it drops — remove the elements first
        $html = preg_replace('#<(style|title|head)\b[^>]*>.*?</\1\s*>#is', '', self::sanitizeHtml($html));

        return (string)vtlib_purify((string)$html);
    }

    /** The gap under a paragraph of an outgoing mail, written into the mail itself */
    private const PARAGRAPH_SPACING = 'margin:0 0 1em 0;';

    /**
     * Paragraphs of an outgoing mail carry their own spacing. Several mail
     * programs (Outlook among them) show a paragraph without the gap a
     * browser gives it — the blank lines the user saw in the editor were
     * gone at the recipient. A paragraph styled with its own margin keeps it
     *
     * @param string $html
     * @return string
     */
    public static function spaceParagraphs($html)
    {
        return preg_replace_callback('/<p\b([^>]*)>/i', function ($match) {
            $attributes = $match[1];
            if (!preg_match('/\sstyle\s*=\s*(["\'])(.*?)\1/is', $attributes, $style)) {
                return '<p' . $attributes . ' style="' . self::PARAGRAPH_SPACING . '">';
            }
            if (stripos($style[2], 'margin') !== false) {
                return $match[0];
            }

            return '<p' . str_replace(
                $style[0],
                ' style=' . $style[1] . self::PARAGRAPH_SPACING . $style[2] . $style[1],
                $attributes
            ) . '>';
        }, (string)$html);
    }

    /**
     * Baseline styles prepended to a message frame — compact paragraph
     * spacing like desktop mail clients (older cached mails lost their
     * <style> blocks to the previous sanitizer and fell back to the browser
     * default 1em <p> margins). A mail's own styles win — they come later
     *
     * @param string $bodyHtml sanitized mail HTML
     * @return string HTML for the frame srcdoc
     */
    public static function frameHtml($bodyHtml)
    {
        if ((string)$bodyHtml === '') {
            return '';
        }

        // <base>: links open in a new tab (inside the frame they would load the
        // remote page into the conversation), and a relative address in the
        // mail resolves against nothing instead of the CRM — an
        // <img src="index.php?module=Users&action=Logout"> must not reach it
        return '<base href="about:blank" target="_blank">'
            . '<style>'
            . 'body{margin:8px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.45;color:#222;word-break:break-word;}'
            . 'p{margin:0 0 8px;}'
            . 'blockquote{margin:4px 0 4px 8px;}'
            // preformatted text (plain mail wrapped in <pre> by a ticket
            // system) runs off the frame in one line — wrap it, the frame
            // grows by height and has no reachable horizontal scrollbar
            . 'pre{white-space:pre-wrap !important;word-wrap:break-word;overflow-wrap:anywhere;}'
            . '</style>' . $bodyHtml;
    }

    /**
     * Prepares the HTML bodies for the sandboxed message frames: baseline styles and a
     * Content-Security-Policy that blocks remote content (images, fonts, styles loaded
     * from the internet) until the user allows it – the same privacy behavior as Basic.
     * Inline cid: images stay visible, they are served by the CRM itself.
     *
     * @param array  $messages timeline rows
     * @param string $siteUrl  site_URL of the CRM
     *
     * @return array rows with body_html framed and has_remote set
     */
    public static function getFramedMessages(array $messages, string $siteUrl): array
    {
        $siteUrl = rtrim($siteUrl, '/');
        $siteOrigin = (string)preg_replace('#^(https?://[^/]+).*$#i', '$1', $siteUrl);
        $endpoint = $siteUrl . '/index.php?module=MailManager&view=ProAttachment';
        $policy = '<meta http-equiv="Content-Security-Policy" content="default-src \'none\'; style-src \'unsafe-inline\'; '
            . 'img-src data: ' . htmlspecialchars($siteOrigin, ENT_QUOTES, 'UTF-8') . '; font-src data:">';

        foreach ($messages as $index => $message) {
            $bodyHtml = (string)($message['body_html'] ?? '');
            $local = str_ireplace($endpoint, '', $bodyHtml);
            $messages[$index]['has_remote'] = 1 === preg_match('/(?:\b(?:src|background|srcset)\s*=\s*["\']?|url\(\s*["\']?|@import\s+["\']?)\s*(?:https?:)?\/\//i', $local);
            $messages[$index]['body_html'] = '' === $bodyHtml ? '' : $policy . self::frameHtml($bodyHtml);
        }

        return $messages;
    }

    /**
     * @param string $text
     * @return bool text pretends to be plain but carries HTML/CSS
     */
    private static function looksLikeMarkup($text)
    {
        return preg_match('/<(html|body|head|style|div|table|td|p|br|span)\b/i', $text) === 1
            || (strpos($text, '{') !== false
                && preg_match('/[\w#.-]+\s*\{[^}]*(font|color|margin|padding|width)[^}]*\}/i', $text) === 1);
    }
}
