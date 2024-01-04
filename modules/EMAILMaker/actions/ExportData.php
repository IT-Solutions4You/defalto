<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_ExportData_Action extends Vtiger_Mass_Action
{

    private $moduleInstance;
    private $focus;

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        $this->ExportData($request);
    }

    public function ExportData(Vtiger_Request $request)
    {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->get('source_module');

        $this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $this->focus = CRMEntity::getInstance($moduleName);

        $orderBy = $request->get('orderby');
        $sortOrder = $request->get('sortorder');

        $EMAILMakerModel = Vtiger_Module_Model::getInstance('EMAILMaker');
        $mode = $request->getMode();

        if ($mode == "ExportAllData") {
            $result = $EMAILMakerModel->GetListviewResult($orderBy, $sortOrder, false);
        } elseif ($mode == "ExportCurrentPage") {
            $result = $EMAILMakerModel->GetListviewResult($orderBy, $sortOrder, $request);
        } else {
            $sql = $this->getExportQuery($request);

            if (!empty($orderby)) {
                $sql .= " ORDER BY vtiger_emakertemplates." . $orderBy . " " . $sortOrder;
            }

            $result = $adb->pquery($sql, array());
        }
        $entries = array();
        $num_rows = $adb->num_rows($result);

        while ($row = $adb->fetchByAssoc($result)) {

            $currModule = $row['module'];
            $templateid = $row['templateid'];

            $Template_Permissions_Data = $EMAILMakerModel->returnTemplatePermissionsData($currModule, $templateid);
            if ($Template_Permissions_Data["detail"] === false) {
                continue;
            }

            $entries[] = $row;
        }

        $this->output($entries);

    }

    public function getExportQuery(Vtiger_Request $request)
    {

        $query = "SELECT vtiger_emakertemplates.* FROM vtiger_emakertemplates LEFT JOIN vtiger_emakertemplates_displayed USING(templateid)";
        $idList = $this->getRecordsListFromRequest($request);

        $query .= "WHERE vtiger_emakertemplates.deleted = '0'";
        if (!empty($idList)) {
            $idList = implode(',', $idList);
            $query .= 'AND vtiger_emakertemplates.templateid IN (' . $idList . ')';
        }
        return $query;
    }

    public function output($entries)
    {
        foreach ($entries as $templateResult) {
            $templatename = $templateResult["templatename"];
            $subject = $templateResult["subject"];
            $description = $templateResult["description"];
            $module = $templateResult["module"];
            $is_listview = $templateResult["is_listview"];
            $is_theme = $templateResult["is_theme"];
            $body = decode_html($templateResult["body"]);
            $c .= "<template>";
            $c .= "<type>EMAILMaker</type>";
            $c .= "<templatename>" . $this->cdataEncode($templatename, true) . "</templatename>";
            $c .= "<subject>" . $this->cdataEncode($subject, true) . "</subject>";
            $c .= "<description>" . $this->cdataEncode($description, true) . "</description>";
            $c .= "<module>" . $this->cdataEncode($module) . "</module>";
            $c .= "<is_listview>" . $this->cdataEncode($is_listview) . "</is_listview>";
            $c .= "<is_theme>" . $this->cdataEncode($is_theme) . "</is_theme>";
            $c .= "<body>";
            $c .= $this->cdataEncode($body, true);
            $c .= "</body>";
            $c .= "</template>";
        }

        header('Content-Type: application/xhtml+xml');
        header("Content-Disposition: attachment; filename=export.xml");

        echo "<?xml version='1.0'?" . ">";
        echo "<export>";
        echo $c;
        echo "</export>";
        exit;
    }

    private function cdataEncode($text, $encode = false)
    {
        $From = array("<![CDATA[", "]]>");
        $To = array("<|!|[%|CDATA|[%|", "|%]|]|>");

        if ($text != "") {
            $pos1 = strpos("<![CDATA[", $text);
            $pos2 = strpos("]]>", $text);

            if ($pos1 === false && $pos2 === false && $encode == false) {
                $content = $text;
            } else {
                $text = decode_html($text);
                $encode_text = str_replace($From, $To, $text);
                $content = "<![CDATA[" . $encode_text . "]]>";
            }
        } else {
            $content = "";
        }
        return $content;
    }
}