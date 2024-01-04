<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_SaveEMAILTemplate_Action extends Vtiger_Action_Controller
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        EMAILMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $adb->println("TRANS save emailmaker starts");
        $adb->startTransaction();

        $S_Data = $request->getAll();
        $templateName = $request->get('templatename');
        $moduleName = $request->get('modulename');
        $templateId = $request->get('templateid');
        $description = $request->get('description');
        $subject = $request->get('subject');
        $is_theme = $request->get('is_theme');
        $body = $S_Data['body'];
        $owner = $request->get('template_owner');
        $sharingType = $request->get('sharing');
        $email_category = $request->get('email_category');
        $is_active = $request->get('is_active');

        $is_default_dv = '' != $request->get('is_default_dv') ? '1' : '0';
        $is_default_lv = '' != $request->get('is_default_lv') ? '1' : "0";
        $is_listview = '' != $request->get('is_listview') ? '1' : '0';

        $templateParams = array(
            'templatename' => $templateName,
            'module' => $moduleName,
            'description' => $description,
            'subject' => $subject,
            'body' => $body,
            'owner' => $owner,
            'sharingtype' => $sharingType,
            'category' => $email_category,
            'is_listview' => $is_listview,
            'is_theme' => $is_theme,
        );

        $dec_point = $request->get('dec_point');
        $dec_decimals = $request->get('dec_decimals');
        $dec_thousands = $request->get('dec_thousands');

        $settingsParams = array(
            'decimals' => $dec_decimals,
            'decimal_point' => $dec_point,
            'thousands_separator' => ' ' == $dec_thousands ? 'sp' : $dec_thousands
        );

        $templateId = EMAILMaker_Record_Model::saveTemplate($templateParams, $templateId);

        EMAILMaker_Record_Model::saveTemplateSettings($settingsParams);
        EMAILMaker_Record_Model::saveIgnoredPicklistValues(explode(',', $request->get('ignore_picklist_values')));

        EMAILMaker_Record_Model::saveUserStatus($templateId, $moduleName, $is_active, $is_default_lv, $is_default_dv, $request->get('tmpl_order'));
        EMAILMaker_Record_Model::saveSharing($templateId, $sharingType, $request->get('members'));
        EMAILMaker_Record_Model::saveDefaultFrom($templateId, $request->get('default_from_email'));
        EMAILMaker_Record_Model::saveDisplayed($templateId, $request->get('displayedValue'), $request->get('display_conditions'));

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $EMAILMaker->AddLinks($moduleName);

        $adb->completeTransaction();
        $adb->println("TRANS save emailmaker ends");

        $redirect = $request->get('redirect');

        if ($redirect == 'false') {
            $redirect_url = 'index.php?module=EMAILMaker&view=Edit&applied=true&record=' . $templateId;

            if (!$request->isEmpty('return_module')) {
                $redirect_url .= '&return_module=' . $request->get('return_module');
            }

            if (!$request->isEmpty('return_view')) {
                $redirect_url .= '&return_view=' . $request->get('return_view');
            }

            header("Location:" . $redirect_url);
        } elseif ($is_theme == "1") {
            header('Location:index.php?module=EMAILMaker&view=Edit&mode=selectTheme&return_module=EMAILMaker&return_view=List');
        } else {
            header('Location:index.php?module=EMAILMaker&view=Detail&record=' . $templateId);
        }
    }
}