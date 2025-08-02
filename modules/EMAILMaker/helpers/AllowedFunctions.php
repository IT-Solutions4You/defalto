<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class EMAILMaker_AllowedFunctions_Helper
{
    public static $Allowed_Functions = [
        "number_format",
        "nl2br",
        "substr",
        "trim",
        "str_replace",
        "strpos",
        "ucfirst",
        "ucwords",
        "html_entity_decode",
        "htmlentities",
        "htmlspecialchars_decode",
        "htmlspecialchars",
        "lcfirst",
        "strtolower",
        "strtoupper",
        "strtr",
        "mktime",
        "date",
        "time",
        "round",
        "abs",
        "ceil",
        "floor",
        "rand"
    ];

    public function getAllowedFunctions()
    {
        $All_Allowed_Functions = self::$Allowed_Functions;

        $files = glob('modules/EMAILMaker/resources/functions/*.php');
        foreach ($files as $file) {
            $filename = $file;
            $source = fread(fopen($filename, "r"), filesize($filename));
            $tokens = token_get_all($source);
            foreach ($tokens as $token) {
                if (is_array($token)) {
                    if ($token[0] == T_FUNCTION) {
                        $ready = true;
                    } elseif ($ready) {
                        if ($token[0] == T_STRING && $function_name == "") {
                            $function_name = $token[1];
                        }
                    }
                } elseif ($ready && $token == "{") {
                    $ready = false;
                    $All_Allowed_Functions[] = trim($function_name);
                    $function_name = "";
                }
            }
        }

        $own_allowed_functions_desc = "modules/EMAILMaker/resources/functions/AllowedFunctions.txt";
        if (file_exists($own_allowed_functions_desc)) {
            $own_allowed_functions_content = file_get_contents($own_allowed_functions_desc);

            $Own_Allowed_Functions = explode(",", $own_allowed_functions_content);

            foreach ($Own_Allowed_Functions as $own_allowed_function) {
                $own_allowed_function = trim($own_allowed_function);

                if (!in_array($own_allowed_function, $All_Allowed_Functions)) {
                    $All_Allowed_Functions[] = $own_allowed_function;
                }
            }
        }

        return $All_Allowed_Functions;
    }
}