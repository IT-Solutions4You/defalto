<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'IncomingPayment' => 'Incoming Payments',
    'SINGLE_IncomingPayment' => 'Incoming Payment',
    'LBL_INCOMING_PAYMENT_INTEGRATION' => 'Incoming Payments Integration',
    'LBL_INTEGRATION_INTRO' => 'Choose which documents receive incoming payments and which of their fields the module reads and updates.',
    'LBL_MODULE' => 'Module',
    'LBL_ACTIVE' => 'Active',
    'LBL_STATUS_FIELD' => 'Status field',
    'LBL_STATUS_VALUE' => 'Status when fully paid',
    'LBL_STATUS_VALUE_BACK' => 'Status when no longer paid',
    'LBL_RELATED_TO_FIELD' => 'Organization field',
    'LBL_TOTAL_FIELD' => 'Total amount field',
    'LBL_PAID_FIELD' => 'Paid amount field',
    'LBL_BALANCE_FIELD' => 'Balance field',
    'LBL_NONE' => 'None',
    'LBL_SAVE' => 'Save',
    'LBL_FIELDS_NOTE' => 'While an integration is active, the paid amount field of the document is read-only and is calculated from the received payments. Missing paid and balance fields are created automatically.',
    'LBL_NO_MODULES' => 'No inventory module is available for the integration.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Underpayment threshold',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'A document whose balance is at most this amount gets the paid status. The amount is compared in the document currency without conversion and the balance keeps the real underpayment. 0 means only fully paid documents.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'The underpayment threshold must be a number of 0 or more.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'None',
    'JS_INTEGRATION_SAVED' => 'Integration settings saved',
    'JS_TOTAL_FIELD_REQUIRED' => 'Select the total amount field for every active module',
];
