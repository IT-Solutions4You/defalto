<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!function_exists('setCFGNumberValue')) {
    function setCFGNumberValue($name, $value = '0')
    {
        global $focus;
        $value = its4you_formatNumberFromPDF($value);
        if (!isset($focus->PDFMakerCFGNumberValue[$name]) || empty($focus->PDFMakerCFGNumberValue[$name])) {
            $focus->PDFMakerCFGNumberValue[$name] = $value;
        }

        return "";
    }
}
if (!function_exists('sumCFGNumberValue')) {
    function sumCFGNumberValue($name, $value1)
    {
        mathCFGNumberValue($name, "+", $value1);

        return "";
    }
}

if (!function_exists('deductCFGNumberValue')) {
    function deductCFGNumberValue($name, $value1)
    {
        mathCFGNumberValue($name, "-", $value1);

        return "";
    }
}

if (!function_exists('mathCFGNumberValue')) {
    function mathCFGNumberValue($name, $type1, $value1, $type2 = "", $value2 = "")
    {
        global $focus;
        if (isset($focus->PDFMakerCFGNumberValue[$value1]) && $focus->PDFMakerCFGNumberValue[$value1] != "") {
            $value1 = $focus->PDFMakerCFGNumberValue[$value1];
        } else {
            $value1 = its4you_formatNumberFromPDF($value1);
        }
        if ($value2 == "") {
            if ($type1 == "=") {
                $focus->PDFMakerCFGNumberValue[$name] = $value1;
            } elseif ($type1 == "+") {
                $focus->PDFMakerCFGNumberValue[$name] += $value1;
            } elseif ($type1 == "-") {
                $focus->PDFMakerCFGNumberValue[$name] -= $value1;
            }
        } else {
            if (isset($focus->PDFMakerCFGNumberValue[$value2]) && $focus->PDFMakerCFGNumberValue[$value2] != "") {
                $value2 = $focus->PDFMakerCFGNumberValue[$value2];
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
                $focus->PDFMakerCFGNumberValue[$name] = $newvalue;
            } elseif ($type1 == "+") {
                $focus->PDFMakerCFGNumberValue[$name] += $newvalue;
            } elseif ($type1 == "-") {
                $focus->PDFMakerCFGNumberValue[$name] -= $newvalue;
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
if (!function_exists('showCFGNumberValue')) {
    function showCFGNumberValue($name)
    {
        global $focus;
        if (isset($focus->PDFMakerCFGNumberValue[$name])) {
            $value = $focus->PDFMakerCFGNumberValue[$name];

            return its4you_formatNumberToPDF($value);
        } else {
            return '[CUSTOM FUNCTION ERROR: number value "' . $name . '" is not defined.]';
        }
    }
}