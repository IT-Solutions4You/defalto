<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EMAILMaker_Import_Action extends Vtiger_Save_Action
{

    public function checkPermission(Vtiger_Request $request)
    {
    }

    public function process(Vtiger_Request $request)
    {
        if ($_FILES['import_file']['error'] == 0) {

            $tmp_file_name = $_FILES['import_file']['tmp_name'];

            $fh = fopen($tmp_file_name, "r");
            $xml_content = fread($fh, filesize($tmp_file_name));
            fclose($fh);

            $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
            $adb = PearDatabase::getInstance();
            $xml = new SimpleXMLElement($xml_content);

            foreach ($xml->template as $data) {
                if ($data->type = "EMAILMaker") {
                    $templatename = $this->cdataDecode($data->templatename);
                    $subject = $this->cdataDecode($data->subject);
                    $description = $this->cdataDecode($data->description);
                    $modulename = $this->cdataDecode($data->module);
                    $tabid = getTabId($modulename);
                    $body = $this->cdataDecode($data->body);
                    $is_listview = $this->cdataDecode($data->is_listview);
                    if ($is_listview == "0") {
                        $is_listview = "0";
                    }
                    $is_theme = $this->cdataDecode($data->is_theme);
                    if ($is_theme == "0") {
                        $is_theme = "0";
                    }
                    $templateid = $adb->getUniqueID('vtiger_emakertemplates');
                    $adb->pquery("insert into vtiger_emakertemplates (templatename,subject,module,description,body,deleted,templateid,is_listview,is_theme) values (?,?,?,?,?,?,?,?,?)", array($templatename, $subject, $modulename, $description, $body, 0, $templateid, $is_listview, $is_theme));
                    $EMAILMaker->AddLinks($modulename);
                }
            }
        }
        header('Location: index.php?module=EMAILMaker&view=List');
    }

    private function cdataDecode($text)
    {
        $From = array("<|!|[%|CDATA|[%|", "|%]|]|>");
        $To = array("<![CDATA[", "]]>");
        $decode_text = str_replace($From, $To, $text);
        return $decode_text;
    }
}