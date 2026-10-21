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
 * Recipient autocomplete for the composer To field — CRM records only.
 *
 * MailManager_ProRecipientSearch_Model covers every module that has an email
 * field (vtws_listtypes discovers them dynamically), reads the indexed
 * vtiger_emailslookup table and honours record permissions. No module list
 * of our own to maintain
 */
class MailManager_ProSearchRecipients_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateReadAccess();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // A search must not queue behind a running sync / AI call
        session_write_close();
        $response = new Vtiger_Response();

        $term = trim((string)$request->get('search'));
        if (mb_strlen($term) < 2) {
            $response->setResult(['groups' => []]);
            $response->emit();

            return;
        }

        $response->setResult(['groups' => MailManager_ProRecipientSearch_Model::getRecipientGroups($term)]);
        $response->emit();
    }
}
