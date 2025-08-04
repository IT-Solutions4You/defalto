<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!function_exists('setCFNumberValue')) {
    function setCFNumberValue($name, $value = '0')
    {
        global $PDFContent;
        $value = its4you_formatNumberFromPDF($value);
        $PDFContent->PDFMakerCFNumberValue[$name] = $value;

        return "";
    }
}

if (!function_exists('sumCFNumberValue')) {
    function sumCFNumberValue($name, $value1)
    {
        mathCFNumberValue($name, "+", $value1);

        return "";
    }
}

if (!function_exists('deductCFNumberValue')) {
    function deductCFNumberValue($name, $value1)
    {
        mathCFNumberValue($name, "-", $value1);

        return "";
    }
}

if (!function_exists('mathCFNumberValue')) {
    function mathCFNumberValue($name, $type1, $value1, $type2 = "", $value2 = "")
    {
        global $PDFContent;

        if (isset($PDFContent->PDFMakerCFNumberValue[$value1]) && $PDFContent->PDFMakerCFNumberValue[$value1] != "") {
            $value1 = $PDFContent->PDFMakerCFNumberValue[$value1];
        } else {
            $value1 = its4you_formatNumberFromPDF($value1);
        }

        if ($value2 == "") {
            if ($type1 == "=") {
                $PDFContent->PDFMakerCFNumberValue[$name] = $value1;
            } elseif ($type1 == "+") {
                $PDFContent->PDFMakerCFNumberValue[$name] += $value1;
            } elseif ($type1 == "-") {
                $PDFContent->PDFMakerCFNumberValue[$name] -= $value1;
            }
        } else {
            if (isset($PDFContent->PDFMakerCFNumberValue[$value2]) && $PDFContent->PDFMakerCFNumberValue[$value2] != "") {
                $value2 = $PDFContent->PDFMakerCFNumberValue[$value2];
            } else {
                $value2 = its4you_formatNumberFromPDF($value2);
            }

            if ($type2 == "+") {
                $newvalue = $value1 + $value2;
            } elseif ($type2 == "-") {
                $newvalue = $value1 - $value2;
            } elseif ($type2 == "*") {
                $newvalue = $value1 * $value2;
            } elseif ($type2 == "/") {
                $newvalue = $value1 / $value2;
            }

            if ($type1 == "=") {
                $PDFContent->PDFMakerCFNumberValue[$name] = $newvalue;
            } elseif ($type1 == "+") {
                $PDFContent->PDFMakerCFNumberValue[$name] += $newvalue;
            } elseif ($type1 == "-") {
                $PDFContent->PDFMakerCFNumberValue[$name] -= $newvalue;
            }
        }

        return "";
    }
}
/**
 * This function show number value
 *
 * @param $name - number name
 * */
if (!function_exists('showCFNumberValue')) {
    function showCFNumberValue($name)
    {
        global $PDFContent;

        if (isset($PDFContent->PDFMakerCFNumberValue[$name])) {
            $value = $PDFContent->PDFMakerCFNumberValue[$name];

            return its4you_formatNumberToPDF($value);
        } else {
            return '[CUSTOM FUNCTION ERROR: number value "' . $name . '" is not defined.]';
        }
    }
}