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
 * Personal data out of the AI prompts (I15, levels I16): e-mail addresses, IBANs, phone
 * numbers and document numbers become placeholders ([EMAIL_1], [IBAN_1], [PHONE_1],
 * [DOC_1]); the full level also takes out the names of people, companies and addresses
 * of a dictionary (MailManager_ProAiDictionary_Model: [PERSON_1], [COMPANY_1],
 * [ADDRESS_1]) — without regard to case and diacritics, whole words, longest first.
 * mask() keeps the map so the answer gets the real values back (restore(), the same
 * call only); the mail of other customers (solved tickets, similar cases) is masked
 * for good, always on the full level.
 */
class MailManager_ProAnonymizer_Helper
{
    /** ai_anonymize: nothing masked */
    public const LEVEL_OFF = '0';
    /** ai_anonymize: e-mails, phones, IBANs, document numbers */
    public const LEVEL_CONTACT = '1';
    /** ai_anonymize: also names of people and companies and addresses */
    public const LEVEL_FULL = '2';

    public const LEVELS = [self::LEVEL_OFF, self::LEVEL_CONTACT, self::LEVEL_FULL];

    /** Terms of one compiled pattern — the diacritic classes make a pattern large (PCRE limit) */
    private const TERMS_PER_PATTERN = 40;

    /** letter => the letter with its diacritic variants (Central and Western European) */
    private const VARIANTS = [
        'a' => 'aáäàâãåăą', 'c' => 'cčćç', 'd' => 'dďđ', 'e' => 'eéěèêëę', 'i' => 'iíìîï',
        'l' => 'lĺľł', 'n' => 'nňńñ', 'o' => 'oóôöòõőø', 'r' => 'rŕř', 's' => 'sšśşß',
        't' => 'tťţ', 'u' => 'uúůüùûű', 'y' => 'yýÿ', 'z' => 'zžźż',
    ];

    /** placeholder kind => pattern, in the order they are replaced */
    private const PATTERNS = [
        'EMAIL' => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
        'IBAN' => '/\b[A-Z]{2}\d{2}(?: ?[A-Z0-9]{4}){2,7}(?: ?[A-Z0-9]{1,4})?\b/',
        // nine digits at least, spaces, dashes, slashes and brackets between them (no dots: dates)
        'PHONE' => '/(?<![\w\[])\+?\d[\d \-\/()]{7,}\d(?![\w\]])/',
        // invoice, order, contract numbers: letters and five or more digits
        'DOC' => '/\b[A-Z]{1,5}[-\/]?\d{5,}\b/',
    ];

    /**
     * @param string $text
     * @param array  $dictionary kind => [term, ...] of the full level (MailManager_ProAiDictionary_Model)
     * @param string $tagPattern tags left untouched (our prompt wrappers); the dictionary
     *                           applies only between an opening and a closing one when given
     *
     * @return array ['text' => masked text, 'map' => placeholder => original]
     */
    public static function mask(string $text, array $dictionary = [], string $tagPattern = ''): array
    {
        $map = [];
        $counters = [];
        $terms = self::compileDictionary($dictionary);

        if ('' === $tagPattern) {
            return ['text' => self::maskPart($text, $terms, $map, $counters), 'map' => $map];
        }

        $inside = false;
        $masked = '';

        foreach (preg_split($tagPattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE) as $part) {
            if (preg_match($tagPattern, $part)) {
                $inside = '/' !== substr($part, 1, 1);
                $masked .= $part;
            } else {
                $masked .= self::maskPart($part, $inside ? $terms : null, $map, $counters);
            }
        }

        return ['text' => $masked, 'map' => $map];
    }

    /**
     * @param string     $text
     * @param array|null $terms    compileDictionary() — null = the contact patterns only
     * @param array      $map      placeholder => original, shared by the parts of one prompt
     * @param array      $counters kind => last number
     *
     * @return string
     */
    private static function maskPart(string $text, ?array $terms, array &$map, array &$counters): string
    {
        // a placeholder-like text of the mail itself would get a real value back in restore()
        $text = (string)preg_replace('/\[(EMAIL|IBAN|PHONE|DOC|PERSON|COMPANY|ADDRESS)_(\d+)\]/', '($1_$2)', $text);
        $replace = function (string $kind, string $value) use (&$map, &$counters): string {
            // the same value gets the same placeholder
            $existing = array_search($value, $map, true);

            if (false !== $existing) {
                return $existing;
            }

            $counters[$kind] = ($counters[$kind] ?? 0) + 1;
            $placeholder = '[' . $kind . '_' . $counters[$kind] . ']';
            $map[$placeholder] = $value;

            return $placeholder;
        };

        foreach (self::PATTERNS as $kind => $pattern) {
            $text = (string)preg_replace_callback($pattern, function ($match) use ($kind, $replace) {
                if ('PHONE' === $kind && strlen((string)preg_replace('/\D/', '', $match[0])) < 9) {
                    return $match[0];
                }

                return $replace($kind, $match[0]);
            }, $text);

            // names after the e-mail addresses — "jan.novak@" stays one address
            if ('EMAIL' === $kind && $terms) {
                $callback = function ($match) use ($terms, $replace) {
                    return $replace($terms['kinds'][self::getTermKey($match[0])] ?? 'PERSON', $match[0]);
                };

                foreach ($terms['chunks'] as $chunk) {
                    $masked = preg_replace_callback(self::getAlternation($chunk), $callback, $text);

                    // a pattern PCRE refuses: term by term — a name must never stay unmasked
                    if (null === $masked) {
                        foreach ($chunk as $key) {
                            $text = (string)(preg_replace_callback(self::getAlternation([$key]), $callback, $text) ?? $text);
                        }
                    } else {
                        $text = $masked;
                    }
                }
            }
        }

        return $text;
    }

