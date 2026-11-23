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
    'OutgoingPayment' => 'Outgoing Payments',
    'SINGLE_OutgoingPayment' => 'Outgoing Payment',
    'LBL_OUTGOING_PAYMENT_INTEGRATION' => 'Outgoing Payments Integration',
    'LBL_INTEGRATION_INTRO' => 'Choose which documents receive outgoing payments and which of their fields the module reads and updates.',
    'LBL_MODULE' => 'Module',
    'LBL_ACTIVE' => 'Active',
    'LBL_STATUS_FIELD' => 'Status field',
    'LBL_STATUS_VALUE' => 'Status when fully paid',
    'LBL_STATUS_VALUE_BACK' => 'Status when no longer paid',
    'LBL_RELATED_TO_FIELD' => 'Vendor or organization field',
    'LBL_TOTAL_FIELD' => 'Total amount field',
    'LBL_PAID_FIELD' => 'Paid amount field',
    'LBL_BALANCE_FIELD' => 'Balance field',
    'LBL_NONE' => 'None',
    'LBL_SAVE' => 'Save',
    'LBL_FIELDS_NOTE' => 'While an integration is active, the paid amount field of the document is read-only and is calculated from the paid outgoing payments. Missing paid and balance fields are created automatically.',
    'LBL_NO_MODULES' => 'No inventory module is available for the integration.',
    'LBL_UNDERPAYMENT_THRESHOLD' => 'Underpayment threshold',
    'LBL_UNDERPAYMENT_THRESHOLD_HELP' => 'A document whose balance is at most this amount gets the paid status. The amount is compared in the document currency without conversion and the balance keeps the real underpayment. 0 means only fully paid documents.',
    'LBL_INVALID_UNDERPAYMENT_THRESHOLD' => 'The underpayment threshold must be a number of 0 or more.',
    'LBL_REFUND' => 'Refund',
    'LBL_REFUND_HELP' => 'Payments of this module are refunds of the document and are subtracted from its paid amount.',
    'LBL_REFUND_WITHOUT_PAYING' => 'No active payment module pays this document; refunds are not counted.',
    'LBL_TWO_PAYING_MODULES' => 'Another payment module already pays %s. Mark the payments of one of them as refunds.',
];

$jsLanguageStrings = [
    'JS_NONE' => 'None',
    'JS_INTEGRATION_SAVED' => 'Integration settings saved',
    'JS_TOTAL_FIELD_REQUIRED' => 'Select the total amount field for every active module',
];
