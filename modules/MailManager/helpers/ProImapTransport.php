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
 * Generic IMAP driver — socket client, no ext-imap. Incremental sync via
 * UIDVALIDITY + last UID cursor; message source is fetched with a size cap
 * and parsed locally (attachment content is never downloaded).
 */
class MailManager_ProImapTransport_Helper extends MailManager_ProBaseTransport_Helper
{
    /** Max messages fetched per getChangesSince() call */
    private const BATCH_SIZE = 25;

    /** Partial fetch cap for one message source */
    private const MAX_FETCH_BYTES = 524288;

    /** A message longer than the cap is fetched whole up to this size (attachments behind the cap) */
    private const MAX_FULL_BYTES = 15728640;

    /** Runs a message the server refuses to deliver is retried before it is skipped */
    private const MAX_FETCH_ATTEMPTS = 3;

    /** @var MailManager_ProImapClient_Helper|null */
    private $client;

    /** @var bool a LIST ran on this connection (the client knows the delimiter) */
    private $listed = false;

    /**
     * @return MailManager_ProImapClient_Helper connected client (lazy)
     */
    private function getClient()
    {
        if (!$this->client) {
            $client = new MailManager_ProImapClient_Helper(
                $this->mailbox['imap_server'],
                $this->mailbox['imap_port'],
                $this->mailbox['imap_encryption'],
                (string)($this->mailbox['imap_cert_fingerprint'] ?? '')
            );

            // the client is kept only after a successful login, so a failed login is
            // reported again instead of reusing a half-open connection
            try {
                if (MailManager_ProTransport_Model::isOAuthProvider((string)($this->mailbox['provider'] ?? ''))) {
                    $client->connectXOAuth2($this->mailbox['imap_username'], (string)($this->mailbox['oauth_access_token'] ?? ''));
                } else {
                    $client->connect($this->mailbox['imap_username'], $this->mailbox['imap_password']);
                }
            } catch (Exception $e) {
                $client->disconnect();

                throw $e;
            }

            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * The IMAP driver implements every capability of the contract
     *
     * @param string $capability one of the CAP_* constants
     * @return bool
     */
    public function supports(string $capability): bool
    {
        return in_array(
            $capability,
            [self::CAP_SYNC, self::CAP_ATTACHMENTS, self::CAP_APPEND, self::CAP_MANAGE],
            true
        );
    }

    /**
     * @return void
     */
    public function close()
    {
        if ($this->client) {
            $this->client->disconnect();
            $this->client = null;
            $this->listed = false;
        }
    }

    /**
     * @return array ['success' => bool, 'folders' => int, 'error' => string|null]
     */
    public function testConnection(): array
    {
        try {
            $folders = $this->getClient()->listFolders();

            return ['success' => true, 'folders' => count($folders), 'error' => null];
        } catch (Exception $e) {
            return ['success' => false, 'folders' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array unified folder arrays
     */
    public function listFolders(): array
    {
        $folders = $this->getClient()->listFolders();
        $this->listed = true;

        return $folders;
    }

    /**
     * @return string the delimiter the server announces in LIST (a LIST of
     *                this connection already carried it)
     */
    public function getFolderDelimiter(): string
    {
        $client = $this->getClient();
        if (!$this->listed) {
            $this->listFolders();
        }

        return $client->getDelimiter();
    }

    /**
     * @param array $folder df_mailmanager_folder row
     * @return array ['messages' => int, 'unseen' => int]
     */
    public function getFolderCounts(array $folder): array
    {
        return $this->getClient()->status($folder['remote_id']);
    }

    /**
     * Stores a message on the server (Sent copy, Drafts)
     *
     * @param array $folder df_mailmanager_folder row
     * @param string $rawMessage full MIME source
     * @param string $flags IMAP flags, e.g. '\Seen' or '\Draft \Seen'
     * @return void
     */
    public function appendMessage(array $folder, $rawMessage, $flags = '\\Seen')
    {
        $this->getClient()->append($folder['remote_id'], $rawMessage, $flags);
    }

    /**
     * Fetches and decodes one attachment part on demand (lazy — no caching)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int $uid remote message UID
     * @param string $partPath MIME part path
     * @return string|null decoded binary content
     */
    public function fetchAttachmentPart(array $folder, $uid, $partPath)
    {
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, true);

        $part = $client->uidFetchPart($uid, $partPath);
        if ($part === null) {
            return null;
        }

        $encoding = '';
        if (preg_match('/content-transfer-encoding:\s*([\w-]+)/i', $part['headers'], $match)) {
            $encoding = strtolower($match[1]);
        }
        if ($encoding === 'base64') {
            return base64_decode($part['body'], false) ?: '';
        }
        if ($encoding === 'quoted-printable') {
            return quoted_printable_decode($part['body']);
        }

        return $part['body'];
    }

    /**
     * Deletes messages on the server — moved to Trash when available,
     * hard-deleted when already in Trash or no Trash folder exists
     *
     * @param array $folder source df_mailmanager_folder row
     * @param int[] $uids
     * @param string $trashRemoteId '' = no Trash folder
     * @return void
     */
    public function deleteMessages(array $folder, array $uids, $trashRemoteId = '')
    {
        if (!$uids) {
            return;
        }
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, false);

        if ($trashRemoteId !== '' && $trashRemoteId !== $folder['remote_id']) {
            $client->uidMove($uids, $trashRemoteId);
        } else {
            $client->uidDelete($uids);
        }
    }

    /**
     * Bulk move of messages into another folder (triage cleanup — Move to Spam)
     *
     * @param array $folder source df_mailmanager_folder row
     * @param int[] $uids
     * @param string $targetRemoteId
     * @return void
     */
    public function moveMessages(array $folder, array $uids, string $targetRemoteId): void
    {
        if (!$uids || $targetRemoteId === $folder['remote_id']) {
            return;
        }
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, false);
        $client->uidMove($uids, $targetRemoteId);
    }

    /**
     * Server UIDs of a message by its Message-ID — for copies stored by the
     * CRM itself, whose UID the cache never learned (APPEND returns none)
     *
     * @param array $folder df_mailmanager_folder row
     * @param string $messageId
     * @return int[]
     */
    public function findMessageUids(array $folder, $messageId)
    {
        if ((string)$messageId === '') {
            return [];
        }
        $client = $this->getClient();
        $client->select($folder['remote_id']);

        return $client->uidSearchHeader('Message-ID', $messageId);
    }

    /**
     * Pushes the \Seen flag to the server for the given messages
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote UIDs
     * @return void
     */
    public function markSeen(array $folder, array $uids)
    {
        if (!$uids) {
            return;
        }
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, false);
        $client->uidStoreSeen($uids);
    }

    /**
     * Takes the \Seen flag away — the message is unread again in every client
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote UIDs
     * @return void
     */
    public function markUnseen(array $folder, array $uids)
    {
        if (!$uids) {
            return;
        }
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, false);
        $client->uidStoreFlag($uids, '\\Seen', true);
    }

