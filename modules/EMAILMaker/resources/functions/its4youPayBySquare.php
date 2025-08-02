<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

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
            'vs'       => $vs,
            'ss'       => $ss,
            'cs'       => $cs,
            'note'     => $note,
            'due_date' => $due_date,
            'size'     => $size,
        ]);

        return $payBySquare->getImage();
    }
}