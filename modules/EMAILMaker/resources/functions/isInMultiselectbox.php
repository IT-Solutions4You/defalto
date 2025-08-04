<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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