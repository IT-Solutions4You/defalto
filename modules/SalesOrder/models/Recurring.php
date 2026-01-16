<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class SalesOrder_Recurring_Model
{
    /**
     * Handles recurring events from a particular SalesOrder.
     * Based on the current date, finds the matching SalesOrders and runs the script that creates a new entity.
     *
     * @return void
     * @throws Exception
     */
    public static function run(): void
    {
        $db = PearDatabase::getInstance();
        $log = Logger::getLogger('RecurringInvoice');
        $log->debug('invoked RecurringInvoice');

        $currentDate = date('Y-m-d');
        $currentDateStrTime = strtotime($currentDate);

        $sql = 'SELECT vtiger_salesorder.salesorderid, recurring_frequency, start_period, end_period, next_recurring_date,
		 payment_duration, recurring_module FROM vtiger_salesorder
		 INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0
		 INNER JOIN vtiger_invoice_recurring_info ON vtiger_salesorder.salesorderid = vtiger_invoice_recurring_info.salesorderid
		 WHERE
		    sostatus != "Cancelled"
		    AND DATE_FORMAT(start_period, "%Y-%m-%d") <= ?
		    AND (DATE_FORMAT(end_period, "%Y-%m-%d") >= ? OR end_period = "0000-00-00" OR end_period = "" OR end_period IS NULL)
		    AND DATE_FORMAT(next_recurring_date, "%Y-%m-%d") <= ?
		 ORDER BY salesorderid
		 LIMIT 50';
        $result = $db->pquery($sql, [$currentDate, $currentDate, $currentDate]);

        while ($row = $db->fetchByAssoc($result)) {
            $salesOrderId = (int)$row['salesorderid'];
            $startPeriod = $row['start_period'];
            $endPeriod = $row['end_period'];
            $recurringDate = $row['next_recurring_date'];
            $recurringFrequency = $row['recurring_frequency'];
            $recurringModule = $row['recurring_module'] ?? 'Invoice';

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
            $paymentDuration = self::decidePaymentDuration($row['payment_duration']);

            if ($recurringDateStrTime < $currentDateStrTime) {
                $recurringDatesList = [];
                $nextRecurringDate = $validNextRecurringDate = $recurringDate;

                while (strtotime($validNextRecurringDate) <= $currentDateStrTime && $currentDateStrTime <= $endDateStrTime) {
                    $recurringDatesList[] = $validNextRecurringDate;
                    $nextRecurringDatesInfo = self::getRecurringDate($nextRecurringDate, $recurringFrequency);
                    $validNextRecurringDate = $nextRecurringDatesInfo['validDate'];
                    $nextRecurringDate = $nextRecurringDatesInfo['nextRecurringDate'];
                }

                if ($recurringDatesList) {
                    foreach ($recurringDatesList as $recurringDateFromList) {
                        self::dispatchRecurring($salesOrderId, $recurringDateFromList, $paymentDuration, $recurringModule);
                    }

                    $db->pquery(
                        'UPDATE vtiger_invoice_recurring_info SET next_recurring_date = ? WHERE salesorderid = ?',
                        [$validNextRecurringDate, $salesOrderId]
                    );
                }
            } elseif ($recurringDateStrTime == $currentDateStrTime && $recurringDateStrTime <= $endDateStrTime) {
                self::dispatchRecurring($salesOrderId, $recurringDate, $paymentDuration, $recurringModule);

                $nextRecurringDatesInfo = self::getRecurringDate($recurringDate, $recurringFrequency);
                $nextRecurringDate = $nextRecurringDatesInfo['validDate'];
                $db->pquery('UPDATE vtiger_invoice_recurring_info SET next_recurring_date = ? WHERE salesorderid = ?', [$nextRecurringDate, $salesOrderId]);
            }

            // Add some free time for the case when automatic workflow generates a .pdf file and sends it to the customer to prevent the mail server from limit errors
            sleep(5);
        }
    }

    /**
     * Determines module and class. Then runs the handling script.
     *
     * @param int $salesOrderId
     * @param string $recurringDate
     * @param int $paymentDuration
     * @param string $recurringModule
     *
     * @return void
     */
    private static function dispatchRecurring(int $salesOrderId, string $recurringDate, int $paymentDuration, string $recurringModule): void
    {
        $log = Logger::getLogger('RecurringInvoice');

        $recurringModule = $recurringModule ?: 'Invoice';
        $moduleModel = Vtiger_Module_Model::getInstance($recurringModule);

        if (!$moduleModel || !$moduleModel->isActive()) {
            $log->info('Recurring module is not active: ' . $recurringModule);

            return;
        }

        $handlerClass = $recurringModule . '_SORecurring_Model';

        if (!class_exists($handlerClass)) {
            $log->info('Recurring handler class not found: ' . $handlerClass);

            return;
        }

        if (!is_callable([$handlerClass, 'run'])) {
            $log->info('Recurring handler method missing: ' . $handlerClass . '::run');

            return;
        }

        $handlerClass::run($salesOrderId, $recurringDate, $paymentDuration);
    }

    /**
     * @param string $recurringDate
     * @param string $recurringFrequency
     *
     * @return array{validDate: string, nextRecurringDate: string}
     */
    private static function getRecurringDate(string $recurringDate, string $recurringFrequency): array
    {
        [$y, $m, $d] = explode('-', $recurringDate);
        $y = (int)$y;
        $m = (int)$m;
        $d = (int)$d;
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
                $m = '0' . $m;
            }

            $nextRecurringDate = $validNextRecurringDate = $y . '-' . $m . '-' . $d;

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
    private static function decidePaymentDuration(string $paymentDuration): int
    {
        if (preg_match('/\d+/', $paymentDuration, $matches)) {
            return (int)$matches[0];
        }

        return 15;
    }
}