<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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