<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!function_exists('datefmt')) {
    function datefmt($date, $outFormat = "d.m.Y")
    {
        if (strlen($date) > 10) {
            $date = substr($date, 0, 10);
        }
        $sql_format_date = getValidDBInsertDateValue($date);
        $date = new DateTime($sql_format_date);

        return $date->format($outFormat);
    }
}