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
 * Data of the AI suggestion card in the conversation — derived live from
 * the current label configuration and the records linked to the thread
 */
class MailManager_ProTriageBanner_Model
{
    /**
     * Data for the conversation triage banner (approved mockup screen 08),
     * null when there is nothing to show.
     *
     * The suggestion is derived LIVE from the current label configuration —
     * the AI only classifies; changing a label's Suggested Action therefore
     * applies to already classified threads as well. Only the resolution
     * states (dismissed / accepted / created) are remembered on the thread
     *
     * @param array $thread thread row
     * @param array $mailbox mailbox row
     * @return array|null
     */
    public static function getBannerData(array $thread, array $mailbox)
    {
        // Only an explicit dismiss silences the card — 'accepted' (a manual
        // link) must NOT: the chain keeps proposing the next missing step
        $state = (string)$thread['triage_state'];
        if ($state === 'dismissed') {
            return null;
        }
        $payload = json_decode((string)$thread['triage_payload'], true) ?: [];
        $adb = PearDatabase::getInstance();

        // The label only ADDS to the chain (a case record or a cleanup
        // action) — the Organization → Contact base applies to every mail
        // that passed the spam checks, whatever the label says
        $suggested = '';
        if (!empty($thread['labelid'])) {
            // the owner of a personal mailbox may have set the label up for himself
            $label = Settings_MailManager_Label_Model::getEffective(
                (int)$thread['labelid'],
                (int)$mailbox['mailboxid']
            );
            if ($label && (int)$label['active'] === 1) {
                $suggested = (string)$label['suggested_module'];
            }
        }
        // A disabled module never gets suggested (Module Manager state)
        if ($suggested !== '' && strpos($suggested, 'action:') !== 0 && !vtlib_isModuleActive($suggested)) {
            $suggested = '';
        }

        // Always the CURRENT sender — the triage payload snapshot goes stale
        // when someone else joins the conversation later
        $threadId = (int)$thread['threadid'];
        $fromEmail = MailManager_ProRecordMatch_Model::getLatestSender($threadId);
        if ($fromEmail === '') {
            // outbound-only conversation, or only colleagues wrote — nobody
            // to build the chain for
            return null;
        }

        // The customer confirmed the fix — closing the linked ticket outranks
        // any further chain step (the Solution summary is generated with full
        // conversation context when the user clicks the button)
        if (!empty($payload['case_resolved'])) {
            $ticket = MailManager_ProRelate_Model::findOpenLinkedTicket($threadId);
            if ($ticket) {
                return [
                    'state' => 'suggested',
                    'action' => 'close_ticket',
                    'module' => 'HelpDesk',
                    'module_label' => vtranslate('SINGLE_HelpDesk', 'HelpDesk'),
                    'known_sender' => true,
                    'from_email' => $fromEmail,
                    'crmid' => (int)$ticket['crmid'],
                    'record_label' => (string)$ticket['label'],
                    'prefill_json' => '',
                ];
            }
        }

        // Cleanup suggestion (Move to Spam / Delete)
        if (strpos($suggested, 'action:') === 0) {
            return [
                'state' => 'suggested',
                'action' => substr($suggested, strlen('action:')),
                'module' => '',
                'module_label' => '',
                'known_sender' => false,
                'from_email' => $fromEmail,
                'crmid' => 0,
                'record_label' => '',
                'prefill_json' => '',
            ];
        }

        // Record-creation suggestion. A Lead is a single step for senders the
        // CRM does not know; a label set to the Organization or the Contact
        // asks for that one record alone; otherwise the chain Organization →
        // Contact → (label's case module, when set) offers the next missing
        // step — a step is skipped when such a record is already linked or
        // the sender matches an existing one (the rail suggests linking it)
        $links = MailManager_ProRelate_Model::getLinks($threadId);
        $linkedModules = array_unique(array_column($links, 'module'));
        // steps the user dismissed — the chain moves on to the next one
        $skipped = is_array($payload['skipped_steps'] ?? null) ? $payload['skipped_steps'] : [];

        if ($suggested === 'Leads') {
            if ($links || in_array('Leads', $skipped, true)
                || MailManager_ProRecordMatch_Model::findSenderRecord($fromEmail)) {
                return null;
            }
            $stepModule = 'Leads';
        } else {
            $stepModule = '';
            $steps = in_array($suggested, ['Accounts', 'Contacts'], true)
                ? [$suggested]
                : array_filter(['Accounts', 'Contacts', $suggested]);
            foreach ($steps as $step) {
                if (!vtlib_isModuleActive($step) || in_array($step, $skipped, true)) {
                    continue;
                }
                // The Contacts step follows the CURRENT sender — a linked
                // contact of ANOTHER participant does not cover a new sender
                if ($step === 'Contacts') {
                    if (!MailManager_ProRecordMatch_Model::senderHasRecordIn($fromEmail, 'Contacts')) {
                        $stepModule = $step;
                        break;
                    }
                    continue;
                }
                if (in_array($step, $linkedModules, true)) {
                    continue;
                }
                if ($step === 'Accounts' && MailManager_ProRecordMatch_Model::senderHasRecordIn($fromEmail, $step)) {
                    continue;
                }
                $stepModule = $step;
                break;
            }
            if ($stepModule === '') {
                // The whole chain exists for this conversation — nothing to
                // suggest; an auto-created record still gets its confirmation
                return null;
            }
        }

        $prefills = MailManager_ProCreatePrefill_Model::getCreatePrefills($thread, $mailbox['email'], $links);
        $params = $prefills[$stepModule]['params'] ?? self::buildFallbackPrefill($thread, $stepModule, $links);

        // Who/what would be created — "Create Contact: Kubánek Petr" reads
        // better than a bare module name
        $recordHint = trim(($params['firstname'] ?? '') . ' ' . ($params['lastname'] ?? ''));
        if ($recordHint === '') {
            $recordHint = (string)($params['accountname'] ?? $params['company'] ?? '');
        }

        return [
            'state' => 'suggested',
            'action' => '',
            'module' => $stepModule,
            'module_label' => vtranslate('SINGLE_' . $stepModule, $stepModule),
            'known_sender' => MailManager_ProRecordMatch_Model::findSenderRecord($fromEmail) !== null,
            'from_email' => $fromEmail,
            'crmid' => 0,
            'record_label' => '',
            'record_hint' => $recordHint,
            'prefill_json' => json_encode($params, JSON_UNESCAPED_UNICODE),
            'prefill_labels_json' => json_encode(
                MailManager_ProCreatePrefill_Model::getFieldLabels($stepModule, $params),
                JSON_UNESCAPED_UNICODE
            ),
        ];
    }

