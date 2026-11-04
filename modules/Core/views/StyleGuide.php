<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_StyleGuide_View extends Vtiger_Footer_View
{
    /**
     * @inheritDoc
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        return 'Defalto Style Guide';
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        if (!$currentUser || !$currentUser->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request): void
    {
        $viewer = $this->getViewer($request);
        $viewer->view('StyleGuide.tpl', 'Core');
    }
}
