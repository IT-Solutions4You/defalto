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
 * The sidebar numbers — unread badges of the folders and the Workspace,
 * conversation counts of the label and priority filters — repainted after
 * anything that changes them (read, delete, move, label, priority, new
 * mail) without a page reload. Read only, straight from the cache
 */
class MailManager_ProSidebarCounts_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        if (!$mailbox) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }
        $response->setResult(
            ['success' => true] + MailManager_ProThreadList_Model::getSidebarCounts((int)$mailbox['mailboxid'], $userId)
        );
        $response->emit();
    }
}
