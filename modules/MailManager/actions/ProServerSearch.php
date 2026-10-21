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
 * "Search on the server" below the search results — fetches the matches
 * the cache does not hold yet (unscanned folders, older mail)
 */
class MailManager_ProServerSearch_Action extends MailManager_ProAbstract_Action
{
    /** Seconds after which nothing new is started */
    private const TIME_BUDGET = 40;


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
        // IMAP searches take a while — parallel UI requests must not queue
        // behind the session lock
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        // raw read — the purifier would encode a term like "a & b"
        $term = trim((string)$request->getRaw('search'));
        if (!$mailbox || mb_strlen($term) < MailManager_ProSync_Model::SEARCH_MIN_TERM) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        try {
            // a repeated click resumes where the previous one stopped
            $result = MailManager_ProSync_Model::searchOnServer(
                $mailbox,
                $term,
                time() + self::TIME_BUDGET,
                (int)$request->get('resume_folder')
            );
            $response->setResult(['success' => true] + $result);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
