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
 * The facts of a reply our code checks itself (I16): links, e-mail addresses, phone
 * numbers, IBANs, amounts and percentages. A fact of the reply that appears nowhere in
 * the conversation, the linked records or the company knowledge is "unverified" — the
 * composer highlights it, nothing is ever blocked.
 */
class MailManager_ProDraftFacts_Helper
{
    public const TYPE_LINK = 'link';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_IBAN = 'iban';
    public const TYPE_AMOUNT = 'amount';
    public const TYPE_PERCENT = 'percent';

    /** The highlight of an unverified fact in the composer */
    public const MARK_CLASS = 'its4you-ai-unverified';

    /** At most so many facts are reported */
    private const MAX_FACTS = 20;

    /** type => pattern */
    private const PATTERNS = [
        self::TYPE_LINK => '/\b(?:https?:\/\/|www\.)[^\s<>"\'()]+/iu',
        self::TYPE_EMAIL => '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/iu',
        self::TYPE_IBAN => '/\b[A-Z]{2}\d{2}(?: ?[A-Z0-9]{4}){2,7}(?: ?[A-Z0-9]{1,4})?\b/u',
        self::TYPE_PERCENT => '/(?<![\d.,])\d{1,3}(?:[.,]\d+)?\s?(?:%|percent|percento|percentá|prozent|procent)/iu',
        self::TYPE_AMOUNT => '/(?:(?:€|\$|£|EUR|USD|CZK|GBP|CHF|PLN|HUF)\s?\d[\d \x{00A0}.,]*\d|\d[\d \x{00A0}.,]*\d?\s?(?:€|\$|£|EUR|USD|CZK|Kč|GBP|CHF|PLN|zł|HUF|Ft|eur|euro|eur\.)(?![\p{L}]))/u',
        self::TYPE_PHONE => '/(?<![\w])\+?\d[\d \-\/()]{7,}\d(?![\w])/u',
    ];

    /**
     * @param string $draft   the reply (plain text)
     * @param string $sources everything the reply may rely on (plain text)
     *
     * @return array of ['type' => TYPE_*, 'value' => as written in the reply]
     */
    public static function getUnverified(string $draft, string $sources): array
    {
        $known = [];

        foreach (self::extract($sources) as $fact) {
            $known[$fact['type'] . ':' . $fact['key']] = true;
        }

        $unverified = [];

        foreach (self::extract($draft) as $fact) {
            $id = $fact['type'] . ':' . $fact['key'];

            if (!isset($known[$id]) && !isset($unverified[$id])) {
                $unverified[$id] = ['type' => $fact['type'], 'value' => $fact['value']];
            }
        }

        return array_slice(array_values($unverified), 0, self::MAX_FACTS);
    }

    /**
     * @param string $text
     *
     * @return array of ['type', 'value', 'key' => the comparable form]
     */
    public static function extract(string $text): array
    {
        $facts = [];
        $taken = [];

        foreach (self::PATTERNS as $type => $pattern) {
            if (!preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            foreach ($matches[0] as [$value, $offset]) {
                $value = rtrim(trim($value), '.,;:!?');
                $end = $offset + strlen($value);

                // a phone inside an IBAN, an amount inside a link — the first reading wins
                foreach ($taken as [$from, $to]) {
                    if ($offset < $to && $end > $from) {
                        continue 2;
                    }
                }

                $key = self::getKey($type, $value);

                if ('' === $key) {
                    continue;
                }

                $taken[] = [$offset, $end];
                $facts[] = ['type' => $type, 'value' => $value, 'key' => $key];
            }
        }

        return $facts;
    }

    /**
     * @param string $type
     * @param string $value
     *
     * @return string the value as compared ('' = not a fact of that type)
     */
    private static function getKey(string $type, string $value): string
    {
        switch ($type) {
            case self::TYPE_LINK:
                return rtrim((string)preg_replace('/^(?:https?:\/\/)?(?:www\.)?/i', '', mb_strtolower($value)), '/');
            case self::TYPE_EMAIL:
                return mb_strtolower($value);
            case self::TYPE_PHONE:
                $digits = (string)preg_replace('/\D/', '', $value);

                // a date, a short number or a year range is no phone
                return strlen($digits) >= 9 ? substr($digits, -9) : '';
            case self::TYPE_IBAN:
                return strtoupper(str_replace(' ', '', $value));
            case self::TYPE_AMOUNT:
            case self::TYPE_PERCENT:
                return self::getNumber($value);
        }

        return '';
    }

    /**
     * "1 200,50 €" and "1200.50 EUR" are the same amount
     *
     * @param string $value
     *
     * @return string the number in it ('' for none)
     */
    private static function getNumber(string $value): string
    {
        $number = (string)preg_replace('/[^\d.,]/u', '', $value);

        if ('' === $number) {
            return '';
        }

        // the last separator followed by one or two digits is the decimal one
        if (preg_match('/^(.*)[.,](\d{1,2})$/', $number, $match)) {
            $number = preg_replace('/[.,]/', '', $match[1]) . '.' . $match[2];
        } else {
            $number = (string)preg_replace('/[.,]/', '', $number);
        }

        if (false !== strpos($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        return ltrim($number, '0') ?: '0';
    }

    /**
     * Marks the unverified facts in the draft HTML (plain paragraphs of textToHtml())
     *
     * @param string $html
     * @param array  $facts getUnverified()
     *
     * @return string
     */
    public static function highlight(string $html, array $facts): string
    {
        $values = array_column($facts, 'value');
        usort($values, function ($a, $b) {
            return mb_strlen($b) <=> mb_strlen($a);
        });
        $replacements = [];

        foreach ($values as $value) {
            $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $replacements[$escaped] = '<mark class="' . self::MARK_CLASS . '">' . $escaped . '</mark>';
        }

        // one pass — a shorter value inside a marked longer one is not marked again
        return $replacements ? strtr($html, $replacements) : $html;
    }

    /**
     * The marks never leave the composer (sending, saving a draft)
     *
     * @param string $html
     *
     * @return string
     */
    public static function stripMarks(string $html): string
    {
        return (string)preg_replace('#<mark\b[^>]*\b' . self::MARK_CLASS . '\b[^>]*>(.*?)</mark>#is', '$1', $html);
    }
}