    /**
     * Marks messages as answered (\Answered) or forwarded ($Forwarded) on the
     * server — the other mail clients of the mailbox show the reply arrow
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote UIDs
     * @param bool $forwarded true = forwarded, false = answered
     * @return void
     */
    public function markAnswered(array $folder, array $uids, $forwarded = false)
    {
        if (!$uids) {
            return;
        }
        $client = $this->getClient();
        $this->selectUnchanged($client, $folder, false);
        $client->uidStoreFlag($uids, $forwarded ? '$Forwarded' : '\\Answered');
    }

    /**
     * @param array $folder df_mailmanager_folder row
     * @return array|null ['seen' => [uid => bool], 'modseq' => int, 'uids' => int[]|null, 'signature' => string]
     */
    public function getFolderState(array $folder): ?array
    {
        return $this->getClient()->fetchFolderState(
            $folder['remote_id'],
            (int)($folder['flags_modseq'] ?? 0),
            (string)($folder['state_signature'] ?? '')
        );
    }

    /**
     * Incremental folder sync. Cursor format: "uidvalidity:lastUid" plus
     * ":retryUid:attempts" while a message the server refused is retried.
     *
     * @param array $folder df_mailmanager_folder row
     * @param string|null $cursor
     * @return array ['messages' => array[], 'cursor' => string, 'has_more' => bool,
     *                'counts' => ['messages' => int, 'unseen' => int], 'reset' => bool]
     */
    public function getChangesSince(array $folder, ?string $cursor): array
    {
        $client = $this->getClient();
        $state = $client->select($folder['remote_id']);

        [$cursorValidity, $lastUid, $retryUid, $retryCount] = array_pad(explode(':', (string)$cursor, 4), 4, 0);
        $cursorValidity = (int)$cursorValidity;
        $lastUid = (int)$lastUid;
        $retryUid = (int)$retryUid;
        $retryCount = (int)$retryCount;

        // UIDVALIDITY change invalidates all stored UIDs — restart the folder;
        // the caller must forget the cached UIDs of this folder
        $reset = $cursorValidity !== 0 && (int)$state['uidvalidity'] > 0 && $cursorValidity !== (int)$state['uidvalidity'];
        if ($reset) {
            $lastUid = 0;
            $retryUid = 0;
            $retryCount = 0;
        }

        if ($lastUid > 0) {
            $uids = array_values(array_filter(
                $client->uidSearch('UID ' . ($lastUid + 1) . ':*'),
                function ($uid) use ($lastUid) {
                    return $uid > $lastUid;
                }
            ));
        } else {
            $syncFrom = $this->mailbox['sync_from'] ?? null;
            $criteria = $syncFrom ? 'SINCE ' . date('d-M-Y', strtotime($syncFrom)) : 'ALL';
            $uids = $client->uidSearch($criteria);
        }

        $batch = array_slice($uids, 0, self::BATCH_SIZE);
        $messages = [];
        $processedUid = $lastUid;
        $stopped = false;

        foreach ($batch as $uid) {
            $fetched = $this->fetchMessage($client, $uid);
            if ($fetched === false) {
                // The server refused this message (e.g. [UNAVAILABLE]) — the
                // cursor stays before it and the next run retries; after
                // MAX_FETCH_ATTEMPTS it is given up, one broken message must
                // not block the rest of the folder
                $attempts = $retryUid === $uid ? $retryCount + 1 : 1;
                if ($attempts < self::MAX_FETCH_ATTEMPTS) {
                    $retryUid = $uid;
                    $retryCount = $attempts;
                    $stopped = true;
                    break;
                }
                $retryUid = 0;
                $retryCount = 0;
                $processedUid = max($processedUid, $uid);
                continue;
            }
            $processedUid = max($processedUid, $uid);
            if ($retryUid === $uid) {
                $retryUid = 0;
                $retryCount = 0;
            }
            if ($fetched === null) {
                // expunged between SEARCH and FETCH — nothing to import
                continue;
            }

            $messages[] = self::parseFetched($fetched, $uid);
        }

        return [
            'messages' => $messages,
            'cursor' => $state['uidvalidity'] . ':' . $processedUid
                . ($retryUid ? ':' . $retryUid . ':' . $retryCount : ''),
            'reset' => $reset,
            'has_more' => !$stopped && count($uids) > count($batch),
            // the batch ended on a message the server refused — the folder was NOT read to its end
            'stopped' => $stopped,
            'counts' => $client->status($folder['remote_id']),
        ];
    }

