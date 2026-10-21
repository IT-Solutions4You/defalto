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
 * Automatic linking of certain sender matches, logged into the triage
 * payload so the AI card shows what was linked without a user click
 */
class MailManager_ProAutoLink_Model
{
    /**
     * Links the sender's CRM records to the conversation automatically when
     * the match is (near-)certain, and logs every link into the AI card:
     * - Contact: exact email match (unique)
     * - Organization: exact email match, the organization of the sender's
     *   contact, company mail/web domain or domain of the organization's
     *   contacts — each accepted only when it points to exactly ONE
     *   organization. The company name from the signature is text the sender
     *   writes himself (anyone can sign as a customer) — it is only offered
     *   as a suggestion (RecordMatch::getNameSuggestion), never linked here
     * A record the user unlinked from the conversation is never linked again.
     * Best-effort — a failure never breaks the sync or the triage
     *
     * @param int $threadId
     * @return void
     */
    public static function autoLink($threadId)
    {
        try {
            $adb = PearDatabase::getInstance();
            $threadResult = $adb->pquery(
                'SELECT t.threadid, m.owner_userid
                 FROM df_mailmanager_thread t
                 INNER JOIN df_mailmanager_mailbox m ON m.mailboxid = t.mailboxid
                 WHERE t.threadid = ?',
                [$threadId]
            );
            if (!$adb->num_rows($threadResult)) {
                return;
            }
            $thread = $adb->raw_query_result_rowdata($threadResult, 0);

            // a colleague who wrote into the conversation is not who it is about
            $email = MailManager_ProRecordMatch_Model::getLatestSender($threadId);
            if ($email === '') {
                return;
            }

            $links = MailManager_ProRelate_Model::getLinks($threadId);
            $linkedModules = array_unique(array_column($links, 'module'));
            $userId = (int)$thread['owner_userid'] ?: (int)Users_Record_Model::getCurrentUserModel()->getId();
            $logged = [];

            if (!in_array('Contacts', $linkedModules, true)) {
                $match = MailManager_ProRecordMatch_Model::findUniqueByEmail('Contacts', $email);
                if ($match && !MailManager_ProRelate_Model::isUnlinked($threadId, $match['crmid'])
                    && MailManager_ProRelate_Model::link(
                        $threadId,
                        $match['crmid'],
                        $userId,
                        true,
                        MailManager_ProHistory_Model::SOURCE_AUTO
                    )) {
                    $logged[] = ['module' => 'Contacts', 'crmid' => $match['crmid'], 'label' => $match['label'], 'reason' => 'email'];
                }
            }

            if (!in_array('Accounts', $linkedModules, true)) {
                // Most certain first; a record the user unlinked from this
                // conversation is never linked again — the next rung is tried
                $ladder = [
                    'email' => function () use ($email) {
                        return MailManager_ProRecordMatch_Model::findUniqueByEmail('Accounts', $email);
                    },
                    'contact' => function () use ($email) {
                        return MailManager_ProRecordMatch_Model::findAccountOfContact($email);
                    },
                    'domain' => function () use ($email) {
                        return MailManager_ProRecordMatch_Model::findAccountByDomain($email);
                    },
                    'contacts' => function () use ($email) {
                        return MailManager_ProRecordMatch_Model::findAccountByContactsDomain($email);
                    },
                ];
                foreach ($ladder as $reason => $find) {
                    $match = $find();
                    if (!$match || MailManager_ProRelate_Model::isUnlinked($threadId, $match['crmid'])) {
                        continue;
                    }
                    if (MailManager_ProRelate_Model::link(
                        $threadId,
                        $match['crmid'],
                        $userId,
                        true,
                        MailManager_ProHistory_Model::SOURCE_AUTO
                    )) {
                        $logged[] = ['module' => 'Accounts', 'crmid' => $match['crmid'], 'label' => $match['label'], 'reason' => $reason];
                    }
                    break;
                }
            }

            if ($logged) {
                self::appendAutoLinkLog($threadId, $logged);
            }
        } catch (Exception $e) {
            // auto-linking is an assist, never a blocker
        }
    }

    /**
     * Appends auto-link entries into the thread's triage payload — the AI
     * card renders them so the user always sees what was done automatically
     *
     * @param int $threadId
     * @param array $entries [['module', 'crmid', 'label', 'reason'], ...]
     * @return void
     */
    private static function appendAutoLinkLog($threadId, array $entries)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT triage_payload FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);
        if (!$adb->num_rows($result)) {
            return;
        }
        // raw read — query_result() HTML-escapes the quotes, json_decode then
        // fails and the write below would wipe the whole triage payload
        $row = $adb->raw_query_result_rowdata($result, 0);
        $stored = trim((string)$row['triage_payload']);
        $payload = $stored !== '' ? json_decode($stored, true) : [];
        if (!is_array($payload)) {
            // unreadable payload — never overwrite data we could not parse
            return;
        }
        $payload['autolink'] = array_merge($payload['autolink'] ?? [], $entries);
        $adb->pquery(
            'UPDATE df_mailmanager_thread SET triage_payload = ? WHERE threadid = ?',
            [json_encode($payload, JSON_UNESCAPED_UNICODE), $threadId]
        );
    }
}
