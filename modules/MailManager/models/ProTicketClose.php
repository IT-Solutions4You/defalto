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
 * Closes the open ticket linked to a conversation (the accepted AI close-ticket
 * suggestion): one AI call summarizes the problem and the fix from the FULL
 * conversation into the ticket Solution field, then the ticket is closed.
 */
class MailManager_ProTicketClose_Model
{
    /** Output budget of the solution summary — a reasoning model thinks within it too */
    private const SOLUTION_MAX_TOKENS = 3000;

    /**
     * Closes the ticket, with the AI solution summary when AI is configured.
     *
     * @param array $thread thread row
     * @param array $ticket open linked ticket (crmid, label) the user may edit
     *
     * @return array ['success' => true, 'record_label', 'solution_saved'] or
     *               ['success' => false, 'message'] when the ticket could not be saved
     */
    public static function closeTicket(array $thread, array $ticket): array
    {
        $ticketId = (int)$ticket['crmid'];
        $summary = self::getSolutionSummary($thread);

        try {
            $record = Vtiger_Record_Model::getInstanceById($ticketId, 'HelpDesk');
            $record->set('mode', 'edit');
            $record->set('ticketstatus', 'Closed');

            if ('' !== $summary) {
                $existing = trim((string)$record->get('solution'));
                $record->set('solution', '' !== $existing ? $existing . "\n\n" . $summary : $summary);
            }

            $record->save();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'record_label' => (string)$ticket['label'],
            'solution_saved' => '' !== $summary,
        ];
    }

    /**
     * Solution summary from the whole conversation — best-effort: a failed AI call
     * must not block closing the ticket.
     *
     * @param array $thread thread row
     *
     * @return string '' without AI or when the call failed
     */
    public static function getSolutionSummary(array $thread): string
    {
        $summary = '';

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();

            if ($provider) {
                $result = MailManager_ProAiClient_Model::complete(
                    $provider,
                    'solution',
                    (int)$thread['threadid'],
                    'Summarize this solved support conversation for the ticket "Solution" field: '
                    . 'state the PROBLEM and the exact FIX that worked, 2-4 sentences, in the language '
                    . 'of the conversation. Respond with the summary text only.',
                    MailManager_ProAiAssist_Model::buildConversationPrompt($thread),
                    self::SOLUTION_MAX_TOKENS
                );
                $summary = trim(html_entity_decode($result['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        } catch (Exception $e) {
            // close without a generated solution
        }

        return $summary;
    }
}
