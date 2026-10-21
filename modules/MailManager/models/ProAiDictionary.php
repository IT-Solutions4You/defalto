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
 * The names the full anonymization (I16) takes out of a prompt: the people, companies
 * and addresses of a conversation — its senders and recipients, its linked records,
 * the CRM records of its addresses and the CRM users — or of one record (a solved
 * ticket of another customer). The result feeds MailManager_ProAnonymizer_Helper::mask().
 */
class MailManager_ProAiDictionary_Model
{
    public const PERSON = 'PERSON';
    public const COMPANY = 'COMPANY';
    public const ADDRESS = 'ADDRESS';

    /** Shorter words are never masked on their own (initials, "Jo") */
    private const MIN_LENGTH = 3;

    /** A name with one of these is a company, not a person */
    private const COMPANY_HINT = '/\b(s\.\s?r\.\s?o|spol|a\.\s?s|k\.\s?s|v\.\s?o\.\s?s|gmbh|ag|ltd|llc|inc|corp|plc|sp\.\s?z\s?o\.\s?o|kft|srl|sas|bv|nv|oy|ab)\b\.?|\d/iu';

    /** Titles and salutations — never a name of their own */
    private const NOT_NAMES = [
        'ing', 'mgr', 'bc', 'phd', 'mudr', 'judr', 'rndr', 'phdr', 'paeddr', 'doc', 'prof', 'dr', 'dipl',
        'mba', 'msc', 'bsc', 'mr', 'mrs', 'ms', 'miss', 'sir', 'pán', 'pani', 'paní', 'herr', 'frau', 'test',
    ];

    /** Ordinary words a name or a company may be — never masked on their own (I16 review) */
    private const COMMON_WORDS = [
        'data', 'mail', 'email', 'draft', 'message', 'content', 'source', 'info', 'office', 'admin', 'support',
        'sales', 'service', 'team', 'shop', 'group', 'system', 'home', 'web', 'online', 'company', 'firma',
        'nový', 'nova', 'nová', 'malý', 'malá', 'veľký', 'velký', 'biely', 'čierny', 'černý', 'dobrý', 'dobrá',
        'viera', 'milan', 'dana', 'jan', 'ján', 'marek', 'roman', 'jakub', 'max', 'mark', 'will', 'may', 'june',
        'new', 'old', 'white', 'black', 'young', 'king', 'smith', 'post', 'bratislava', 'praha', 'wien',
    ];

    /** @var array|null the users' terms, read once per request */
    private static ?array $userTerms = null;

    /** Users of the CRM — their names are masked too */
    private const MAX_USERS = 300;

    /** Per request: thread id => dictionary */
    private static array $cache = [];

    /**
     * @param int $threadId
     *
     * @return array kind => [term, ...] (longest first) — empty for 0
     */
    public static function getForThread(int $threadId): array
    {
        if ($threadId <= 0) {
            return [];
        }

        if (isset(self::$cache[$threadId])) {
            return self::$cache[$threadId];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT from_name, from_email, to_addrs, cc_addrs FROM df_mailmanager_message WHERE threadid = ? LIMIT 200',
            [$threadId]
        );
        $terms = [];
        $emails = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            self::addPartyName($terms, (string)$row['from_name'], (string)$row['from_email']);
            $emails[] = strtolower((string)$row['from_email']);

            foreach (['to_addrs', 'cc_addrs'] as $column) {
                foreach ((array)json_decode((string)$row[$column], true) as $address) {
                    self::addPartyName($terms, (string)($address['name'] ?? ''), (string)($address['email'] ?? ''));
                    $emails[] = strtolower((string)($address['email'] ?? ''));
                }
            }
        }

        $relResult = $adb->pquery(
            'SELECT r.crmid FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ?',
            [$threadId]
        );
        $recordIds = [];

        while ($row = $adb->fetchByAssoc($relResult, -1, false)) {
            $recordIds[] = (int)$row['crmid'];
        }

        self::addRecords($terms, array_merge($recordIds, self::getRecordIdsByEmail(array_filter(array_unique($emails)))));
        self::addUsers($terms);

