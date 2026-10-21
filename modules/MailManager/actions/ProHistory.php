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
 * One page of a mailbox's activity history (History.js — filter, search,
 * "Load more"). The owner reads the own mailbox, the admin any
 */
class MailManager_ProHistory_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $mailboxId = (int)$request->get('mailbox');
        if (!MailManager_ProHistory_Model::canView($mailboxId, Users_Record_Model::getCurrentUserModel())) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        $page = MailManager_ProHistory_Model::getPage(
            $mailboxId,
            (string)$request->get('search'),
            (string)$request->get('history_action'),
            (int)$request->get('offset')
        );
        $viewer = Vtiger_Viewer::getInstance();
        $viewer->assign('ENTRIES', $page['entries']);

        $response->setResult([
            'success' => true,
            'html' => $viewer->view('Pro/partials/HistoryRows.tpl', $request->getModule(), true),
            'count' => count($page['entries']),
            'has_more' => $page['has_more'],
        ]);
        $response->emit();
    }
}
