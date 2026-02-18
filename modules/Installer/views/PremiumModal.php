<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_PremiumModal_View extends Vtiger_Footer_View
{
    /**
     * @inheritDoc
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $moduleName = $request->get('module');
        $forModule = $request->get('for_module');

        if (empty($moduleName)) {
            $moduleName = $request->getModule();
        }

        $viewer->assign('MODULE', $moduleName);
        $premiumItems = [];
        $i = 1;

        while ($i) {
            $key = 'LBL_PREMIUM_MODAL_ITEM_' . $i;
            $translated = vtranslate($key, $forModule);

            if ($translated !== $key) {
                $premiumItems[] = $translated;
                $i++;
            } else {
                $i = false;
            }
        }

        $viewer->assign('PREMIUM_ITEMS', $premiumItems);
        $viewer->assign('FOR_MODULE', $forModule);
        $viewer->assign('BUY_URL', 'https://defalto.com/services/extend-your-defalto-crm-with-extensions/');

        $viewer->view('PremiumModal.tpl', 'Installer');
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }
}