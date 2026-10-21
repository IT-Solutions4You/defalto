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
 * On-demand sync of one mailbox — inbox open and the Refresh button
 */
class MailManager_ProSyncNow_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // Long-running request (IMAP / AI / SMTP) — release the PHP
        // session lock so parallel UI requests do not queue behind it
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        // the open inbox is the watchdog of the background sync
        MailManager_ProSync_Model::releaseStaleCronTask();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        if (!$mailbox) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        try {
            $response->setResult(MailManager_ProSync_Model::syncNow(
                $mailbox,
                $userId,
                (int)$request->get('folder'),
                $request->get('triage') !== '0',
                '1' === $request->get('digest')
            ));
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
