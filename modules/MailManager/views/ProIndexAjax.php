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
 * Pro inbox AJAX partials – thread list, conversation, CRM panel, composer and
 * My Settings inside the inbox.
 */
class MailManager_ProIndexAjax_View extends MailManager_ProAbstract_View
{
    /** Thread list tabs accepted from the request */
    protected const LIST_FILTERS = MailManager_ProThreadList_Model::LIST_FILTERS;

    /** Composer modes accepted from the request */
    protected const COMPOSE_MODES = ['reply', 'replyall', 'forward', 'new', 'draft'];

    /**
     * Registers the partials served by this view.
     */
    public function __construct()
    {
        parent::__construct();

        $this->exposeMethod('threadList');
        $this->exposeMethod('conversation');
        $this->exposeMethod('crmPanel');
        $this->exposeMethod('composer');
        $this->exposeMethod('mySettings');
        $this->exposeMethod('scheduled');
        $this->exposeMethod('workspaceDigest');
    }

    /**
     * Partials have no page header.
     *
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * Partials have no page footer.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * Dispatches the requested partial.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProInbox_Model::useUtf8mb4();

        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * Returns the mailbox of the request when the current user may access it.
     *
     * @param Vtiger_Request $request
     *
     * @return array mailbox row
     * @throws Exception when the mailbox is not accessible
     */
    protected function getRequestMailbox(Vtiger_Request $request): array
    {
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);