    /**
     * The newest messages below the oldest cached UID — history beyond the
     * sync window, newest first. A message the server refuses is skipped
     * (the next click continues below it)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int $beforeUid oldest cached UID, 0 = nothing cached
     * @param int $limit
     * @return array ['messages' => array[], 'has_more' => bool]
     */
    public function getOlderMessages(array $folder, int $beforeUid, int $limit): array
    {
        $client = $this->getClient();
        $state = $client->select($folder['remote_id']);
        if ($state['exists'] === 0 || $beforeUid === 1) {
            return ['messages' => [], 'has_more' => false];
        }

        $uids = array_values(array_filter(
            $client->uidSearch($beforeUid > 0 ? 'UID 1:' . ($beforeUid - 1) : 'ALL'),
            function ($uid) use ($beforeUid) {
                return $beforeUid === 0 || $uid < $beforeUid;
            }
        ));
        $batch = array_reverse(array_slice($uids, -$limit));

        return ['messages' => $this->fetchMessages($folder, $batch), 'has_more' => count($uids) > count($batch)];
    }

    /**
     * @param array $folder df_mailmanager_folder row
     * @param string $term
     * @return int[] ascending UIDs
     */
    public function searchFolder(array $folder, string $term): array
    {
        $client = $this->getClient();
        $state = $client->select($folder['remote_id']);
        if ($state['exists'] === 0) {
            return [];
        }

        return $client->uidSearchText($term);
    }