    /** Creation steps of the chain a Dismiss may skip */
    public const SKIPPABLE_STEPS = ['Accounts', 'Contacts', 'HelpDesk', 'Potentials', 'Leads'];

    /**
     * Remembers a dismissed creation step — the card then offers the next
     * step of the chain instead of going silent for the whole conversation
     *
     * @param int $threadId
     * @param string $module the skipped step
     * @return void
     */
    public static function skipStep($threadId, $module)
    {
        if (!in_array($module, self::SKIPPABLE_STEPS, true)) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT triage_payload FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);
        if (!$adb->num_rows($result)) {
            return;
        }
        // raw read — query_result() escapes the quotes and the decode would fail
        $stored = trim((string)$adb->raw_query_result_rowdata($result, 0)['triage_payload']);
        $payload = $stored !== '' ? json_decode($stored, true) : [];
        if (!is_array($payload)) {
            // unreadable payload — never overwrite data we could not parse
            return;
        }
        $skipped = is_array($payload['skipped_steps'] ?? null) ? $payload['skipped_steps'] : [];
        $payload['skipped_steps'] = array_values(array_unique(array_merge($skipped, [$module])));

        $adb->pquery(
            'UPDATE df_mailmanager_thread SET triage_payload = ? WHERE threadid = ?',
            [json_encode($payload, JSON_UNESCAPED_UNICODE), $threadId]
        );
    }

    /**
     * Minimal QuickCreate prefill for chain steps getCreatePrefills no longer
     * offers (Ticket / Opportunity without a known customer, or any step once
     * a case record is linked)
     *
     * @param array $thread
     * @param string $module
     * @param array $links linked records of the thread
     * @return array
     */
    private static function buildFallbackPrefill(array $thread, $module, array $links = [])
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email, from_name, reply_to, body_plain, body_html FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$thread['threadid']]
        );
        $body = '';
        $sender = ['from_email' => '', 'from_name' => ''];
        if ($adb->num_rows($result)) {
            $sender = $adb->raw_query_result_rowdata($result, 0);
            // a web form mails from our own address — the person is in Reply-To
            $effective = MailManager_ProRecordMatch_Model::getEffectiveSender($sender);
            if ($effective !== strtolower(trim((string)$sender['from_email']))) {
                $sender['from_email'] = $effective;
                $sender['from_name'] = '';
            }
            $body = mb_substr(MailManager_ProMailText_Model::messageText($sender['body_plain'], $sender['body_html']), 0, 1000);
        }

        if ($module === 'HelpDesk') {
            return array_filter(['ticket_title' => (string)$thread['subject'], 'description' => $body]);
        }
        if ($module === 'Potentials') {
            return array_filter(['potentialname' => (string)$thread['subject'], 'description' => $body]);
        }
        // Contact of the CURRENT sender (a later chain step once a case
        // exists — getCreatePrefills already returned empty by then)
        if ($module === 'Contacts') {
            $nameParts = preg_split('/\s+/', trim((string)$sender['from_name'])) ?: [];
            $lastName = $nameParts ? array_pop($nameParts) : '';
            if ($lastName === '') {
                $lastName = (string)strstr((string)$sender['from_email'], '@', true);
            }
            $accountId = 0;
            foreach ($links as $link) {
                if ($link['module'] === 'Accounts') {
                    $accountId = (int)$link['crmid'];
                    break;
                }
            }
            // AI-extracted signature details beat the from_name parsing
            $payload = json_decode((string)($thread['triage_payload'] ?? ''), true) ?: [];
            $entities = is_array($payload['entities'] ?? null) ? $payload['entities'] : [];

            return array_filter([
                'firstname' => trim((string)($entities['firstname'] ?? '')) ?: implode(' ', $nameParts),
                'lastname' => trim((string)($entities['lastname'] ?? '')) ?: $lastName,
                'email' => (string)$sender['from_email'],
                'phone' => trim((string)($entities['phone'] ?? '')) ?: null,
                'title' => trim((string)($entities['position'] ?? '')) ?: null,
                'account_id' => $accountId ?: null,
            ]);
        }

        return [];
    }
}
