<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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