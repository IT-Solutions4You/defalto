<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class PDFMaker_AjaxRequestHandle_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $cu_model = Users_Record_Model::getCurrentUserModel();
        switch ($request->get('handler')) {
            case 'fill_lang':
                $module = addslashes($request->get('langmod'));
                $mod_lang_big = Vtiger_Language_Handler::getModuleStringsFromFile($cu_model->get('language'), $module);
                $mod_lang = $mod_lang_big['languageStrings'];

                unset($mod_lang_big);

                $module_lang_labels = array_flip($mod_lang);
                $module_lang_labels = array_flip($module_lang_labels);

                asort($module_lang_labels);

                $response = new Vtiger_Response();
                $response->setResult(['success' => true, 'labels' => $module_lang_labels]);
                $response->emit();
                break;

            case 'fill_module_product_fields':
                $module = addslashes($request->get('productmod'));
                $PDFMaker = new PDFMaker_PDFMaker_Model();
                $Product_Block_Fields = $PDFMaker->GetProductBlockFields($module);
                $keys = implode('||', array_keys($Product_Block_Fields['SELECT_PRODUCT_FIELD']));
                $values = implode('||', $Product_Block_Fields['SELECT_PRODUCT_FIELD']);

                echo $keys . '|@|' . $values;
                break;
        }
    }
}