    /**
     * One alternation per chunk of terms, longest first over all kinds ("Novák s.r.o." is
     * a company before "Novák" is a person) — not one pattern per term (I16 review).
     *
     * @param array $dictionary kind => [term, ...]
     *
     * @return array|null ['chunks' => [[term key, ...], ...], 'kinds' => term key => kind], null = none
     */
    private static function compileDictionary(array $dictionary): ?array
    {
        $kinds = [];

        foreach ($dictionary as $kind => $terms) {
            foreach ((array)$terms as $term) {
                $key = self::getTermKey((string)$term);

                if ('' !== $key && !isset($kinds[$key])) {
                    $kinds[$key] = (string)$kind;
                }
            }
        }

        if (!$kinds) {
            return null;
        }

        $keys = array_keys($kinds);
        usort($keys, function ($a, $b) {
            return mb_strlen($b) <=> mb_strlen($a);
        });
        return ['chunks' => array_chunk($keys, self::TERMS_PER_PATTERN), 'kinds' => $kinds];
    }

    /**
     * @param string[] $keys getTermKey() values, longest first
     *
     * @return string one whole-word pattern of them — not inside a word, a number, a
     *                placeholder or an e-mail address
     */
    private static function getAlternation(array $keys): string
    {
        return '/(?<![\p{L}\p{N}_\[@.])(?:' . implode('|', array_map([self::class, 'getTermPattern'], $keys))
            . ')(?![\p{L}\p{N}_\]]|@|\.[\p{L}\p{N}])/iu';
    }

    /**
     * @param string $term
     *
     * @return string the term without case, diacritics and repeated spaces — how terms are compared
     */
    private static function getTermKey(string $term): string
    {
        static $plain = null;

        if (null === $plain) {
            $plain = [];

            foreach (self::VARIANTS as $letter => $variants) {
                foreach (mb_str_split($variants) as $variant) {
                    $plain[$variant] = $letter;
                }
            }
        }

        $term = mb_strtolower(trim((string)preg_replace('/\s+/u', ' ', $term)));

        return strtr($term, $plain);
    }

    /**
     * @param string $key getTermKey()
     *
     * @return string the regex of the term, case and diacritics aside
     */
    private static function getTermPattern(string $key): string
    {
        $pattern = '';

        foreach (mb_str_split($key) as $char) {
            if (' ' === $char) {
                $pattern .= '\s+';
            } elseif (isset(self::VARIANTS[$char])) {
                $pattern .= '[' . self::VARIANTS[$char] . ']';
            } else {
                $pattern .= preg_quote($char, '/');
            }
        }

        return $pattern;
    }

    /**
     * Masks for good — the data of other customers never reaches the prompt
     *
     * @param string $text
     * @param array  $dictionary the parties of that customer's record or conversation
     *
     * @return string
     */
    public static function maskPermanently(string $text, array $dictionary = []): string
    {
        return self::mask($text, $dictionary)['text'];
    }

    /**
     * @return string the level of the AI settings (LEVEL_*)
     */
    public static function getLevel(): string
    {
        $level = (string)MailManager_ProAiConfig_Model::get('ai_anonymize');

        return in_array($level, self::LEVELS, true) ? $level : self::LEVEL_OFF;
    }

    /**
     * @param string $text the AI answer
     * @param array  $map  placeholder => original from mask()
     *
     * @return string the answer with the real values — inside a JSON answer JSON-escaped,
     *                so a street over two lines or a name with quotes keeps it valid
     */
    public static function restore(string $text, array $map): string
    {
        if (!$map) {
            return $text;
        }

        if (preg_match('/\{.*\}/s', $text, $match) && is_array(json_decode($match[0], true))) {
            $escaped = [];

            foreach ($map as $placeholder => $value) {
                $escaped[$placeholder] = substr((string)json_encode((string)$value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 1, -1);
            }

            return strtr($text, $escaped);
        }

        return strtr($text, $map);
    }
}
