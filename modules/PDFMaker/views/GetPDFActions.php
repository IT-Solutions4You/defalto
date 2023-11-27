<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class PDFMaker_GetPDFActions_View extends Vtiger_BasicAjax_View
{

    public function process(Vtiger_Request $request)
    {
        $module = false;

        if ($request->has('source_module') && !$request->isEmpty('source_module')) {
            $source_module = $request->get('source_module');
        } elseif ($request->has('record') && !$request->isEmpty('record')) {
            $source_module = $module = getSalesEntityType($request->get('record'));
        }

        $SourceModuleModel = Vtiger_Module_Model::getInstance($source_module);
        $isentitytype = $SourceModuleModel->isEntityModule();

        if ($isentitytype) {
            $PDFMakerModel = Vtiger_Module_Model::getInstance('PDFMaker');

            if ($request->has('record') && !$request->isEmpty('record')) {
                $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
                $currentLanguage = Vtiger_Language_Handler::getLanguage();

                $viewer = $this->getViewer($request);

                $record = $request->get('record');

                if (!$module) {
                    $module = getSalesEntityType($record);
                }

                if (!empty($module) && $module == $source_module) {
                    $viewer->assign('MODULE', $module);
                    $viewer->assign('ID', $record);

                    require('user_privileges/user_privileges_' . $current_user->id . '.php');

                    $viewer->assign('ENABLE_PDFMAKER', 'true');
                    $viewer->assign('PDFMAKER_MOD', return_module_language($currentLanguage, 'PDFMaker'));
                    $viewer->assign('CURRENT_LANGUAGE', $currentLanguage);
                    $viewer->assign('IS_ADMIN', is_admin($current_user));

                    $template_languages = $PDFMakerModel->GetAvailableLanguages();
                    $viewer->assign('TEMPLATE_LANGUAGES', $template_languages);

                    $tpl_name = 'GetPDFActions';

                    if ($request->has('mode') && !$request->isEmpty('mode')) {
                        $mode = $request->get('mode');
                        if ($mode == 'getButtons') {
                            $tpl_name = 'GetPDFButtons';
                        }
                    }

                    $viewer->view($tpl_name . '.tpl', 'PDFMaker');
                }
            }
        }
    }
}