        return self::$cache[$threadId] = self::finish($terms);
    }

    /**
     * The names of several conversations — one call about many (the digest summary).
     *
     * @param int[] $threadIds
     *
     * @return array kind => [term, ...]
     */
    public static function getForThreads(array $threadIds): array
    {
        $terms = [];

        foreach (array_unique(array_map('intval', $threadIds)) as $threadId) {
            foreach (self::getForThread($threadId) as $kind => $values) {
                $terms[$kind] = array_merge($terms[$kind] ?? [], $values);
            }
        }

        return self::finish($terms);
    }

    /**
     * The parties of one record (a solved ticket: its organization and contact).
     *
     * @param int[] $recordIds
     *
     * @return array kind => [term, ...]
     */
    public static function getForRecords(array $recordIds): array
    {
        $terms = [];
        self::addRecords($terms, $recordIds);

        return self::finish($terms);
    }

    /**
     * @param array  $terms
     * @param string $name  a display name of a mail address
     * @param string $email
     *
     * @return void
     */
    private static function addPartyName(array &$terms, string $name, string $email): void
    {
        $name = trim(decode_html($name), " \t\"'");

        if ('' === $name || false !== strpos($name, '@') || 0 === strcasecmp($name, $email)) {
            return;
        }

        if (preg_match(self::COMPANY_HINT, $name)) {
            self::addCompany($terms, $name);
        } else {
            self::addPerson($terms, $name);
        }
    }

    /**
     * The full name and each of its words (a mail says "Mr Novák")
     *
     * @param array  $terms
     * @param string $name
     *
     * @return void
     */
    private static function addPerson(array &$terms, string $name): void
    {
        $name = trim((string)preg_replace('/\s+/u', ' ', $name));

        if ('' === $name) {
            return;
        }

        $terms[self::PERSON][] = $name;

        foreach (preg_split('/[\s,]+/u', $name) as $part) {
            $part = trim($part, '.');

            if (mb_strlen($part) >= self::MIN_LENGTH && !in_array(mb_strtolower($part), self::NOT_NAMES, true)) {
                $terms[self::PERSON][] = $part;
            }
        }
    }

    /**
     * The company name and the name without its legal form ("Alfa s.r.o." → "Alfa")
     *
     * @param array  $terms
     * @param string $name
     *
     * @return void
     */
    private static function addCompany(array &$terms, string $name): void
    {
        $name = trim((string)preg_replace('/\s+/u', ' ', $name));

        if ('' === $name) {
            return;
        }

        $terms[self::COMPANY][] = $name;
        $base = trim((string)preg_replace('/[\s,]*\b(s\.\s?r\.\s?o|spol\. s r\.\s?o|a\.\s?s|k\.\s?s|v\.\s?o\.\s?s|gmbh|ag|ltd|llc|inc|corp|plc|kft|srl|sas|bv|nv)\b\.?\s*$/iu', '', $name));

        if ($base !== $name && mb_strlen($base) > self::MIN_LENGTH) {
            $terms[self::COMPANY][] = $base;
        }
    }

    /**
     * @param array  $terms
     * @param string $street
     * @param string $city
     *
     * @return void
     */
    private static function addAddress(array &$terms, string $street, string $city): void
    {
        foreach ([$street, $city] as $value) {
            $value = trim(decode_html($value));

            if (mb_strlen($value) >= self::MIN_LENGTH) {
                $terms[self::ADDRESS][] = $value;
            }
        }
    }

    /**
     * Names and addresses of contacts, leads and organizations (a ticket: its
     * organization and contact)
     *
     * @param array $terms
     * @param int[] $recordIds
     *
     * @return void
     */
    private static function addRecords(array &$terms, array $recordIds): void
    {
        $recordIds = array_values(array_unique(array_filter(array_map('intval', $recordIds))));

        if (!$recordIds) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $marks = generateQuestionMarks($recordIds);

        // tickets lead to their organization and contact
        $ticketResult = $adb->pquery(
            'SELECT parent_id, contact_id FROM vtiger_troubletickets WHERE ticketid IN (' . $marks . ')',
            $recordIds
        );

        while ($row = $adb->fetchByAssoc($ticketResult, -1, false)) {
            $recordIds[] = (int)$row['parent_id'];
            $recordIds[] = (int)$row['contact_id'];
        }

        $recordIds = array_values(array_unique(array_filter($recordIds)));
        $marks = generateQuestionMarks($recordIds);

        $contactResult = $adb->pquery(
            'SELECT cd.firstname, cd.lastname, ca.mailingstreet, ca.mailingcity, cd.account_id
             FROM vtiger_contactdetails cd LEFT JOIN vtiger_contactaddress ca ON ca.contactaddressid = cd.contactid
             WHERE cd.contactid IN (' . $marks . ')',
            $recordIds
        );
        $accountIds = $recordIds;

        while ($row = $adb->fetchByAssoc($contactResult, -1, false)) {
            self::addPerson($terms, trim(decode_html($row['firstname'] . ' ' . $row['lastname'])));
            self::addAddress($terms, (string)$row['mailingstreet'], (string)$row['mailingcity']);
            $accountIds[] = (int)$row['account_id'];
        }

        $leadResult = $adb->pquery(
            'SELECT ld.firstname, ld.lastname, ld.company, la.lane, la.city
             FROM vtiger_leaddetails ld LEFT JOIN vtiger_leadaddress la ON la.leadaddressid = ld.leadid
             WHERE ld.leadid IN (' . $marks . ')',
            $recordIds
        );

        while ($row = $adb->fetchByAssoc($leadResult, -1, false)) {
            self::addPerson($terms, trim(decode_html($row['firstname'] . ' ' . $row['lastname'])));
            self::addCompany($terms, decode_html((string)$row['company']));
            self::addAddress($terms, (string)$row['lane'], (string)$row['city']);
        }

        $accountIds = array_values(array_unique(array_filter($accountIds)));

        if (!$accountIds) {
            return;
        }

        $accountResult = $adb->pquery(
            'SELECT a.accountname, b.bill_street, b.bill_city
             FROM vtiger_account a LEFT JOIN vtiger_accountbillads b ON b.accountaddressid = a.accountid
             WHERE a.accountid IN (' . generateQuestionMarks($accountIds) . ')',
            $accountIds
        );

        while ($row = $adb->fetchByAssoc($accountResult, -1, false)) {
            self::addCompany($terms, decode_html((string)$row['accountname']));
            self::addAddress($terms, (string)$row['bill_street'], (string)$row['bill_city']);
        }
    }

    /**
     * @param string[] $emails
     *
     * @return int[] contacts, leads and organizations with one of the addresses
     */
    private static function getRecordIdsByEmail(array $emails): array
    {
        $emails = array_values(array_slice($emails, 0, 50));

        if (!$emails) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $marks = generateQuestionMarks($emails);
        $result = $adb->pquery(
            'SELECT cd.contactid AS id FROM vtiger_contactdetails cd
             INNER JOIN vtiger_crmentity ce ON ce.crmid = cd.contactid AND ce.deleted = 0
             WHERE cd.email IN (' . $marks . ')
             UNION SELECT ld.leadid FROM vtiger_leaddetails ld
             INNER JOIN vtiger_crmentity ce ON ce.crmid = ld.leadid AND ce.deleted = 0
             WHERE ld.email IN (' . $marks . ') AND ld.converted = 0
             UNION SELECT a.accountid FROM vtiger_account a
             INNER JOIN vtiger_crmentity ce ON ce.crmid = a.accountid AND ce.deleted = 0
             WHERE a.email1 IN (' . $marks . ')
             LIMIT 30',
            array_merge($emails, $emails, $emails)
        );
        $ids = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $ids[] = (int)$row['id'];
        }

        return $ids;
    }

    /**
     * @param array $terms
     *
     * @return void
     */
    private static function addUsers(array &$terms): void
    {
        if (null === self::$userTerms) {
            self::$userTerms = [];
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                "SELECT first_name, last_name FROM vtiger_users WHERE status = 'Active' AND deleted = 0 LIMIT " . self::MAX_USERS,
                []
            );

            // the full name and the surname — a first name alone is too common a word
            while ($row = $adb->fetchByAssoc($result, -1, false)) {
                $full = trim(decode_html($row['first_name'] . ' ' . $row['last_name']));
                $last = trim(decode_html((string)$row['last_name']));

                if ('' !== $full) {
                    self::$userTerms[] = $full;
                }

                if (mb_strlen($last) >= self::MIN_LENGTH && !in_array(mb_strtolower($last), self::NOT_NAMES, true)) {
                    self::$userTerms[] = $last;
                }
            }
        }

        foreach (self::$userTerms as $term) {
            $terms[self::PERSON][] = $term;
        }
    }

    /**
     * @param array $terms kind => [term, ...]
     *
     * @return array kind => unique terms, longest first
     */
    private static function finish(array $terms): array
    {
        $dictionary = [];

        foreach ($terms as $kind => $values) {
            $unique = [];

            foreach ($values as $value) {
                // a street over two lines is one term
                $value = trim((string)preg_replace('/\s+/u', ' ', $value));
                $isWord = false === strpos($value, ' ');

                if (mb_strlen($value) >= self::MIN_LENGTH
                    && !($isWord && in_array(mb_strtolower($value), self::COMMON_WORDS, true))) {
                    $unique[mb_strtolower($value)] = $value;
                }
            }

            $values = array_values($unique);
            usort($values, function ($a, $b) {
                return mb_strlen($b) <=> mb_strlen($a);
            });
            $dictionary[$kind] = $values;
        }

        return $dictionary;
    }
}