    /**
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids
     * @return array unified messages; refused / expunged ones left out
     */
    public function fetchMessages(array $folder, array $uids): array
    {
        $client = $this->getClient();
        $client->select($folder['remote_id']);

        $messages = [];
        foreach ($uids as $uid) {
            $fetched = $this->fetchMessage($client, (int)$uid);
            if ($fetched) {
                $messages[] = self::parseFetched($fetched, (int)$uid);
            }
        }

        return $messages;
    }

    /**
     * One message for the cache. The first fetch is capped (most mail fits);
     * a longer one of a sane size is fetched whole — otherwise the attachments
     * and text parts lying behind the cap would never be listed
     *
     * @param MailManager_ProImapClient_Helper $client
     * @param int $uid
     * @return array|null|false as uidFetchRaw()
     */
    private function fetchMessage($client, $uid)
    {
        $fetched = $client->uidFetchRaw($uid, self::MAX_FETCH_BYTES);
        if (is_array($fetched) && !empty($fetched['truncated']) && $fetched['size'] <= self::MAX_FULL_BYTES) {
            $whole = $client->uidFetchRaw($uid, $fetched['size'] + 1024);
            if (is_array($whole)) {
                return $whole;
            }
        }

        return $fetched;
    }

    /**
     * Opens a folder for an action on cached UIDs. The UIDs are only good
     * while the folder keeps the UIDVALIDITY the cache read them under — a
     * renumbered folder would hand the action somebody else's messages
     * (delete, move, mark read, attachment). The next sync re-reads the folder
     *
     * @param MailManager_ProImapClient_Helper $client
     * @param array $folder df_mailmanager_folder row
     * @param bool $readOnly
     * @return void
     * @throws Exception when the folder was renumbered on the server
     */
    private function selectUnchanged($client, array $folder, $readOnly)
    {
        $state = $client->select($folder['remote_id'], $readOnly);
        $known = (int)strtok((string)($folder['delta_cursor'] ?? ''), ':');

        if ($known > 0 && (int)$state['uidvalidity'] > 0 && $known !== (int)$state['uidvalidity']) {
            throw new Exception(vtranslate('LBL_MOVE_PENDING_SYNC', 'MailManager'));
        }
    }

    /**
     * @param array $fetched uidFetchRaw result (raw, flags, size)
     * @param int $uid
     * @return array unified message
     */
    private static function parseFetched(array $fetched, $uid)
    {
        $parsed = MailManager_ProMimeParser_Helper::parse($fetched['raw']);
        $parsed['remote_uid'] = $uid;
        $parsed['is_read'] = strpos($fetched['flags'], '\\seen') !== false ? 1 : 0;
        $parsed['is_draft'] = strpos($fetched['flags'], '\\draft') !== false ? 1 : 0;
        $parsed['size_bytes'] = $fetched['size'];

        return $parsed;
    }
}
