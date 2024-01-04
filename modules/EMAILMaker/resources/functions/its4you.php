<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * This function executes if-else statement based on given parameters
 * @param $param1 first parameter of comparation
 * @param $comparator comparation sign - one of ==,!=,<,>,<=,>=
 * @param $param2 second parameter of comparation
 * @param $whatToReturn1 value returned when comparation succeded
 * @param $whatToReturn2 value returned when comparation not succeded
 * */
if (!function_exists('its4you_if')) {
    function its4you_if($param1, $comparator, $param2, $whatToReturn1, $whatToReturn2 = '')
    {
        global $default_charset;
        $param1 = htmlentities($param1, ENT_QUOTES, $default_charset);
        $comparator = html_entity_decode($comparator, ENT_COMPAT, 'utf-8');
        $param2 = htmlentities($param2, ENT_QUOTES, $default_charset);
        $whatToReturn1 = htmlentities($whatToReturn1, ENT_QUOTES, $default_charset);
        $whatToReturn2 = htmlentities($whatToReturn2, ENT_QUOTES, $default_charset);
        switch ($comparator) {
            case "=":
                $comparator = '==';
                break;
            case "<>":
                $comparator = '!=';
                break;
            case "=>":
                $comparator = '>=';
                break;
            case "=<":
                $comparator = '<=';
                break;
        }

        if (in_array($comparator, array('==', '!=', '>=', '<=', '>', '<'))) {
            return nl2br(html_entity_decode(eval("if('$param1' $comparator '$param2'){return '$whatToReturn1';} else {return '$whatToReturn2';}"), ENT_COMPAT, $default_charset));
        } else {
            return "Error! second parameter must be one from following: ==,!=,<,>,<=,>=";
        }
    }
}
/**
 * This function returns id of current template
 * */
if (!function_exists('getTemplateId')) {
    function getTemplateId()
    {
        global $PDFMaker_template_id;
        return $PDFMaker_template_id;
    }
}
/**
 * This function returns image of contact
 * @param $id - contact id
 * @param $width width of returned image (10%, 100px)
 * @param $height height of returned image (10%, 100px)
 * */
if (!function_exists('its4you_getContactImage')) {
    function its4you_getContactImage($id, $width, $height)
    {
        if (isset($id) and $id != "") {
            global $adb, $site_URL;
            $query = "SELECT vtiger_attachments.*
				FROM vtiger_contactdetails
				INNER JOIN vtiger_seattachmentsrel ON vtiger_contactdetails.contactid=vtiger_seattachmentsrel.crmid
				INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
				INNER JOIN vtiger_crmentity ON vtiger_attachments.attachmentsid=vtiger_crmentity.crmid
				WHERE deleted=0 AND vtiger_contactdetails.contactid=?";

            $result = $adb->pquery($query, array($id));
            $num_rows = $adb->num_rows($result);
            if ($num_rows > 0) {
                $row = $adb->query_result_rowdata($result);
                $row = EMAILMaker_EMAILContentUtils_Model::fixStoredName($row);
                $site_URL = EMAILMaker_EMAILContentUtils_Model::fixSiteUrl(vglobal('site_URL'));
                $image_src = $site_URL . $row['path'] . $row['attachmentsid'] . '_' . $row['storedname'];

                return "<img src='" . $image_src . "' width='" . $width . "' height='" . $height . "'/>";
            }
        } else {
            return "";
        }
    }
}
/**
 * This function returns formated value
 * @param $value - int
 * */
if (!function_exists('its4you_formatNumberToPDF')) {
    function its4you_formatNumberToPDF($value)
    {
        $adb = PearDatabase::getInstance();
        $PDFMaker_template_id = vglobal("PDFMaker_template_id");

        if ($PDFMaker_template_id == "email") {
            $sql = "SELECT decimals, decimal_point, thousands_separator
                            FROM vtiger_emakertemplates_settings ";
            $result = $adb->pquery($sql, array());
        } else {
            $sql = "SELECT decimals, decimal_point, thousands_separator
                            FROM vtiger_pdfmaker_settings           
                            WHERE templateid=?";
            $result = $adb->pquery($sql, array($PDFMaker_template_id));
        }
        $data = $adb->fetch_array($result);

        $decimal_point = html_entity_decode($data["decimal_point"], ENT_QUOTES);
        $thousands_separator = html_entity_decode(($data["thousands_separator"] != "sp" ? $data["thousands_separator"] : " "), ENT_QUOTES);
        $decimals = $data["decimals"];

        if (is_numeric($value)) {
            $number = number_format($value, $decimals, $decimal_point, $thousands_separator);
        } else {
            $number = "";
        }
        return $number;
    }
}
/**
 * This function returns converted value into integer
 * @param $value - int
 * */
