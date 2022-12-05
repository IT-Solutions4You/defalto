<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

if (!function_exists('payBySquare')) {
    function payBySquare($iban, $amount, $currency = 'EUR', $vs = '', $ss = '', $cs = '', $note = '', $due_date = '', $size = '150')
    {
        if (!class_exists('PDFMaker_PayBySquare_Helper')) {
            return 'Required PDFMaker PayBySquare';
        }

        $due_date = DateTimeField::convertToDBFormat($due_date);
        $amount = PDFMaker_Module_Model::convertToFloatNumber($amount);
        $payBySquare = PDFMaker_PayBySquare_Helper::getInstance($iban, $amount);
        $payBySquare->convertFromArray([
            'currency' => $currency,
            'vs' => $vs,
            'ss' => $ss,
            'cs' => $cs,
            'note' => $note,
            'due_date' => $due_date,
            'size' => $size,
        ]);

        return $payBySquare->getImage();
    }
}