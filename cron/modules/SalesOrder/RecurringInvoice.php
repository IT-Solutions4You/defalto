<?php
/**
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License.
 * The Original Code is: vtiger CRM Open Source.
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once('include/utils/utils.php');
require_once('include/logging.php');

global $adb, $log;
$log = Logger::getLogger('RecurringInvoice');
$log->debug('invoked RecurringInvoice');

$currentDate = date('Y-m-d');
$currentDateStrTime = strtotime($currentDate);

$sql = 'SELECT vtiger_salesorder.salesorderid, recurring_frequency, start_period, end_period, last_recurring_date,
		 payment_duration FROM vtiger_salesorder
		 INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
		 INNER JOIN vtiger_invoice_recurring_info ON vtiger_salesorder.salesorderid = vtiger_invoice_recurring_info.salesorderid
		 WHERE
		    sostatus != "Cancelled"
		    AND DATE_FORMAT(start_period, "%Y-%m-%d") <= ?
		    AND (DATE_FORMAT(end_period, "%Y-%m-%d") >= ? OR end_period = "0000-00-00" OR end_period = "" OR end_period IS NULL)
		    AND DATE_FORMAT(last_recurring_date, "%Y-%m-%d") <= ?
		 ORDER BY salesorderid
		 LIMIT 50
		 ';
$result = $adb->pquery($sql, [$currentDate, $currentDate, $currentDate]);

while ($row = $adb->fetchByAssoc($result)) {
    $salesOrderId = (int)$row['salesorderid'];
    $startPeriod = $row['start_period'];
    $endPeriod = $row['end_period'];
    $recurringDate = $row['last_recurring_date'];
    $recurringFrequency = $row['recurring_frequency'];

    if ($recurringDate == null || $recurringDate == '' || $recurringDate == '0000-00-00') {
        $recurringDate = $startPeriod;
    }

    if ($endPeriod == null || $endPeriod == '' || $endPeriod == '0000-00-00') {
        $date = new DateTime($startPeriod);
        $date->modify('+10 years');

        $endPeriod = $date->format('Y-m-d');
    }

    $endDateStrTime = strtotime($endPeriod);
    $recurringDateStrTime = strtotime($recurringDate);

    if ($recurringDateStrTime < $currentDateStrTime) {
        $recurringDatesList = [];
        $nextRecurringDate = $validNextRecurringDate = $recurringDate;

        while (strtotime($validNextRecurringDate) <= $currentDateStrTime && $currentDateStrTime <= $endDateStrTime) {
            $recurringDatesList[] = $validNextRecurringDate;
            $nextRecurringDatesInfo = getRecurringDate($nextRecurringDate, $recurringFrequency);
            $validNextRecurringDate = $nextRecurringDatesInfo['validDate'];
            $nextRecurringDate = $nextRecurringDatesInfo['nextRecurringDate'];
        }

        if ($recurringDatesList) {
            foreach ($recurringDatesList as $recurringDateFromList) {
                createInvoice($salesOrderId, $recurringDateFromList);
            }

            $adb->pquery('UPDATE vtiger_invoice_recurring_info SET last_recurring_date = ? WHERE salesorderid = ?', [$validNextRecurringDate, $salesOrderId]);
        }
    } elseif ($recurringDateStrTime == $currentDateStrTime && $recurringDateStrTime <= $endDateStrTime) {
        createInvoice($salesOrderId, $recurringDate);

        $nextRecurringDatesInfo = getRecurringDate($recurringDate, $recurringFrequency);
        $nextRecurringDate = $nextRecurringDatesInfo['validDate'];
        $adb->pquery('UPDATE vtiger_invoice_recurring_info SET last_recurring_date = ? WHERE salesorderid = ?', [$nextRecurringDate, $salesOrderId]);
    }

    // Add some free time for the case when automatic workflow generates a .pdf file and sends it to the customer to prevent the mail server from limit errors
    sleep(5);
}

/**
 * Function to create a new Invoice using the given Sales Order id
 *
 * @param int    $salesOrderId
 * @param string $recurringDate
 *
 * @return void
 */
function createInvoice(int $salesOrderId, string $recurringDate = '')
{
    global $current_user, $log;

    if (!$recurringDate) {
        $recurringDate = date('Y-m-d');
    }

    if (!$current_user) {
        $current_user = Users::getActiveAdminUser();
    }

    $soFocus = CRMEntity::getInstance('SalesOrder');
    $soFocus->id = $salesOrderId;
    $soFocus->retrieve_entity_info($salesOrderId, 'SalesOrder');

    $focus = CRMEntity::getInstance('Invoice');
    $focus->column_fields['salesorder_id'] = $salesOrderId;

    foreach ($soFocus->column_fields as $fieldName => $fieldValue) {
        if (isset($focus->column_fields[$fieldName])) {
            $focus->column_fields[$fieldName] = decode_html($fieldValue);
        }
    }

    $focus->id = '';
    $focus->mode = '';
    $focus->column_fields['invoicestatus'] = 'Auto Created';
    $focus->column_fields['invoicedate'] = $recurringDate;

    $dueDuration = decidePaymentDuration($soFocus->column_fields['payment_duration']);
    [$y, $m, $d] = explode('-', $recurringDate);
    $focus->column_fields['duedate'] = date('Y-m-d', mktime(0, 0, 0, $m, $d + $dueDuration, $y));

    $focus->_salesorderid = $salesOrderId;
    $focus->column_fields['source'] = 'RECURRING INVOICE';

    try {
        $focus->save('Invoice');
    } catch (Exception $e) {
        $log->error($e->getMessage());
    }
}

/**
 * @param string $recurringDate
 * @param string $recurringFrequency
 *
 * @return array{validDate: string, nextRecurringDate: string}
 */
function getRecurringDate(string $recurringDate, string $recurringFrequency): array
{
    [$y, $m, $d] = explode('-', $recurringDate);
    $period = false;

    switch (strtolower($recurringFrequency)) {
        case 'daily':
            $period = '+1 day';
            break;
        case 'weekly':
            $period = '+1 week';
            break;
        case 'monthly':
            $m = $m + 1;
            break;
        case 'quarterly':
            $m = $m + 3;
            break;
        case 'every 4 months':
            $m = $m + 4;
            break;
        case 'half-yearly':
            $m = $m + 6;
            break;
        case 'yearly':
            $y = $y + 1;
            break;
    }

    if ($period !== false) {
        $nextRecurringDate = $validNextRecurringDate = date('Y-m-d', strtotime($period, mktime(0, 0, 0, $m, $d, $y)));
    } else {
        if ($m > 12) {
            $m = $m - 12;
            $y = $y + 1;
        }

        if (strlen($m) === 1) {
            $m = "0$m";
        }

        $nextRecurringDate = $validNextRecurringDate = "$y-$m-$d";

        if (!checkdate($m, $d, $y)) {
            $validNextRecurringDate = date('Y-m-d', mktime(0, 0, 0, $m, cal_days_in_month(CAL_GREGORIAN, $m, $y), $y));
        }
    }

    return ['validDate' => $validNextRecurringDate, 'nextRecurringDate' => $nextRecurringDate];
}

/**
 * Determines the payment duration based on the provided input string.
 *
 * @param string $paymentDuration A string containing the payment duration, typically in numeric form.
 *
 * @return int The extracted numeric payment duration. If no valid number is found, a default value of 15 is returned.
 */
function decidePaymentDuration(string $paymentDuration): int
{
    if (preg_match('/\d+/', $paymentDuration, $matches)) {
        return (int)$matches[0];
    }

    return 15;
}