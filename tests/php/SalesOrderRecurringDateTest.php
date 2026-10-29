<?php
/**
 * Regression coverage for calendar-anchored Sales Order recurrence dates.
 */
require_once __DIR__ . '/../../modules/SalesOrder/models/Recurring.php';

$method = new ReflectionMethod(SalesOrder_Recurring_Model::class, 'getRecurringDate');

$cases = [
    ['2027-01-31', 'Monthly', '2027-01-31', '2027-02-28'],
    ['2027-02-28', 'Monthly', '2027-01-31', '2027-03-31'],
    ['2027-02-28', 'Monthly', '2027-01-30', '2027-03-30'],
    ['2027-02-28', 'Monthly', '2027-01-29', '2027-03-29'],
    ['2026-11-30', 'Quarterly', '2026-11-30', '2027-02-28'],
    ['2027-02-28', 'Quarterly', '2026-11-30', '2027-05-30'],
    ['2027-02-28', 'Yearly', '2024-02-29', '2028-02-29'],
    ['2027-01-31', 'Daily', '2027-01-29', '2027-02-01'],
];

foreach ($cases as [$recurringDate, $frequency, $startPeriod, $expectedDate]) {
    $result = $method->invoke(null, $recurringDate, $frequency, $startPeriod);

    if ($result['validDate'] !== $expectedDate) {
        throw new RuntimeException(sprintf(
            '%s from %s with anchor %s produced %s instead of %s',
            $frequency,
            $recurringDate,
            $startPeriod,
            $result['validDate'],
            $expectedDate
        ));
    }
}

echo "SalesOrder recurring date tests passed.\n";
