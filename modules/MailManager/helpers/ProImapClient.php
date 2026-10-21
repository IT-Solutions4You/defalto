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
 * Minimal socket IMAP4rev1 client — no ext-imap dependency.
 * Supports SSL / STARTTLS, LIST, SELECT, STATUS, UID SEARCH and UID FETCH
 * with proper literal ({n}) handling.
 */
class MailManager_ProImapClient_Helper
{
    private const TIMEOUT = 15;

    /** Read timeout of a full-text search — the server may scan every body */
    private const SEARCH_TIMEOUT = 60;

    /** Message of a rejected LOGIN (wrong username or password) */
    public const LOGIN_ERROR = 'IMAP login failed';

    /** Message of a failed certificate check — the settings offer to trust the certificate */
    public const CERTIFICATE_ERROR = 'The server certificate could not be verified (self-signed, expired, '
        . 'another host name or not the trusted one).';

    /** @var resource|null */
    private $stream;

    /** @var int */
    private $tagCounter = 0;

    /** @var string */
    private $delimiter = '.';

    /** @var string[]|null uppercase server capabilities, loaded on first use */
    private $capabilities;

    private $server;
    private $port;
    private $encryption;

    /** @var string sha256 of a certificate the admin trusted explicitly ('' = verify normally) */
    private $trustedFingerprint;

    /** @var bool read the server certificate instead of verifying it (details for the trust dialog) */
    private $captureCertificate = false;

    /**
     * @param string $server
     * @param int $port
     * @param string $encryption ssl | starttls | none
     * @param string $trustedFingerprint sha256 of a self-signed certificate the
     *                                   admin trusted — only that one is accepted
     */
    public function __construct($server, $port, $encryption, $trustedFingerprint = '')
    {
        $this->server = $server;
        $this->port = (int)$port;
        $this->encryption = $encryption;
        $this->trustedFingerprint = self::normalizeFingerprint($trustedFingerprint);
    }

    /**
     * @param string $fingerprint sha256, hex with or without colons
     * @return string lowercase hex, '' when not a sha256 fingerprint
     */
    public static function normalizeFingerprint($fingerprint)
    {
        $fingerprint = strtolower(str_replace([':', ' '], '', (string)$fingerprint));

        return preg_match('/^[0-9a-f]{64}$/', $fingerprint) ? $fingerprint : '';
    }

