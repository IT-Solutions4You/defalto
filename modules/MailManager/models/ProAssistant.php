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
 * The AI assistant of a conversation — the user asks about the mail, the
 * customer in the CRM or what to do next, and gets answers plus proposed
 * actions (a reply draft, a task, a ticket, "done") he confirms himself.
 * Nothing is done by the assistant on its own. The chat is kept per
 * conversation and user — colleagues do not see each other's questions
 */
class MailManager_ProAssistant_Model
{
    /** Chat messages handed back to the AI as the conversation so far */
    private const HISTORY_MESSAGES = 12;

    /** Records of the customer listed per module */
    private const CRM_RECORDS = 5;

    /** Output budget — a reply draft fits, reasoning models think from it too */
    private const MAX_TOKENS = 6000;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    /**
     * @return bool the assistant is available (the AI switch of the summary)
     */
    public static function isEnabled()
    {
        return MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_SUMMARY);
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @return array of ['role', 'text', 'actions']
     */
    public static function getHistory($threadId, $userId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT role, content, actions FROM df_mailmanager_assistant
             WHERE threadid = ? AND userid = ? ORDER BY assistantid',
            [(int)$threadId, (int)$userId]
        );
        $messages = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $messages[] = [
                'role' => $row['role'],
                'text' => (string)$row['content'],
                'actions' => json_decode((string)$row['actions'], true) ?: [],
            ];
        }

        return $messages;
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @return void
     */
    public static function clear($threadId, $userId)
    {
        PearDatabase::getInstance()->pquery(
            'DELETE FROM df_mailmanager_assistant WHERE threadid = ? AND userid = ?',
            [(int)$threadId, (int)$userId]
        );
    }

    /**
     * The summary of the conversation as an answer of the chat — the cached
     * one when no new mail came (AiAssist::summarize)
     *
     * @param array $thread
     * @param int $userId
     * @param string $question what the chip said
     * @return array ['success', 'answer', 'actions', 'message']
     */
    public static function summary(array $thread, $userId, $question)
    {
        $result = MailManager_ProAiAssist_Model::summarize($thread);
        if (!$result['success']) {
            return ['success' => false, 'answer' => '', 'actions' => [], 'message' => $result['message']];
        }
        self::store((int)$thread['threadid'], $userId, self::ROLE_USER, $question, []);
        self::store((int)$thread['threadid'], $userId, self::ROLE_ASSISTANT, $result['summary'], []);

        return [
            'success' => true,
            'answer' => $result['summary'],
            'actions' => [],
            'message' => '',
            'backup' => (string)($result['backup'] ?? ''),
        ];
    }

    /**
     * Answers a question about the conversation
     *
     * @param array $thread thread row
     * @param int $userId who asks
     * @param string $question
     * @return array ['success' => bool, 'answer' => string, 'actions' => array, 'message' => string]
     */
    public static function ask(array $thread, $userId, $question)
    {
        $question = trim((string)$question);
        $failed = ['success' => false, 'answer' => '', 'actions' => [], 'message' => ''];
        if ($question === '' || !self::isEnabled()) {
            return $failed;
        }
        MailManager_ProInbox_Model::useUtf8mb4();
        $threadId = (int)$thread['threadid'];
        $links = MailManager_ProRelate_Model::getLinks($threadId, true);
        $allowed = self::getAllowedActions($thread, $links);

        $userLanguage = MailManager_ProThreadLanguage_Model::getPromptName(
            MailManager_ProTranslation_Model::getUserLanguage()
        );
        $mailLanguage = MailManager_ProThreadLanguage_Model::resolve($threadId);
        $mailLanguage = $mailLanguage !== ''
            ? MailManager_ProThreadLanguage_Model::getPromptName($mailLanguage)
            : 'the language of the customer\'s most recent message';

        $actionSpecs = [
            'draft' => '{"type": "draft", "text": "<the whole reply to the customer, plain text, in ' . $mailLanguage
                . ', paragraphs separated by blank lines, no subject line, no signature>"}',
            'task' => '{"type": "task", "name": "<short task name>", "description": "<what is to be done>"}',
            'ticket' => '{"type": "ticket", "title": "<short ticket title>", "description": "<the problem>"}',
            'done' => '{"type": "done"}',
        ];
        $offered = array_intersect_key($actionSpecs, array_flip($allowed));

        $systemPrompt = 'You are the assistant of a CRM user who reads an email conversation in the Mail Manager '
            . 'of vtiger CRM. Answer his questions in ' . $userLanguage . ' — short and to the point. Use only the '
            . 'conversation, the CRM data and the knowledge given below; when something is not there, say so '
            . 'plainly and never invent facts, prices, dates or records. '
            . ($offered
                ? 'When the user asks you to do something, or it clearly helps, PROPOSE one or more of these '
                    . 'actions — the user confirms each himself, you never claim it was done: '
                    . implode(' | ', $offered) . '. '
                : '')
            . 'Respond with a single JSON object only: {"answer": "<your answer as plain text>", "actions": [ ... ]}'
            . ' — "actions" is an empty list when you propose nothing.';

        $userPrompt = "THE CONVERSATION\n\n" . MailManager_ProAiAssist_Model::buildConversationPrompt($thread)
            . "\n\nCRM DATA\n\n" . MailManager_ProAiGuard_Helper::wrap(
                MailManager_ProAiGuard_Helper::SOURCE_CRM,
                self::buildCrmContext(self::getVisibleLinks($links))
            );
        $tail = MailManager_ProKnowledge_Model::buildPrompt($thread, $userId)['prompt']
            . self::buildChatHistory($threadId, $userId)
            . "\n\nTHE USER ASKS\n\n" . $question;
        // the whole history of the linked records ("what was the solution?") — I20
        $history = MailManager_ProRecordHistory_Model::buildPrompt($thread, 'assistant', mb_strlen($userPrompt . $tail));
        $userPrompt .= $history['prompt'] . $tail;

        if ('' !== $history['prompt']) {
            $systemPrompt .= ' The CRM records block holds the description, solution and comment history of the '
                . 'linked records — the user is a colleague, answer from it freely; only a reply text for the '
                . 'customer (a draft action) never quotes team comments or internal notes.';
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
            if (!$provider) {
                return ['message' => vtranslate('LBL_AI_DISABLED', 'MailManager')] + $failed;
            }
            $completion = MailManager_ProAiClient_Model::complete(
                $provider,
                'assistant',
                $threadId,
                $systemPrompt,
                $userPrompt,
                self::MAX_TOKENS
            );
        } catch (Exception $e) {
            return ['message' => $e->getMessage()] + $failed;
        }

        $parsed = MailManager_ProAiClient_Model::parseJsonObject($completion['text']);
        $answer = is_array($parsed)
            ? trim((string)($parsed['answer'] ?? ''))
            : trim(html_entity_decode((string)$completion['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $actions = self::buildActions(is_array($parsed) ? ($parsed['actions'] ?? []) : [], $allowed, $thread, $links);
        if ($answer === '' && !$actions) {
            return ['message' => vtranslate('LBL_AI_EMPTY_RESPONSE', 'MailManager')] + $failed;
        }

        self::store($threadId, $userId, self::ROLE_USER, $question, []);
        self::store($threadId, $userId, self::ROLE_ASSISTANT, $answer, $actions);

        return [
            'success' => true,
            'answer' => $answer,
            'actions' => $actions,
            'message' => '',
            'suspicious' => !empty($completion['suspicious']),
            'backup' => (string)($completion['backup'] ?? ''),
        ];
    }

    /**
     * What the assistant may propose in this conversation
     *
     * @param array $thread
     * @param array $links Relate::getLinks
     * @return string[]
     */
    private static function getAllowedActions(array $thread, array $links)
    {
        $modules = array_column($links, 'module');
        $allowed = ['draft'];
        if (in_array('Project', $modules, true) && vtlib_isModuleActive('ProjectTask')
            && Users_Privileges_Model::isPermitted('ProjectTask', 'CreateView')) {
            $allowed[] = 'task';
        }
        if (!in_array('HelpDesk', $modules, true) && vtlib_isModuleActive('HelpDesk')
            && Users_Privileges_Model::isPermitted('HelpDesk', 'CreateView')) {
            $allowed[] = 'ticket';
        }
        if (MailManager_ProThreadState_Model::hasOwnState((int)$thread['threadid'])) {
            $allowed[] = 'done';
        }

        return $allowed;
    }

    /**
     * The proposed actions the page can carry out — the quick-create values
     * prepared here, the relations of the conversation filled in
     *
     * @param mixed $proposed parsed "actions"
     * @param string[] $allowed
     * @param array $thread
     * @param array $links
     * @return array of ['type', 'html'|'module'+'params'+'labels']
     */
    private static function buildActions($proposed, array $allowed, array $thread, array $links)
    {
        $linked = [];
        foreach ($links as $link) {
            $linked[$link['module']] = $linked[$link['module']] ?? (int)$link['crmid'];
        }
        $actions = [];
        foreach (is_array($proposed) ? $proposed : [] as $action) {
            $type = is_array($action) ? (string)($action['type'] ?? '') : '';
            if (!in_array($type, $allowed, true) || isset($actions[$type])) {
                continue;
            }
            if ($type === 'draft') {
                $text = trim((string)($action['text'] ?? ''));
                if ($text !== '') {
                    $actions[$type] = ['type' => 'draft', 'html' => MailManager_ProAiAssist_Model::textToHtml($text)];
                }
            } elseif ($type === 'task') {
                $params = array_filter([
                    'projecttaskname' => mb_substr(trim((string)($action['name'] ?? '')), 0, 200) ?: (string)$thread['subject'],
                    'description' => trim((string)($action['description'] ?? '')),
                    'projectid' => $linked['Project'] ?? null,
                ]);
                $actions[$type] = ['type' => 'task', 'module' => 'ProjectTask', 'params' => $params,
                    'labels' => MailManager_ProCreatePrefill_Model::getFieldLabels('ProjectTask', $params)];
            } elseif ($type === 'ticket') {
                $params = array_filter([
                    'ticket_title' => mb_substr(trim((string)($action['title'] ?? '')), 0, 200) ?: (string)$thread['subject'],
                    'description' => trim((string)($action['description'] ?? '')),
                    'parent_id' => $linked['Accounts'] ?? null,
                    'contact_id' => $linked['Contacts'] ?? null,
                ]);
                $actions[$type] = ['type' => 'ticket', 'module' => 'HelpDesk', 'params' => $params,
                    'labels' => MailManager_ProCreatePrefill_Model::getFieldLabels('HelpDesk', $params)];
            } elseif ($type === 'done') {
                $actions[$type] = ['type' => 'done'];
            }
        }

        return array_values($actions);
    }

    /**
     * The links whose records the user may see in their list (I16) — only those reach
     * the prompt; the proposed actions still use all links of the conversation
     *
     * @param array $links Relate::getLinks
     *
     * @return array
     */
    private static function getVisibleLinks(array $links): array
    {
        $byModule = [];

        foreach ($links as $link) {
            $byModule[$link['module']][] = (int)$link['crmid'];
        }

        $visible = [];

        foreach ($byModule as $module => $recordIds) {
            foreach (MailManager_ProAiAccess_Model::getVisibleIds($module, $recordIds) as $recordId) {
                $visible[$recordId] = true;
            }
        }

        return array_values(array_filter($links, function ($link) use ($visible) {
            return isset($visible[(int)$link['crmid']]);
        }));
    }

    /**
     * What the CRM knows of the conversation — its linked records and, for
     * the organization, its latest tickets, opportunities and projects
     *
     * @param array $links Relate::getLinks (visible ones)
     * @return string
     */
    private static function buildCrmContext(array $links)
    {
        if (!$links) {
            return 'No CRM record is linked to this conversation.';
        }
        $lines = [];
        foreach ($links as $link) {
            $lines[] = '- ' . vtranslate('SINGLE_' . $link['module'], $link['module']) . ': ' . $link['label']
                . ' (' . MailManager_ProRelate_Model::buildRecordSummary((int)$link['crmid'], $link['module']) . ')';
        }
        foreach ($links as $link) {
            if ($link['module'] === 'Accounts') {
                $lines = array_merge($lines, self::getAccountRecords((int)$link['crmid']));
                break;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param int $accountId
     * @return string[] lines of the organization's latest records
     */
    private static function getAccountRecords($accountId)
    {
        $adb = PearDatabase::getInstance();
        $queries = [
            'HelpDesk' => 'SELECT tt.ticketid AS id, tt.ticket_title AS name, tt.ticketstatus AS state, ce.modifiedtime
                FROM vtiger_troubletickets tt INNER JOIN vtiger_crmentity ce ON ce.crmid = tt.ticketid AND ce.deleted = 0
                WHERE tt.parent_id = ? ORDER BY ce.modifiedtime DESC LIMIT ' . self::CRM_RECORDS,
            'Potentials' => 'SELECT po.potentialid AS id, po.potentialname AS name, po.sales_stage AS state, ce.modifiedtime
                FROM vtiger_potential po INNER JOIN vtiger_crmentity ce ON ce.crmid = po.potentialid AND ce.deleted = 0
                WHERE po.related_to = ? ORDER BY ce.modifiedtime DESC LIMIT ' . self::CRM_RECORDS,
            'Project' => 'SELECT pr.projectid AS id, pr.projectname AS name, pr.projectstatus AS state, ce.modifiedtime
                FROM vtiger_project pr INNER JOIN vtiger_crmentity ce ON ce.crmid = pr.projectid AND ce.deleted = 0
                WHERE pr.account_id = ? ORDER BY ce.modifiedtime DESC LIMIT ' . self::CRM_RECORDS,
        ];
        $lines = [];
        foreach ($queries as $module => $sql) {
            if (!vtlib_isModuleActive($module) || !Users_Privileges_Model::isPermitted($module, 'DetailView')) {
                continue;
            }
            $result = $adb->pquery($sql, [$accountId]);
            $rows = [];
            while ($row = $adb->fetchByAssoc($result, -1, false)) {
                $rows[(int)$row['id']] = $row;
            }
            // the records of the user's own list only (isPermitted() alone lets private ones through)
            foreach (MailManager_ProAiAccess_Model::getVisibleIds($module, array_keys($rows)) as $recordId) {
                $row = $rows[$recordId];
                $lines[] = '- the organization\'s ' . vtranslate('SINGLE_' . $module, $module) . ': '
                    . decode_html((string)$row['name']) . ' — ' . decode_html((string)$row['state'])
                    . ' (changed ' . substr((string)$row['modifiedtime'], 0, 10) . ')';
            }
        }

        return $lines;
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @return string the chat so far, '' when it starts now
     */
    private static function buildChatHistory($threadId, $userId)
    {
        $history = array_slice(self::getHistory($threadId, $userId), -self::HISTORY_MESSAGES);
        if (!$history) {
            return '';
        }
        $lines = [];
        foreach ($history as $message) {
            $lines[] = ($message['role'] === self::ROLE_USER ? 'User: ' : 'Assistant: ') . mb_substr($message['text'], 0, 1500);
        }

        return "\n\nTHE CHAT SO FAR\n\n" . implode("\n", $lines);
    }

    /**
     * @param int $threadId
     * @param int $userId
     * @param string $role
     * @param string $text
     * @param array $actions
     * @return void
     */
    private static function store($threadId, $userId, $role, $text, array $actions)
    {
        PearDatabase::getInstance()->pquery(
            'INSERT INTO df_mailmanager_assistant (threadid, userid, role, content, actions, createdtime)
             VALUES (?,?,?,?,?,?)',
            [
                (int)$threadId, (int)$userId, $role, (string)$text,
                $actions ? json_encode($actions, JSON_UNESCAPED_UNICODE) : null, date('Y-m-d H:i:s'),
            ]
        );
    }
}
