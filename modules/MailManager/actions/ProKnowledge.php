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
 * Knowledge files — list, upload and delete. The company files (scope
 * "general") are the admin's, the personal ones each user's own
 */
class MailManager_ProKnowledge_Action extends MailManager_ProAbstract_Action
{
    /**
     * Checks the Pro module permission; the company knowledge (scope "general") is
     * additionally the administrator's to change.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        if ($this->getScope($request) === MailManager_ProKnowledge_Model::SCOPE_GENERAL
            && !Users_Record_Model::getCurrentUserModel()->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    /**
     * Requires a valid CSRF token and a POST request (the action writes data or spends AI tokens).
     *
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
     * Handles the request and emits the JSON result.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $scope = $this->getScope($request);
        $error = '';

        if ($request->get('mode') === 'upload') {
            // one or more files of one picking; Vtiger_Request carries no uploads, so $_FILES is read here
            $error = MailManager_ProKnowledge_Model::addFiles($scope, $userId, $_FILES['file'] ?? []);
        } elseif ($request->get('mode') === 'delete') {
            if (!MailManager_ProKnowledge_Model::deleteFile($scope, $userId, (int)$request->get('document'))) {
                $error = 'failed';
            }
        }

        $response->setResult([
            'success' => $error === '',
            // a known reason is named, the rest is the page's "save failed"
            'message' => MailManager_ProKnowledge_Model::getErrorMessage($error),
            'files' => MailManager_ProKnowledge_Model::getFiles($scope, $userId),
        ]);
        $response->emit();
    }

    /**
     * Returns the knowledge scope requested.
     *
     * @param Vtiger_Request $request
     *
     * @return string general | personal
     */
    private function getScope(Vtiger_Request $request): string
    {
        return $request->get('scope') === MailManager_ProKnowledge_Model::SCOPE_GENERAL
            ? MailManager_ProKnowledge_Model::SCOPE_GENERAL
            : MailManager_ProKnowledge_Model::SCOPE_PERSONAL;
    }
}
