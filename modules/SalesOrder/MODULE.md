# SalesOrder

## Runtime ownership

- `SalesOrder_Recurring_Model` owns recurring invoice scheduling. The `RecurringInvoice` scheduled task calls `run()` and dispatches each due occurrence to `<RecurringModule>_SORecurring_Model::run()`; Invoice is the default target.
- `RecurringInvoiceHandler` owns schedule reset and removal when a Sales Order is saved. A changed Start Period resets the next recurring date, and disabling recurrence removes its persisted schedule.
- `SalesOrder_Install_Model` owns Sales Order fields, related lists, links, and update migrations.

## Persistent data and invariants

- Sales Order recurrence settings are stored in `vtiger_invoice_recurring_info`, keyed by `salesorderid`. `start_period` is the schedule anchor and `next_recurring_date` is the next valid execution date.
- Calendar-based frequencies preserve the day from `start_period`. When that day is absent from a target month, the occurrence uses the month's last valid day without changing the schedule anchor.
- A missing end date is treated as ten years after the Start Period. Cancelled or deleted Sales Orders are not processed.

## Integration points

- The `RecurringInvoice` service is registered by the install schema and processes at most 50 due Sales Orders per run.
- Recurring target modules must be active and expose a callable `<Module>_SORecurring_Model::run(int $salesOrderId, string $recurringDate, int $paymentDuration)` method.
- User-facing recurrence fields are installed in the **Recurring Invoice Information** block. The Next Invoice Date is system-managed.

## Validation

- Run `php -l modules/SalesOrder/models/Recurring.php` after changing the scheduler.
- Run `php tests/php/SalesOrderRecurringDateTest.php` for calendar-anchor regression coverage.