        if (!$mailbox) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return $mailbox;
    }

    /**
     * Returns the thread of the request inside the given mailbox.
     *
     * @param Vtiger_Request $request
     * @param array          $mailbox accessible mailbox row
     *
     * @return array thread row
     * @throws Exception when the thread does not exist in the mailbox
     */
    protected function getRequestThread(Vtiger_Request $request, array $mailbox): array
    {
        $thread = MailManager_ProInbox_Model::getThread((int)$request->get('thread'), (int)$mailbox['mailboxid']);

        if (!$thread) {
            throw new Exception(vtranslate('LBL_RECORD_NOT_FOUND', 'Vtiger'));
        }

        return $thread;
    }

    /**
     * My Settings inside the inbox – the page template rendered in the main area next
     * to the sidebar.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function mySettings(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $moduleName)
            ->assign('EMBEDDED', true);

        foreach (MailManager_ProMySettings_Model::getViewData($userId, (int)$request->get('mailbox')) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/MySettings.tpl', $moduleName);
    }

    /**
     * Thread list of one folder, the Workspace, a search or a sidebar filter (paginated).
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function threadList(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailbox = $this->getRequestMailbox($request);
        $filter = (string)$request->get('filter');

        if (!in_array($filter, self::LIST_FILTERS, true)) {
            $filter = 'all';
        }

        $priority = (string)$request->get('priority');

        if (!in_array($priority, MailManager_ProAiTriage_Model::PRIORITIES, true)) {
            $priority = '';
        }

        $page = max(1, (int)$request->get('page'));
        // "Load more" continues after the rows the list really shows – rows deleted /
        // moved meanwhile shifted the pages
        $offset = $request->has('offset') ? max(0, (int)$request->get('offset')) : null;
        // the last row the list shows – a valid datetime and an id, nothing else
        $beforeAt = (string)$request->get('before_at');

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $beforeAt)) {
            $offset = [$beforeAt, (int)$request->get('before_id')];
        }

        // raw – a LIKE parameter; the purifier would turn "R&D" into "R&amp;D"
        $search = (string)$request->getRaw('search');
        $labelId = (int)$request->get('label');
        $workspace = '1' === (string)$request->get('workspace');
        $folderId = (int)$request->get('folder');
        $data = MailManager_ProThreadList_Model::getThreads(
            (int)$mailbox['mailboxid'],
            $folderId,
            $page,
            $filter,
            $userId,
            $search,
            $labelId,
            $priority,
            $workspace,
            $offset
        );

        $this->getViewer($request)
            ->assign('MODULE', $moduleName)
            // browsing Spam / Trash – the rows offer "Not spam" / "Restore" instead of "Move to Spam"
            ->assign('JUNK_TYPE', MailManager_ProThreadList_Model::getListJunkType($folderId, $search, $labelId, $priority, $workspace))
            ->assign('THREADS', $data['threads'])
            ->assign('HAS_MORE', $data['has_more'])
            ->assign('CAN_LOAD_OLDER', $data['can_load_older'])
            ->assign('CAN_SEARCH_SERVER', $data['can_search_server'])
            ->assign('PAGE', $page)
            ->view('Pro/ThreadList.tpl', $moduleName);
    }

    /**
     * Conversation timeline of one thread. Read-marking (local store + server \Seen)
     * runs as a follow-up ProMarkThreadRead action so the conversation opens without
     * IMAP latency.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function conversation(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $mailbox = $this->getRequestMailbox($request);
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $moduleName);

        foreach (MailManager_ProConversation_Model::getViewData($this->getRequestThread($request, $mailbox), $mailbox) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/Conversation.tpl', $moduleName);
    }

    /**
     * CRM side panel – linked records, suggestions by participant e-mails and the
     * prefilled "create" actions.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function crmPanel(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $mailbox = $this->getRequestMailbox($request);
        $thread = $this->getRequestThread($request, $mailbox);
        // certain sender matches link their records on open too – covers conversations
        // stored before the auto-link feature existed
        MailManager_ProAutoLink_Model::autoLink((int)$thread['threadid']);
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $moduleName);

        foreach (MailManager_ProCrmPanel_Model::getViewData($thread, $mailbox) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/CrmPanel.tpl', $moduleName);
    }

    /**
     * The user's scheduled mail — the window of the sidebar "Scheduled" entry
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function scheduled(Vtiger_Request $request): void
    {
        $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('SCHEDULED', MailManager_ProScheduled_Model::getForUser((int)Users_Record_Model::getCurrentUserModel()->getId()))
            ->view('Pro/Scheduled.tpl', $request->getModule());
    }

    /**
     * The Workspace digest (I19) — the model sorts, the AI lines come from the cron.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function workspaceDigest(Vtiger_Request $request): void
    {
        $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('DIGEST', MailManager_ProWorkspaceDigest_Model::build(
                (int)Users_Record_Model::getCurrentUserModel()->getId(),
                '1' === $request->get('refresh')
            ))
            ->view('Pro/WorkspaceDigest.tpl', $request->getModule());
    }

    /**
     * Inline composer partial – reply / reply all / forward / new mail / saved draft.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function composer(Vtiger_Request $request): void
    {
        $moduleName = $request->getModule();
        $mailbox = $this->getRequestMailbox($request);
        $mode = (string)$request->get('composeMode');

        if (!in_array($mode, self::COMPOSE_MODES, true)) {
            $mode = 'new';
        }

        $viewer = $this->getViewer($request)
            ->assign('MODULE', $moduleName);
        $viewData = MailManager_ProComposer_Model::getViewData(
            $mode,
            $mailbox,
            $this->getComposerPrefill($request, $mailbox, $mode),
            (int)Users_Record_Model::getCurrentUserModel()->getId()
        );

        foreach ($viewData as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/Composer.tpl', $moduleName);
    }

    /**
     * Returns the composer prefill of the requested mode.
     *
     * @param Vtiger_Request $request
     * @param array          $mailbox accessible mailbox row
     * @param string         $mode    composer mode
     *
     * @return array
     * @throws Exception when the draft or the thread does not exist in the mailbox
     */
    protected function getComposerPrefill(Vtiger_Request $request, array $mailbox, string $mode): array
    {
        if ('draft' === $mode) {
            // continue writing a saved draft
            $prefill = MailManager_ProComposer_Model::getDraftComposerPrefill((int)$request->get('draft'), (int)$mailbox['mailboxid']);

            if (!$prefill) {
                throw new Exception(vtranslate('LBL_RECORD_NOT_FOUND', 'Vtiger'));
            }

            return $prefill;
        }

        if ('new' !== $mode) {
            return MailManager_ProComposer_Model::getComposerPrefill($this->getRequestThread($request, $mailbox), $mailbox['email'], $mode);
        }

        return MailManager_ProComposer_Model::getNewComposerPrefill();
    }
}
