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
 * Common transport contract. The rest of the module works exclusively with this
 * class and asks supports() before an operation — it never branches on the
 * provider name.
 *
 * Drivers: MailManager_ProImapTransport_Helper (generic IMAP, complete),
 * the only driver; Google mailboxes use it with IMAP XOAUTH2.
 * Obtain an instance via MailManager_ProTransport_Model::getForMailbox().
 *
 * A driver overrides the operations of the capabilities it reports; the base
 * implementations throw, so a caller that skipped supports() fails loudly
 * instead of silently doing nothing.
 */
class MailManager_ProBaseTransport_Helper
{
    /** Folder list, counters and incremental message sync */
    public const CAP_SYNC = 'sync';

    /** On-demand download of one attachment part */
    public const CAP_ATTACHMENTS = 'attachments';

    /** Storing a message on the server (Sent copy, Drafts) */
    public const CAP_APPEND = 'append';

    /** Server-side flags, move and delete of messages */
    public const CAP_MANAGE = 'manage';

    /**
     * Mailbox row from df_mailmanager_mailbox (secrets already decrypted)
     *
     * @var array
     */
    protected $mailbox;

    /**
     * @param array $mailbox
     */
    public function __construct(array $mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * Whether this driver implements the operations of a capability
     *
     * @param string $capability one of the CAP_* constants
     * @return bool
     */
    public function supports(string $capability): bool
    {
        return false;
    }

    /**
     * Releases the server connection — safe to call on every driver, any time
     *
     * @return void
     */
    public function close()
    {
    }

    /**
     * Verifies the connection and returns basic mailbox info
     *
     * @return array ['success' => bool, 'folders' => int, 'error' => string|null]
     */
    public function testConnection(): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Lists remote folders (CAP_SYNC)
     *
     * @return array of ['remote_id' => string, 'name' => string, 'flags' => string]
     */
    public function listFolders(): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Hierarchy delimiter of the folder names ("Inbox/Projects") — the API
     * providers use "/", IMAP servers announce their own in LIST
     *
     * @return string
     */
    public function getFolderDelimiter(): string
    {
        return '/';
    }

    /**
     * Lightweight folder counters without downloading messages — badges of
     * folders that are not synced locally (CAP_SYNC)
     *
     * @param array $folder df_mailmanager_folder row
     * @return array ['messages' => int, 'unseen' => int]
     */
    public function getFolderCounts(array $folder): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * The folder as the server has it now — the read state of its messages
     * and the complete id list, so mail read, deleted or moved in another
     * client shows the same here. null = the driver does not support it yet
     * (the cache keeps its own state)
     *
     * @param array $folder df_mailmanager_folder row (flags_modseq, state_signature = previous run)
     * @return array|null ['seen' => [remote uid => bool], 'modseq' => int,
     *                     'uids' => int[]|null (null = compare nothing), 'signature' => string]
     */
    public function getFolderState(array $folder): ?array
    {
        return null;
    }

    /**
     * Incremental sync — messages changed since the stored cursor (CAP_SYNC).
     * The cursor format is driver-specific (IMAP = "uidvalidity:lastUid").
     *
     * @param array $folder df_mailmanager_folder row
     * @param string|null $cursor
     * @return array ['messages' => array[], 'cursor' => string, 'has_more' => bool,
     *                'counts' => ['messages' => int, 'unseen' => int],
     *                'reset' => bool — the server renumbered the folder, cached ids are void]
     */
    public function getChangesSince(array $folder, ?string $cursor): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * History before the cached mail — the newest messages older than the
     * given one, for "load older" beyond the mailbox sync window (CAP_SYNC)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int $beforeUid oldest cached remote id, 0 = nothing cached (the newest overall)
     * @param int $limit
     * @return array ['messages' => array[] (same shape as getChangesSince), 'has_more' => bool]
     */
    public function getOlderMessages(array $folder, int $beforeUid, int $limit): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Full-text search of one folder on the server — mail the cache does
     * not hold (unscanned folders, history) (CAP_SYNC)
     *
     * @param array $folder df_mailmanager_folder row
     * @param string $term
     * @return int[] remote ids of the matches, ascending
     */
    public function searchFolder(array $folder, string $term): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Fetches the given messages of one folder (CAP_SYNC)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote ids
     * @return array unified messages (same shape as getChangesSince), the gone ones left out
     */
    public function fetchMessages(array $folder, array $uids): array
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Fetches and decodes one attachment part (CAP_ATTACHMENTS)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int $uid remote message id
     * @param string $partPath MIME part path
     * @return string|null decoded binary content, null when the part is gone
     */
    public function fetchAttachmentPart(array $folder, $uid, $partPath)
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Stores a message on the server — Sent copy, Drafts (CAP_APPEND)
     *
     * @param array $folder df_mailmanager_folder row
     * @param string $rawMessage full MIME source
     * @param string $flags server flags, e.g. '\Seen' or '\Draft \Seen'
     * @return void
     */
    public function appendMessage(array $folder, $rawMessage, $flags = '\\Seen')
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Server message ids of a message by its Message-ID header (CAP_MANAGE)
     *
     * @param array $folder df_mailmanager_folder row
     * @param string $messageId
     * @return int[] remote message ids
     */
    public function findMessageUids(array $folder, $messageId)
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Pushes the read flag to the server (CAP_MANAGE)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote message ids
     * @return void
     */
    public function markSeen(array $folder, array $uids)
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Takes the read flag away on the server (CAP_MANAGE)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote message ids
     * @return void
     */
    public function markUnseen(array $folder, array $uids)
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Marks messages as answered / forwarded on the server (CAP_MANAGE)
     *
     * @param array $folder df_mailmanager_folder row
     * @param int[] $uids remote message ids
     * @param bool $forwarded true = forwarded, false = answered
     * @return void
     */
    public function markAnswered(array $folder, array $uids, $forwarded = false)
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Moves messages into another folder (CAP_MANAGE)
     *
     * @param array $folder source df_mailmanager_folder row
     * @param int[] $uids remote message ids
     * @param string $targetRemoteId
     * @return void
     */
    public function moveMessages(array $folder, array $uids, string $targetRemoteId): void
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * Deletes messages — into Trash when there is one, otherwise for good (CAP_MANAGE)
     *
     * @param array $folder source df_mailmanager_folder row
     * @param int[] $uids remote message ids
     * @param string $trashRemoteId '' = no Trash folder
     * @return void
     */
    public function deleteMessages(array $folder, array $uids, $trashRemoteId = '')
    {
        throw $this->notImplemented(__FUNCTION__);
    }

    /**
     * @param string $methodName
     * @return Exception
     */
    protected function notImplemented(string $methodName): Exception
    {
        return new Exception(get_class($this) . '::' . $methodName . ' is not implemented yet');
    }
}
