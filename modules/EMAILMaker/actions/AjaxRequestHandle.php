<?php
/* * *******************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class EMAILMaker_AjaxRequestHandle_Action extends Vtiger_Action_Controller
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();
        $cu_model = Users_Record_Model::getCurrentUserModel();
        switch ($request->get("handler")) {
            case "fill_lang":
                $module = addslashes($request->get("langmod"));
                $mod_lang_big = Vtiger_Language_Handler::getModuleStringsFromFile($cu_model->get('language'), $module);
                $mod_lang = $mod_lang_big['languageStrings'];
                unset($mod_lang_big);
                $module_lang_labels = array_flip($mod_lang);
                $module_lang_labels = array_flip($module_lang_labels);
                asort($module_lang_labels);
                $keys = implode('||', array_keys($module_lang_labels));
                $response = new Vtiger_Response();
                $response->setResult(array('success' => true, 'labels' => $module_lang_labels));
                $response->emit();
                break;
            case "templates_order":
                $inStr = $request->get("tmpl_order");
                $inStr = rtrim($inStr, "#");
                $inArr = explode("#", $inStr);
                $tmplArr = array();
                foreach ($inArr as $val) {
                    $valArr = explode("_", $val);
                    $tmplArr[$valArr[0]]["order"] = $valArr[1];
                    $tmplArr[$valArr[0]]["is_active"] = "1";
                    $tmplArr[$valArr[0]]["is_default"] = "0";
                }
                $sql = "SELECT templateid, userid, is_active, is_default, sequence
                FROM vtiger_emakertemplates_userstatus
                WHERE userid = ?";
                $result = $adb->pquery($sql, array($cu_model->getId()));
                while ($row = $adb->fetchByAssoc($result)) {
                    if (!isset($tmplArr[$row["templateid"]])) {
                        $tmplArr[$row["templateid"]]["order"] = $row["sequence"];
                    }
                    $tmplArr[$row["templateid"]]["is_active"] = $row["is_active"];
                    $tmplArr[$row["templateid"]]["is_default"] = $row["is_default"];
                }
                $adb->pquery("DELETE FROM vtiger_emakertemplates_userstatus WHERE userid=?", array($cu_model->getId()));
                $sqlB = "";
                $params = array();
                foreach ($tmplArr as $templateid => $valArr) {
                    $sqlB .= "(?,?,?,?,?),";
                    $params[] = $templateid;
                    $params[] = $cu_model->getId();
                    $params[] = $valArr["is_active"];
                    $params[] = $valArr["is_default"];
                    $params[] = $valArr["order"];
                }
                $result = "error";
                if ($sqlB != "") {
                    $sqlB = rtrim($sqlB, ",");
                    $sql = "INSERT INTO vtiger_emakertemplates_userstatus(templateid, userid, is_active, is_default, sequence) VALUES " . $sqlB;
                    $adb->pquery($sql, $params);
                    $result = "ok";
                }
                echo $result;
                break;
            case "custom_labels_edit";
                $adb->pquery("DELETE FROM vtiger_emakertemplates_label_vals WHERE label_id=? AND lang_id=?", array($request->get("label_id"), $request->get("lang_id")));
                $adb->pquery("INSERT INTO vtiger_emakertemplates_label_vals(label_id, lang_id, label_value) VALUES(?,?,?)", array($request->get("label_id"), $request->get("lang_id"), $request->get("label_value")));
                break;
            case "fill_relblocks":
                $module = addslashes($request->get("selmod"));
                $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                $Related_Blocks = $EMAILMaker->GetRelatedBlocks($module, false);
                $response = new Vtiger_Response();
                $response->setResult(array('success' => true, 'relblocks' => $Related_Blocks));
                $response->emit();
                break;
            case "fill_module_product_fields":
                $module = addslashes($request->get("productmod"));
                $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                $Product_Block_Fields = $EMAILMaker->GetProductBlockFields($module);
                $keys = implode('||', array_keys($Product_Block_Fields["SELECT_PRODUCT_FIELD"]));
                $values = implode('||', $Product_Block_Fields["SELECT_PRODUCT_FIELD"]);
                echo $keys . '|@|' . $values;
                break;
            case 'get_relblock':
                $record = addslashes($request->get('relblockid'));

                echo EMAILMaker_RelatedBlock_Model::getBlockBody($record);
                break;
            case "delete_relblock":
                $record = addslashes($request->get("relblockid"));
                $adb->pquery("UPDATE vtiger_emakertemplates_relblocks SET deleted = 1 WHERE relblockid = ?", array($record));
                break;
        }
    }
}