    /**
     * TLS options of the connection — the certificate and the host name are
     * verified by the certificate authorities; a certificate the admin
     * trusted explicitly (Thunderbird-style exception) is accepted only while
     * the server presents exactly that one
     *
     * @param string $server
     * @param string $trustedFingerprint sha256 ('' = regular verification)
     * @param bool $capture accept anything and capture the certificate (trust dialog)
     * @return resource stream context
     */
    public static function createContext($server, $trustedFingerprint = '', $capture = false)
    {
        $fingerprint = self::normalizeFingerprint($trustedFingerprint);
        if ($capture) {
            $ssl = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true,
                'capture_peer_cert' => true];
        } elseif ($fingerprint !== '') {
            $ssl = ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true,
                'peer_fingerprint' => ['sha256' => $fingerprint]];
        } else {
            $ssl = ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $server,
                'allow_self_signed' => false];
        }

        return stream_context_create(['ssl' => $ssl]);
    }

    /**
     * Runs a socket/TLS call and collects the PHP warnings it raises — the
     * reason of a failed handshake exists only as a warning, the call itself
     * returns false with an empty error string
     *
     * @param callable $call
     * @param array $warnings collected warning messages (out)
     * @return mixed the call's return value
     */
    public static function collectWarnings(callable $call, &$warnings)
    {
        $warnings = [];
        set_error_handler(function ($number, $message) use (&$warnings) {
            $warnings[] = (string)$message;

            return true;
        });
        try {
            return $call();
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @param array $warnings from collectWarnings()
     * @return bool the failure is the server certificate (not the network)
     */
    public static function isCertificateError(array $warnings)
    {
        foreach ($warnings as $warning) {
            if (stripos($warning, 'certificate') !== false || stripos($warning, 'peer') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Readable reason of a failed connect / TLS handshake
     *
     * @param array $warnings from collectWarnings()
     * @param string $fallback
     * @return string
     */
    public static function tlsError(array $warnings, $fallback)
    {
        if (self::isCertificateError($warnings)) {
            return self::CERTIFICATE_ERROR;
        }

        return $fallback;
    }

    /**
     * Does the server present a certificate that verifies (trusted issuer,
     * matching host name)? Used once by the migration for existing mailboxes
     *
     * @param string $server
     * @param int $port
     * @param string $encryption ssl | starttls
     * @return bool
     */
    public static function probeCertificate($server, $port, $encryption)
    {
        $client = new self($server, $port, $encryption);
        try {
            $client->openSecureStream();
            $client->disconnect();

            return true;
        } catch (Exception $e) {
            $client->disconnect();

            return false;
        }
    }

    /**
     * The certificate the server presents — for the trust dialog (no login,
     * no verification; the admin decides from these details)
     *
     * @param string $server
     * @param int $port
     * @param string $encryption ssl | starttls
     * @return array|null ['fingerprint', 'subject', 'issuer', 'valid_from', 'valid_to' (unix), 'self_signed']
     */
    public static function fetchCertificate($server, $port, $encryption)
    {
        $client = new self($server, $port, $encryption);
        $client->captureCertificate = true;
        try {
            $client->openSecureStream();
            $params = stream_context_get_params($client->stream);
            $certificate = $params['options']['ssl']['peer_certificate'] ?? null;
        } catch (Exception $e) {
            $certificate = null;
        }
        $client->disconnect();
        if (!$certificate) {
            return null;
        }

        $info = openssl_x509_parse($certificate) ?: [];
        $subject = (string)($info['subject']['CN'] ?? '');
        $issuer = (string)($info['issuer']['O'] ?? ($info['issuer']['CN'] ?? ''));

        return [
            'fingerprint' => (string)openssl_x509_fingerprint($certificate, 'sha256'),
            'subject' => $subject,
            'issuer' => $issuer,
            'valid_from' => (int)($info['validFrom_time_t'] ?? 0),
            'valid_to' => (int)($info['validTo_time_t'] ?? 0),
            'self_signed' => ($info['subject'] ?? null) === ($info['issuer'] ?? null),
        ];
    }

    /**
     * Folder role from the SPECIAL-USE flags, the name as the fallback
     *
     * @param string $flags lowercased LIST flags
     * @param string $name display folder name
     * @return string inbox | sent | drafts | spam | trash | archive | custom
     */
    public static function folderType($flags, $name)
    {
        $nameLower = strtolower($name);
        if ($nameLower === 'inbox') {
            return 'inbox';
        }

        $map = [
            '\\sent' => 'sent', '\\drafts' => 'drafts', '\\junk' => 'spam',
            '\\trash' => 'trash', '\\archive' => 'archive',
        ];
        foreach ($map as $flag => $type) {
            if (strpos($flags, $flag) !== false) {
                return $type;
            }
        }

        $nameMap = [
            'sent' => 'sent', 'drafts' => 'drafts', 'draft' => 'drafts',
            'spam' => 'spam', 'junk' => 'spam', 'trash' => 'trash',
            'deleted' => 'trash', 'archive' => 'archive',
        ];
        foreach ($nameMap as $needle => $type) {
            if (strpos($nameLower, $needle) !== false) {
                return $type;
            }
        }

        return 'custom';
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Opens the connection and authenticates
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws Exception on connect/TLS/login failure
     */
    public function connect($username, $password)
    {
        $this->openSecureStream();

        $reply = $this->command('LOGIN ' . $this->quote($username) . ' ' . $this->quote($password));
        if (!$this->isOk($reply)) {
            throw new Exception(self::LOGIN_ERROR);
        }
    }

    /**
     * Opens the connection and authenticates with an OAuth 2 access token (SASL XOAUTH2,
     * used by Google and Microsoft 365 IMAP servers)
     *
     * @param string $username    mailbox address
     * @param string $accessToken OAuth 2 access token
     *
     * @return void
     * @throws Exception on connect/TLS/authentication failure
     */
    public function connectXOAuth2($username, $accessToken)
    {
        $this->openSecureStream();

        $credentials = base64_encode('user=' . $username . "\x01auth=Bearer " . $accessToken . "\x01\x01");
        $tag = $this->nextTag();
        $this->write($tag . ' AUTHENTICATE XOAUTH2 ' . $credentials . "\r\n");

        while (true) {
            $line = $this->readLine();

            if (false === $line) {
                throw new Exception('IMAP connection dropped');
            }

            // a rejected token comes back as a continuation with the error; an empty line ends it
            if (str_starts_with($line, '+')) {
                $this->write("\r\n");
                continue;
            }

            if (str_starts_with($line, $tag . ' ')) {
                break;
            }
        }

        if (!$this->isOk(['status' => trim($line)])) {
            throw new Exception(self::LOGIN_ERROR);
        }
    }

    /**
     * Opens the socket, reads the greeting and establishes TLS (implicit or
     * STARTTLS) — everything before the login
     *
     * @return void
     * @throws Exception on connect / TLS failure
     */
    private function openSecureStream()
    {
        $remote = ($this->encryption === 'ssl' ? 'ssl://' : 'tcp://') . $this->server . ':' . $this->port;
        $context = self::createContext($this->server, $this->trustedFingerprint, $this->captureCertificate);

        $errno = 0;
        $errstr = '';
        $this->stream = self::collectWarnings(function () use ($remote, &$errno, &$errstr, $context) {
            return stream_socket_client($remote, $errno, $errstr, self::TIMEOUT, STREAM_CLIENT_CONNECT, $context);
        }, $warnings);
        if (!$this->stream) {
            throw new Exception(self::tlsError($warnings, 'IMAP connect failed: ' . (trim($errstr) ?: $errno)));
        }
        stream_set_timeout($this->stream, self::TIMEOUT);

        $greeting = $this->readLine();
        if (strpos($greeting, '* OK') !== 0 && strpos($greeting, '* PREAUTH') !== 0) {
            throw new Exception('No IMAP greeting from the server');
        }

        if ($this->encryption === 'starttls') {
            $reply = $this->command('STARTTLS');
            if (!$this->isOk($reply)) {
                throw new Exception('STARTTLS not supported by the server');
            }
            $stream = $this->stream;
            $secured = self::collectWarnings(function () use ($stream) {
                return stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }, $warnings);
            if (!$secured) {
                throw new Exception(self::tlsError($warnings, 'TLS negotiation failed'));
            }
        }
    }

    /**
     * @return void
     */
    public function disconnect()
    {
        if ($this->stream) {
            @fwrite($this->stream, $this->nextTag() . " LOGOUT\r\n");
            @fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * @return array of ['remote_id', 'name', 'flags' (lowercased string)]
     */
    public function listFolders()
    {
        $reply = $this->command('LIST "" "*"');
        list($folders, $delimiter) = self::parseListReply($reply['raw']);
        if ($delimiter !== '') {
            $this->delimiter = $delimiter;
        }

        return $folders;
    }

    /**
     * Parses a LIST reply — selectable folders and the hierarchy delimiter of
     * the personal folders
     *
     * @param string $raw LIST reply
     * @return array [folders (['remote_id', 'name', 'flags']), delimiter ('' = unknown)]
     */
    public static function parseListReply($raw)
    {

        $folders = [];
        if (!preg_match_all('/^\* LIST \(([^)]*)\) (?:"([^"]*)"|NIL) (.+?)\r?$/mi', $raw, $matches, PREG_SET_ORDER)) {
            return [$folders, ''];
        }
        // The hierarchy delimiter is the one of the personal folders — other
        // namespaces (shared, public roots) may announce a different one, so
        // the INBOX line decides, otherwise the majority of real folders
        $inboxDelimiter = '';
        $votes = [];
        foreach ($matches as $match) {
            $flags = strtolower($match[1]);
            if (strpos($flags, '\\noselect') !== false) {
                continue;
            }
            $rawName = trim($match[3]);
            if ($rawName !== '' && $rawName[0] === '"') {
                $rawName = stripcslashes(substr($rawName, 1, -1));
            }
            if ($match[2] !== '') {
                $votes[$match[2]] = ($votes[$match[2]] ?? 0) + 1;
                if (strcasecmp($rawName, 'INBOX') === 0) {
                    $inboxDelimiter = $match[2];
                }
            }
            $folders[] = [
                'remote_id' => $rawName,
                'name' => @mb_convert_encoding($rawName, 'UTF-8', 'UTF7-IMAP') ?: $rawName,
                'flags' => $flags,
            ];
        }
        $delimiter = $inboxDelimiter;
        if ($delimiter === '' && $votes) {
            arsort($votes);
            $delimiter = (string)key($votes);
        }

        return [$folders, $delimiter];
    }

    /**
     * Opens a folder (read-only EXAMINE by default; writable SELECT is needed
     * for STORE flag updates)
     *
     * @param string $folder remote folder name
     * @param bool $readOnly
     * @return array ['exists' => int, 'uidvalidity' => int, 'uidnext' => int]
     * @throws Exception when the folder cannot be opened
     */
    public function select($folder, $readOnly = true)
    {
        $reply = $this->command(($readOnly ? 'EXAMINE ' : 'SELECT ') . $this->quote($folder));
        if (!$this->isOk($reply)) {
            throw new Exception('Cannot open folder ' . $folder);
        }

        $result = ['exists' => 0, 'uidvalidity' => 0, 'uidnext' => 0];
        if (preg_match('/^\* (\d+) EXISTS/m', $reply['raw'], $m)) {
            $result['exists'] = (int)$m[1];
        }
        if (preg_match('/UIDVALIDITY (\d+)/', $reply['raw'], $m)) {
            $result['uidvalidity'] = (int)$m[1];
        }
        if (preg_match('/UIDNEXT (\d+)/', $reply['raw'], $m)) {
            $result['uidnext'] = (int)$m[1];
        }

        return $result;
    }

    /**
     * The state of the folder on the server: the \Seen flag of its messages
     * (read in Thunderbird or on a phone must show as read here too) and the
     * complete UID list (mail deleted or moved elsewhere must leave the
     * cache). With CONDSTORE only the flags changed since the given
     * mod-sequence travel. The UID list is read only when the folder's
     * signature (UIDVALIDITY:EXISTS:UIDNEXT) moved — a removal lowers
     * EXISTS unless a new message came, which raises UIDNEXT
     *
     * @param string $folder remote folder name
     * @param int $sinceModseq HIGHESTMODSEQ of the previous run (0 = all flags)
     * @param string $previousSignature signature of the previous complete run
     * @return array ['seen' => [uid => bool], 'modseq' => int (0 = no CONDSTORE),
     *                'uids' => int[]|null (null = unchanged or not trustworthy, remove nothing),
     *                'signature' => string ('' = keep comparing next time)]
     * @throws Exception when the folder cannot be opened or read
     */
    public function fetchFolderState($folder, $sinceModseq = 0, $previousSignature = '')
    {
        $condstore = $this->hasCapability('CONDSTORE');
        $reply = $this->command('EXAMINE ' . $this->quote($folder) . ($condstore ? ' (CONDSTORE)' : ''));
        if (!$this->isOk($reply)) {
            throw new Exception('Cannot open folder ' . $folder);
        }
        $modseq = $condstore && preg_match('/HIGHESTMODSEQ (\d+)/', $reply['raw'], $m) ? (int)$m[1] : 0;
        $hasExists = (bool)preg_match('/^\* (\d+) EXISTS/m', $reply['raw'], $m);
        $exists = $hasExists ? (int)$m[1] : 0;
        $signature = '';
        if ($hasExists && preg_match('/UIDVALIDITY (\d+)/', $reply['raw'], $validity)
            && preg_match('/UIDNEXT (\d+)/', $reply['raw'], $next)) {
            $signature = $validity[1] . ':' . $exists . ':' . $next[1];
        }
        $unchanged = $signature !== '' && $signature === (string)$previousSignature;

        $incremental = $modseq > 0 && $sinceModseq > 0;
        $seen = [];
        // "1:*" of an empty folder is an error on some servers; an unchanged
        // mod-sequence means no flag moved since the previous run
        if ($exists > 0 && !($incremental && $modseq === (int)$sinceModseq)) {
            $fetch = $this->command(
                'UID FETCH 1:* (FLAGS)' . ($incremental ? ' (CHANGEDSINCE ' . (int)$sinceModseq . ')' : '')
            );
            if (!$this->isOk($fetch)) {
                throw new Exception('Cannot read the flags of ' . $folder);
            }
            if (preg_match_all('/^\* \d+ FETCH \((.*)\)\r?$/m', $fetch['raw'], $lines)) {
                foreach ($lines[1] as $line) {
                    if (preg_match('/\bUID (\d+)/', $line, $uid)
                        && preg_match('/\bFLAGS \(([^)]*)\)/', $line, $flags)) {
                        $seen[(int)$uid[1]] = stripos($flags[1], '\\Seen') !== false;
                    }
                }
            }
        }

        if (!$hasExists || $unchanged) {
            // nothing added or removed since the previous complete run
            $uids = null;
        } elseif ($exists === 0) {
            $uids = [];
        } elseif (!$incremental) {
            $uids = array_keys($seen);
        } else {
            $uids = $this->uidSearch('ALL');
        }
        // a list that does not match the message count is a misread reply —
        // trusting it would drop mail the server still has; the next run
        // compares again
        if ($uids !== null && count($uids) !== $exists) {
            $uids = null;
            $signature = '';
        }

        return ['seen' => $seen, 'modseq' => $modseq, 'uids' => $uids, 'signature' => $signature];
    }

    /**
     * @param string $folder
     * @return array ['messages' => int, 'unseen' => int]
     */
    public function status($folder)
    {
        $reply = $this->command('STATUS ' . $this->quote($folder) . ' (MESSAGES UNSEEN)');

        $result = ['messages' => 0, 'unseen' => 0];
        if (preg_match('/MESSAGES (\d+)/', $reply['raw'], $m)) {
            $result['messages'] = (int)$m[1];
        }
        if (preg_match('/UNSEEN (\d+)/', $reply['raw'], $m)) {
            $result['unseen'] = (int)$m[1];
        }

        return $result;
    }

    /**
     * Messages of the selected folder carrying a header value
     *
     * @param string $header e.g. 'Message-ID'
     * @param string $value
     * @return int[] ascending UIDs
     */
    public function uidSearchHeader($header, $value)
    {
        return $this->uidSearch('HEADER ' . $this->quote($header) . ' ' . $this->quote($value));
    }

    /**
     * @param string $criteria e.g. 'UID 12:*' or 'SINCE 01-Jan-2026' or 'ALL'
     * @return int[] ascending UIDs
     */
    public function uidSearch($criteria)
    {
        return self::parseSearchReply($this->command('UID SEARCH ' . $criteria)['raw']);
    }

    /**
     * Full-text search in the selected folder (subject, addresses, body).
     * The term travels as a UTF-8 literal — a quoted string must be 7-bit
     *
     * @param string $term
     * @return int[] ascending UIDs
     * @throws Exception when the server refuses the search
     */
    public function uidSearchText($term)
    {
        // without a full-text index the server reads every body — a big
        // folder may take longer than the usual read timeout
        stream_set_timeout($this->stream, self::SEARCH_TIMEOUT);
        try {
            $reply = $this->commandWithLiteral('UID SEARCH CHARSET UTF-8 TEXT', (string)$term);
        } finally {
            stream_set_timeout($this->stream, self::TIMEOUT);
        }
        if (!$this->isOk($reply)) {
            throw new Exception('Search refused: ' . $reply['status']);
        }

        return self::parseSearchReply($reply['raw']);
    }

    /**
     * @param string $raw untagged part of a SEARCH reply
     * @return int[] ascending UIDs
     */
    private static function parseSearchReply($raw)
    {
        // Split, never a repeated regex group — a large folder answers with
        // one line of tens of thousands of UIDs, the PCRE backtrack limit
        // then fails silently and the list came back empty
        $uids = [];
        foreach (explode("\n", $raw) as $line) {
            $line = rtrim($line, "\r");
            if (strncasecmp($line, '* SEARCH', 8) !== 0) {
                continue;
            }
            foreach (explode(' ', substr($line, 8)) as $uid) {
                if ($uid !== '' && ctype_digit($uid)) {
                    $uids[] = (int)$uid;
                }
            }
        }
        sort($uids);

        return $uids;
    }

    /**
     * Fetches one message: flags, size and the raw source (capped)
     *
     * @param int $uid
     * @param int $maxBytes partial fetch cap for the raw source
     * @return array|null|false ['flags' => string, 'size' => int, 'raw' => string, 'truncated' => bool];
     *                          null = no such message any more (expunged meanwhile),
     *                          false = the server refused the fetch (worth a retry)
     */
    public function uidFetchRaw($uid, $maxBytes = 524288)
    {
        $reply = $this->command('UID FETCH ' . (int)$uid . ' (UID FLAGS RFC822.SIZE BODY.PEEK[]<0.' . (int)$maxBytes . '>)');
        if (!$this->isOk($reply)) {
            return false;
        }
        if (!empty($reply['literals'])) {
            $raw = $reply['literals'][0];
        } elseif (preg_match('/BODY\[\]<0> "((?:[^"\\\\]|\\\\.)*)"/', $reply['raw'], $m)) {
            // RFC 3501 lets the server send a short body as a quoted string
            $raw = stripcslashes($m[1]);
        } else {
            return null;
        }

        $flags = '';
        if (preg_match('/FLAGS \(([^)]*)\)/i', $reply['raw'], $m)) {
            $flags = strtolower($m[1]);
        }
        $size = 0;
        if (preg_match('/RFC822\.SIZE (\d+)/i', $reply['raw'], $m)) {
            $size = (int)$m[1];
        }

        return [
            'flags' => $flags,
            'size' => $size,
            'raw' => $raw,
            'truncated' => $size > strlen($raw),
        ];
    }

    /**
     * Appends a raw message into a folder (used to store sent mail)
     *
     * @param string $folder remote folder name
     * @param string $rawMessage full MIME source
     * @return void
     * @throws Exception when the server rejects the append
     */
    public function append($folder, $rawMessage, $flags = '\\Seen')
    {
        $tag = $this->nextTag();
        $this->write($tag . ' APPEND ' . $this->quote($folder) . ' (' . $flags . ') {' . strlen($rawMessage) . "}\r\n");

        // Wait for the continuation prompt, then stream the literal
        while (true) {
            $line = $this->readLine();
            if ($line === false) {
                throw new Exception('IMAP connection dropped during APPEND');
            }
            if ($line[0] === '+') {
                break;
            }
            if (strpos($line, $tag . ' ') === 0) {
                throw new Exception('IMAP APPEND rejected: ' . trim($line));
            }
        }

        $this->write($rawMessage . "\r\n");
        while (($line = $this->readLine()) !== false) {
            if (strpos($line, $tag . ' ') === 0) {
                if (!preg_match('/^\S+ OK/i', $line)) {
                    throw new Exception('IMAP APPEND failed: ' . trim($line));
                }

                return;
            }
        }
        throw new Exception('IMAP APPEND: no tagged reply');
    }

    /**
     * Fetches one MIME part of a message together with its part headers
     * (needed to decode the transfer encoding). Attachment content is never
     * cached — this streams on demand.
     *
     * @param int $uid
     * @param string $partPath IMAP part path (e.g. "2" or "1.3")
     * @return array|null ['headers' => string, 'body' => string]
     */
    public function uidFetchPart($uid, $partPath)
    {
        $part = preg_replace('/[^0-9.]/', '', $partPath);
        $reply = $this->command(
            'UID FETCH ' . (int)$uid . ' (BODY.PEEK[' . $part . '.MIME] BODY.PEEK[' . $part . '])'
        );
        if (!$this->isOk($reply) || count($reply['literals']) < 2) {
            return null;
        }

        // Literals arrive in the requested order (MIME headers first)
        return [
            'headers' => $reply['literals'][0],
            'body' => $reply['literals'][1],
        ];
    }

    /**
     * Moves messages into another folder — UID MOVE with a COPY + \Deleted +
     * EXPUNGE fallback for servers without the MOVE capability
     * (the source folder must be opened writable)
     *
     * @param int[] $uids
     * @param string $targetFolder remote folder name
     * @return void
     * @throws Exception when the messages cannot be copied
     */
    public function uidMove(array $uids, $targetFolder)
    {
        if (!$uids) {
            return;
        }
        $set = implode(',', array_map('intval', $uids));

        $move = $this->command('UID MOVE ' . $set . ' ' . $this->quote($targetFolder));
        if ($this->isOk($move)) {
            return;
        }

        $reply = $this->command('UID COPY ' . $set . ' ' . $this->quote($targetFolder));
        if (!$this->isOk($reply)) {
            // the server's own reason — a missing folder, quota, permissions
            throw new Exception('Cannot move messages to ' . $targetFolder . ': ' . $reply['status']);
        }
        $this->uidDelete($uids);
    }

    /**
     * Flags messages \Deleted and expunges them (the folder must be opened
     * writable). With UIDPLUS only these UIDs are expunged — a plain EXPUNGE
     * would also destroy mail another client merely flagged \Deleted
     *
     * @param int[] $uids
     * @return void
     * @throws Exception when the server refuses the flag or the expunge
     */
    public function uidDelete(array $uids)
    {
        if (!$uids) {
            return;
        }
        $set = implode(',', array_map('intval', $uids));
        $reply = $this->command('UID STORE ' . $set . ' +FLAGS.SILENT (\\Deleted)');
        if (!$this->isOk($reply)) {
            throw new Exception('Cannot delete messages: ' . $reply['status']);
        }
        $reply = $this->command($this->hasCapability('UIDPLUS') ? 'UID EXPUNGE ' . $set : 'EXPUNGE');
        if (!$this->isOk($reply)) {
            throw new Exception('Cannot expunge messages: ' . $reply['status']);
        }
    }

    /**
     * @param string $name capability name, e.g. 'UIDPLUS'
     * @return bool
     */
    private function hasCapability($name)
    {
        if ($this->capabilities === null) {
            $this->capabilities = [];
            $reply = $this->command('CAPABILITY');
            if (preg_match('/^\* CAPABILITY (.+?)\r?$/mi', $reply['raw'], $m)) {
                $this->capabilities = preg_split('/\s+/', strtoupper(trim($m[1])));
            }
        }

        return in_array(strtoupper($name), $this->capabilities, true);
    }

    /**
     * Sets the \Seen flag on messages (the folder must be opened writable)
     *
     * @param int[] $uids
     * @return void
     */
    public function uidStoreSeen(array $uids)
    {
        $this->uidStoreFlag($uids, '\\Seen');
    }

    /**
     * Adds one flag to messages (the folder must be opened writable). A
     * keyword the server does not keep ($Forwarded) is refused by it — that
     * is no error of the connection
     *
     * @param int[] $uids
     * @param string $flag \Seen | \Answered | $Forwarded
     * @param bool $remove take the flag away instead
     * @return void
     */
    public function uidStoreFlag(array $uids, $flag, $remove = false)
    {
        if (!$uids || !in_array($flag, ['\\Seen', '\\Answered', '$Forwarded'], true)) {
            return;
        }
        $this->command('UID STORE ' . implode(',', array_map('intval', $uids))
            . ($remove ? ' -' : ' +') . 'FLAGS.SILENT (' . $flag . ')');
    }

    /**
     * Sends a tagged command and reads the full response including literals
     *
     * @param string $command
     * @return array ['status' => string tagged line, 'raw' => string untagged data, 'literals' => string[]]
     * @throws Exception on a broken connection
     */
    private function command($command)
    {
        $tag = $this->nextTag();
        $this->write($tag . ' ' . $command . "\r\n");

        return $this->readResponse($tag);
    }

    /**
     * A command ending with one literal argument ("prefix {n}" + data) —
     * the data may carry any bytes (UTF-8 search terms)
     *
     * @param string $prefix command without the argument
     * @param string $literal
     * @return array see command()
     * @throws Exception on a broken connection
     */
    private function commandWithLiteral($prefix, $literal)
    {
        $tag = $this->nextTag();
        $this->write($tag . ' ' . $prefix . ' {' . strlen($literal) . "}\r\n");
        // the server confirms the literal with "+", or refuses the command
        while (true) {
            $line = $this->readLine();
            if ($line === false) {
                throw new Exception('IMAP connection dropped');
            }
            if ($line[0] === '+') {
                break;
            }
            if (strpos($line, $tag . ' ') === 0) {
                return ['status' => trim($line), 'raw' => '', 'literals' => []];
            }
        }
        $this->write($literal . "\r\n");

        return $this->readResponse($tag);
    }

    /**
     * Reads the response of a tagged command including literals
     *
     * @param string $tag
     * @return array see command()
     * @throws Exception on a broken connection
     */
    private function readResponse($tag)
    {
        $raw = '';
        $literals = [];
        while (true) {
            $line = $this->readLine();
            if ($line === false) {
                throw new Exception('IMAP connection dropped');
            }
            if (strpos($line, $tag . ' ') === 0) {
                return ['status' => trim($line), 'raw' => $raw, 'literals' => $literals];
            }
            // Literal announcement: line ends with {N}
            if (preg_match('/\{(\d+)\}\r?\n?$/', $line, $m)) {
                $raw .= $line;
                $literals[] = $this->readBytes((int)$m[1]);
                continue;
            }
            $raw .= $line;
        }
    }

    /**
     * @param array $reply from command()
     * @return bool
     */
    private function isOk(array $reply)
    {
        return (bool)preg_match('/^\S+ OK/i', $reply['status']);
    }

    /**
     * @return string
     */
    private function nextTag()
    {
        return 'A' . str_pad(++$this->tagCounter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param string $data
     * @return void
     */
    private function write($data)
    {
        if (@fwrite($this->stream, $data) === false) {
            throw new Exception('IMAP write failed');
        }
    }

    /**
     * @return string|false
     */
    private function readLine()
    {
        $line = fgets($this->stream, 16384);
        $meta = stream_get_meta_data($this->stream);
        if ($meta['timed_out']) {
            throw new Exception('IMAP read timed out');
        }

        return $line;
    }

    /**
     * Reads exactly $length bytes (IMAP literal)
     *
     * @param int $length
     * @return string
     */
    private function readBytes($length)
    {
        $data = '';
        while (strlen($data) < $length) {
            $chunk = fread($this->stream, min(16384, $length - strlen($data)));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->stream);
                if ($meta['timed_out'] || feof($this->stream)) {
                    throw new Exception('IMAP literal read failed');
                }
                continue;
            }
            $data .= $chunk;
        }

        return $data;
    }

    /**
     * @param string $value
     * @return string IMAP quoted string
     */
    private function quote($value)
    {
        // a line break inside a quoted string would start a command of its own
        if (preg_match('/[\x00\r\n]/', (string)$value)) {
            throw new Exception('Invalid characters in an IMAP argument');
        }

        return '"' . addcslashes($value, "\\\"") . '"';
    }
}
