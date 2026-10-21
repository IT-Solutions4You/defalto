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
 * New mail of the current user's Workspace for the browser notifications —
 * asked by the global header script once a minute on any page of the CRM.
 * Read only, straight from the cache; without the Pro edition the request
 * fails and the header script stops asking.
 */
class MailManager_ProCheckNewMail_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setResult(MailManager_ProNotifier_Model::getNews(
            (int)Users_Record_Model::getCurrentUserModel()->getId(),
            (int)$request->get('after'),
            (bool)$request->get('count')
        ));
        $response->emit();
    }
}
