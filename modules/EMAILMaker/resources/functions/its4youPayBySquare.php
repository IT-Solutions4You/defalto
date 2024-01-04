<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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