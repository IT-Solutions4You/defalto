<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_RecurringDeleteCheck_View extends Vtiger_Index_View
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->view('RecurringDeleteView.tpl', $moduleName);
    }
}
