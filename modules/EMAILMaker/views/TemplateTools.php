<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_TemplateTools_View extends Vtiger_Index_View
{

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $viewer = $this->getViewer($request);
        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        if ($EMAILMaker->CheckPermissions("EDIT")) {
            $viewer->assign("EXPORT", "yes");
            $viewer->assign("IMPORT", "yes");
        }
        if ($request->get('from_view') == 'Detail' && !$request->isEmpty('from_templateid')) {
            $viewer->assign('ALLOW_SET_AS', 'yes');
            $EMAILtemplateResult = $EMAILMaker->GetDetailViewData($request->get('from_templateid'));
            $viewer->assign("IS_ACTIVE", $EMAILtemplateResult["is_active"]);
            $viewer->assign("IS_DEFAULT", $EMAILtemplateResult["is_default"]);
            $viewer->assign("ACTIVATE_BUTTON", $EMAILtemplateResult["activateButton"]);
            $viewer->assign("DEFAULT_BUTTON", $EMAILtemplateResult["defaultButton"]);
            $viewer->assign("TEMPLATEID", $request->get('from_templateid'));
        }
        $viewer->view('TemplateTools.tpl', 'EMAILMaker');
    }
}