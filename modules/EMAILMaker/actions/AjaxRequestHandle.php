<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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
                $response->setResult(['success' => true, 'labels' => $module_lang_labels]);
                $response->emit();
                break;
            case "templates_order":
                $inStr = $request->get("tmpl_order");
                $inStr = rtrim($inStr, "#");
                $inArr = explode("#", $inStr);
                $tmplArr = [];
                foreach ($inArr as $val) {
                    $valArr = explode("_", $val);
                    $tmplArr[$valArr[0]]["order"] = $valArr[1];
                    $tmplArr[$valArr[0]]["is_active"] = "1";
                    $tmplArr[$valArr[0]]["is_default"] = "0";
                }
                $sql = "SELECT templateid, userid, is_active, is_default, sequence
                FROM vtiger_emakertemplates_userstatus
                WHERE userid = ?";
                $result = $adb->pquery($sql, [$cu_model->getId()]);
                while ($row = $adb->fetchByAssoc($result)) {
                    if (!isset($tmplArr[$row["templateid"]])) {
                        $tmplArr[$row["templateid"]]["order"] = $row["sequence"];
                    }
                    $tmplArr[$row["templateid"]]["is_active"] = $row["is_active"];
                    $tmplArr[$row["templateid"]]["is_default"] = $row["is_default"];
                }
                $adb->pquery("DELETE FROM vtiger_emakertemplates_userstatus WHERE userid=?", [$cu_model->getId()]);
                $sqlB = "";
                $params = [];
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
                $adb->pquery("DELETE FROM vtiger_emakertemplates_label_vals WHERE label_id=? AND lang_id=?", [$request->get("label_id"), $request->get("lang_id")]);
                $adb->pquery(
                    "INSERT INTO vtiger_emakertemplates_label_vals(label_id, lang_id, label_value) VALUES(?,?,?)",
                    [$request->get("label_id"), $request->get("lang_id"), $request->get("label_value")]
                );
                break;
            case "fill_module_product_fields":
                $module = addslashes($request->get("productmod"));
                $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
                $Product_Block_Fields = $EMAILMaker->GetProductBlockFields($module);
                $keys = implode('||', array_keys($Product_Block_Fields["SELECT_PRODUCT_FIELD"]));
                $values = implode('||', $Product_Block_Fields["SELECT_PRODUCT_FIELD"]);
                echo $keys . '|@|' . $values;
                break;
        }
    }
}