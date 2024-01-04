<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_BodyWidget_View extends Vtiger_Detail_View
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $moduleName = $request->getModule();

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD_ID', $recordId);
        $viewer->assign('MODULE_NAME', $moduleName);

        $viewer->view('BodyWidget.tpl', $moduleName);
    }
}