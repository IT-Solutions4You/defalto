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
 * The user's own scheduled mail (I12): cancel it, move it to another time or send it now
 */
class MailManager_ProScheduledMail_Action extends MailManager_ProAbstract_Action
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
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // sending now talks to the mail servers — the session must not block the UI
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $scheduledId = (int)$request->get('scheduled');

        try {
            switch ((string)$request->get('op')) {
                case 'cancel':
                    $result = ['success' => MailManager_ProScheduled_Model::cancel($scheduledId, $userId)];
                    break;
                case 'reschedule':
                    $result = MailManager_ProScheduled_Model::updateScheduledTime(
                        $scheduledId,
                        $userId,
                        (string)$request->get('scheduled_at')
                    );
                    break;
                case 'send':
                    MailManager_ProInbox_Model::useUtf8mb4();
                    $result = MailManager_ProScheduled_Model::sendNow($scheduledId, $userId);
                    break;
                case 'count':
                    $result = ['success' => true];
                    break;
                default:
                    $result = ['success' => false];
            }
        } catch (Exception $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        $result['count'] = MailManager_ProScheduled_Model::countForUser($userId);
        $response->setResult($result);
        $response->emit();
    }
}
