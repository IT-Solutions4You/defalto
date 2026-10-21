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
 * The AI agent settings actions (I13): the administrator of the Pro edition, a POST with
 * a CSRF token, and a JSON answer
 */
abstract class Settings_MailManager_ProAiAbstract_Action extends Settings_MailManager_ProAbstract_Action
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
     * @param array $result
     *
     * @return void
     */
    protected function emit(array $result): void
    {
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
