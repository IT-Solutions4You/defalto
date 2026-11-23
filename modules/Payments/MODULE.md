# Payments

## Purpose
Payments is one list of the records of all payment modules (IncomingPayment, later OutgoingPayment). It has no records, fields, tables or edit/detail views of its own (`isentitytype = 0`, class `Payments extends CRMExtension`). Payments are created and edited in their own module.

## Runtime owner and entry points
- `modules/Payments/helpers/Sources.php`: `Payments_Sources_Helper` is the single list of sources (module, direction, optional source condition, fields treated as missing). The sources are IncomingPayment and OutgoingPayment (since phase 4), without conditions or missing fields.
- `modules/Payments/models/ListView.php`: `Payments_ListView_Model` overrides the headers, entries, count, basic links (one add button per payment module) and mass actions (none).
- `modules/Payments/models/UnionQuery.php`: `Payments_UnionQuery_Model` joins the source queries with `UNION ALL`. The union only decides the order, the page and the count.
- `modules/Payments/models/SourceQuery.php`: `Payments_SourceQuery_Model` (a `Core_QueryGenerator_Model` of the payment module) applies the Payments filter, the list search, the source condition and the permissions of its module. It returns the union part (record id + sort value) through the projection columns of `Core_QueryGenerator_Model`.
- `modules/Payments/models/QueryGenerator.php`: `Payments_QueryGenerator_Model` is the generator `Vtiger_ListView_Model::getQueryGeneratorForModule()` creates for Payments. It is built on the primary payment module and remembers the Payments filter id.
- `modules/Payments/models/Module.php`: `Payments_Module_Model` returns the blocks of the primary payment module and the fields of all payment modules (a shared field once). The filter editor, the column choice and the list search therefore offer payment fields.
- `modules/Payments/views/Widget.php`: `Payments_Widget_View::showPayments` is the common payments widget on documents (template `PaymentsWidget.tpl`, add buttons `SummaryWidgetHeaderActions.tpl`). Its registration and data are in Core (`Core_PaymentWidget_Model`, `Core_PaymentWidgetSummary_Helper`, see `modules/Core/MODULE.md`, Payments base).
- `modules/Payments/actions/MassDelete.php`: `Payments_MassDelete_Action` deletes each selected payment in its own module (`Delete` permission per record, skipped records in the message). It requires only access to Payments, because Payments itself has no Delete permission.
- `modules/Payments/actions/ExportData.php` and `models/SourceExport.php`: the export takes the rows from the union in list order (all rows of the filter without the list search, the current page, or the selection), lets `Payments_SourceExport_Model` (a `Vtiger_ExportData_Action` that keeps the result instead of sending it) export the records of each payment module with the core field choice and formatting, and joins the columns by field name. A field only one module has is empty in the other rows.
- `Payments_ListView_Model::getInstanceForRequest()` and `getSelectedRows()` give mass actions and the export the same filter, search and order as the list; `selected_ids = all` takes every row of the union except `excluded_ids`.
- `modules/Payments/helpers/WebserviceOperation.php`: the webservice handler of Payments, registered in `vtiger_ws_entity` by the install model. Core code (for example `new CustomView('Payments')` in `CustomView_Record_Model::getSavedConditionCount()`) loads the webservice metadata of the list module. The handler returns the metadata of the primary payment module and refuses every write operation.

## Data flow of the list
1. For every accessible source a `Payments_SourceQuery_Model` loads the Payments filter (`initForCustomViewById`), the list search (`parseAdvFilterList`) and the simple search. Filters and searches work by field name (`QueryGenerator::initForCustomViewById()` uses `$details[2]`), and payment modules share their field names.
2. A condition on a field the source does not have is replaced by a constant condition on `createdtime` (`ny` = true, `y` = false). Otherwise the core would drop it silently. The replacement keeps the AND/OR structure. A missing field counts as empty: `y` and `k` hold, `e` holds for an empty value, `n` for a non-empty value, everything else fails.
3. The union orders by the sort value (`payment_amount` sorts by the base amount `payment_amount_base`, see `Payments_Sources_Helper::SORT_FIELDS`) and then by record id.
4. The rows of the page are loaded per source by a `Core_QueryGenerator_Model` of the module with `addRecordIdsCondition()`. They are formatted by that module's `ListViewController` and returned as record models of the payment module, so links open the payment module.

## Invariants
- Never create `CustomView` with the module name inside Payments code. Use `new CustomView()` and pass `Payments` to `getViewId()`.
- The install model keeps exactly one `vtiger_ws_entity` row: `vtws_addDefaultModuleTypeEntity()` adds a default row on every install/update, and `updateWebserviceHandler()` removes duplicates. `deleteModule()` removes the row, because the core leaves it behind.
- Filters of Payments are stored with `entitytype = 'Payments'` and columns of the payment module fields.

## Install
- `addCustomLinks()` registers the common widget on the documents and hides the payment modules in the menu once; `deleteCustomLinks()` gives the documents back the widgets of the payment modules and, except on an update, shows the payment modules in the menu again (`Core_PaymentMenu_Model`). `deleteModule()` runs it before the core drops the module.
- The default All filter starts with the direction (`Payments_Sources_Helper::LIST_FIELDS`); an update does not change an existing filter.

## Not done yet
A merged block list for several modules. A dedicated message when no payment module is active (the list shows the standard empty list). Global search does not include Payments, because it lists only entity modules.

## Validation
- `php -l` on touched files; `check_change.py` signature check.
- On the test instance: install via `index.php?module=Payments&view=Install&mode=install`. Compare `view=ListAjax&mode=getRecordsCount` with the IncomingPayment count. Compare the order of a page with the IncomingPayment list ordered by the same field.
- The module title on the detail of a payment leads here: `IncomingPayment_Module_Model::getCustomViewUrl()` returns the Payments list URL and `getBreadcrumbLabel()` the Payments label while Payments is available. OutgoingPayment must do the same (phase 3 moves it to the Core base).
- Status labels: the payment modules share one status picklist with the final value `Paid`; `languages/<locale>/Payments.php` translates the statuses for the filter and search of the Payments list; rows keep the labels of their payment module.
