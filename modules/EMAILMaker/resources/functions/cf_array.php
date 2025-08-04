<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * This function returns
 *
 * @param $name  - array name
 * @param $value -
 * */
if (!function_exists('addToCFArray')) {
    function addToCFArray($name, $value)
    {
        global $PDFContent;
        $PDFContent->PDFMakerCFArray[$name][] = $value;

        return "";
    }
}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 * */
if (!function_exists('implodeCFArray')) {
    function implodeCFArray($name, $glue)
    {
        global $PDFContent;

        return implode($glue, $PDFContent->PDFMakerCFArray[$name]);
    }
}
/**
 * This function returns
 *
 * @param $name  - array name
 * @param $value -
 * */
if (!function_exists('addToCFArrayALL')) {
    function addToCFArrayALL($name, $value)
    {
        global $focus;
        $focus->PDFMakerCFArrayALL[$name][] = $value;

        return "";
    }
}
/**
 * Join array elements with a string
 *
 * @param $name - array name
 * @param $glue -
 * */
if (!function_exists('implodeCFArrayALL')) {
    function implodeCFArrayALL($name, $glue)
    {
        global $focus;

        return implode($glue, $focus->PDFMakerCFArrayALL[$name]);
    }
}
/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 * */
if (!function_exists('sumCFArray')) {
    function sumCFArray($name)
    {
        global $PDFContent;
        foreach ($PDFContent->PDFMakerCFArray[$name] as $key => $number) {
            $PDFContent->PDFMakerCFArray[$name][$key] = its4you_formatNumberFromPDF($number);
        }
        $value = array_sum($PDFContent->PDFMakerCFArray[$name]);

        return its4you_formatNumberToPDF($value);
    }
}
/**
 * This function returns the sum of values in an array.
 *
 * @param $name - array name
 * */
if (!function_exists('sumCFArrayAll')) {
    function sumCFArrayAll($name)
    {
        global $focus;
        foreach ($focus->PDFMakerCFArrayALL[$name] as $key => $number) {
            $focus->PDFMakerCFArrayALL[$name][$key] = its4you_formatNumberFromPDF($number);
        }
        $value = array_sum($focus->PDFMakerCFArrayALL[$name]);

        return its4you_formatNumberToPDF($value);
    }
}