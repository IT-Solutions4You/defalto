<?php

if (!function_exists('isInMultiselectbox')) {
    function isInMultiselectbox($value, $search)
    {
        global $language;
        $app_strings = return_application_language($language);
        $Values = explode(" |##| ", $value);
        if (in_array($search, $Values)) {
            $s = $app_strings["LBL_YES"];
        } else {
            $s = $app_strings["LBL_NO"];
        }
        return $s;
    }
}