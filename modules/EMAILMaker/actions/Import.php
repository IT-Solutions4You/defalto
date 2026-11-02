<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_Import_Action extends Vtiger_Save_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    public function process(Vtiger_Request $request)
    {
        if ($_FILES['import_file']['error'] == 0) {
            $tmp_file_name = $_FILES['import_file']['tmp_name'];

            $fh = fopen($tmp_file_name, "r");
            $xml_content = fread($fh, filesize($tmp_file_name));
            fclose($fh);

            $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
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
                    EMAILMaker_Record_Model::saveTemplate([
                        'templatename' => $templatename,
                        'subject' => $subject,
                        'module' => $modulename,
                        'description' => $description,
                        'body' => $body,
                        'deleted' => 0,
                        'is_listview' => $is_listview,
                        'is_theme' => $is_theme,
                    ], null);

                    $EMAILMaker->AddLinks($modulename);
                }
            }
        }
        header('Location: index.php?module=EMAILMaker&view=List');
    }

    private function cdataDecode($text)
    {
        $From = ["<|!|[%|CDATA|[%|", "|%]|]|>"];
        $To = ["<![CDATA[", "]]>"];
        $decode_text = str_replace($From, $To, $text);

        return $decode_text;
    }
}