if (!function_exists('its4you_formatNumberFromPDF')) {
    function its4you_formatNumberFromPDF($value)
    {
        $adb = PearDatabase::getInstance();
        $PDFMaker_template_id = vglobal("PDFMaker_template_id");

        if ($PDFMaker_template_id == "email") {
            $sql = "SELECT decimals, decimal_point, thousands_separator
                            FROM vtiger_emakertemplates_settings ";
            $result = $adb->pquery($sql, array());
        } else {
            $sql = "SELECT decimals, decimal_point, thousands_separator
                            FROM vtiger_pdfmaker_settings           
                            WHERE templateid=?";
            $result = $adb->pquery($sql, array($PDFMaker_template_id));
        }
        $data = $adb->fetch_array($result);

        $decimal_point = html_entity_decode($data["decimal_point"], ENT_QUOTES);
        $thousands_separator = html_entity_decode(($data["thousands_separator"] != "sp" ? $data["thousands_separator"] : " "), ENT_QUOTES);

        $number = str_replace($thousands_separator, '', $value);
        $number = str_replace($decimal_point, '.', $number);
        return $number;
    }
}
/**
 * This function returns multipication of all input values
 * @param $sum - int (unlimited count of input params)
 * using: [CUSTOMFUNCTION|its4you_multiplication|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_multiplication')) {
    function its4you_multiplication()
    {
        $input_args = func_get_args();
        $return = 0;
        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $sum = its4you_formatNumberFromPDF(strip_tags($sum));
                if (!is_numeric($sum) || $sum == '') {
                    $sum = 0;
                }
                if ($key == 0) {
                    $return = $sum;
                } else {
                    $return = $return * $sum;
                }
            }
        }
        return its4you_formatNumberToPDF($return);
    }
}
/**
 * This function returns deducated value sum1-sum2-...-sum_n (all following values are deducted from the first one)
 * @param $sum - int (unlimited count of input params)
 * using: [CUSTOMFUNCTION|its4you_deduct|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_deduct')) {
    function its4you_deduct()
    {
        $input_args = func_get_args();
        $return = 0;
        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $sum = its4you_formatNumberFromPDF(strip_tags($sum));
                if (!is_numeric($sum) || $sum == '') {
                    $sum = 0;
                }
                if ($key == 0) {
                    $return = $sum;
                } else {
                    $return -= $sum;
                }
            }
        }
        return its4you_formatNumberToPDF($return);
    }
}
/**
 * This function returns sum of input values
 * @param $sum - int (unlimited count of input params)
 * using: [CUSTOMFUNCTION|its4you_sum|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_sum')) {
    function its4you_sum()
    {
        $input_args = func_get_args();
        $return = 0;
        if (!empty($input_args)) {
            foreach ($input_args as $sum) {
                $sum = its4you_formatNumberFromPDF(strip_tags($sum));
                if (!is_numeric($sum) || $sum == '') {
                    $sum = 0;
                }

                $return += $sum;
            }
        }
        return its4you_formatNumberToPDF($return);
    }
}
/**
 * This function returns divided value sum1/sum2/.../sum_n
 * @param $sum - int (unlimited count of input params)
 * using: [CUSTOMFUNCTION|its4you_divide|param1|param2|...|param_n|CUSTOMFUNCTION]
 * */
if (!function_exists('its4you_divide')) {
    function its4you_divide()
    {
        $input_args = func_get_args();
        $return = 0;
        if (!empty($input_args)) {
            foreach ($input_args as $key => $sum) {
                $sum = its4you_formatNumberFromPDF(strip_tags($sum));
                if (!is_numeric($sum) || $sum == '') {
                    $sum = 0;
                }
                if ($key == 0) {
                    $return = $sum;
                } elseif ($sum != 0) {
                    $return = $return / $sum;
                }
            }
        }
        return its4you_formatNumberToPDF($return);
    }
}

if (!function_exists('its4you_nl2br')) {
    function its4you_nl2br($value)
    {
        global $default_charset;
        $string = str_replace(array("\\r\\n", "\\r", "\\n"), "<br />", $value);
        return $string;
    }
}