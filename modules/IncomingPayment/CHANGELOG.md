# IncomingPayment changelog

## 1.0 - 2026-11-25

- New module for recording incoming payments with automatic numbering, statuses derived from the payment and due dates and base currency amounts.
- Payments can be created directly from an invoice with the open amount, currency, due date, organization and contact prefilled, in a quick form from the Add Payment button or in the full form from the More menu.
- Invoices show an Incoming Payments widget, a related list and an Add Payment button; their received amount, balance and status are updated automatically from the received payments.
- The detail view of a payment lists its documents in a tab for every integrated module.
- Proforma invoices: a tax document counts the payments of its proforma, a final invoice with the advance deducted by an item is paid when the advance is paid, and payments keep the Invoiced, Canceled and Cancel statuses and statuses changed while saving a document.
- Organizations, vendors and contacts show their incoming payments in a related list.
- Deleting an invoice with payments warns about the linked payments and removes the link from them; a restored invoice recalculates its received amount and balance without them.
- Every payment shows its direction (Incoming or Outgoing) in a read-only field.
- With the Payments list installed, documents show the common Payments widget instead of the Incoming Payments widget and the module is hidden in the Sales menu.
- Refunds: payments of this module integrated into a document paid by Outgoing Payments are refunds; they are subtracted from the paid amount, the Settings page has a Refund column and documents offer Add Refund.
- Add Payment from a document prefills the total minus all payments, including the ones not received yet, as Cashflow did.
- Received payments of a sales order count on the invoices created from it (same organisation, split by due date).
- Opportunities (Potentials) can be integrated in the settings: total from Amount, due date from the expected close date.
- One payment can pay several documents: the amount is split by due date and can be changed per document in the payment form.
- Bulk payment of documents selected in a list (Pay selected): one payment for one organization, Pay Off for documents paid first, amounts per document, different currencies.
- Integration into document modules is configured in Settings → Incoming Payments Integration, which lists every inventory module automatically.
- An underpayment threshold in the settings marks documents with a small remaining balance as paid while the balance keeps the real amount.
- With the Payments list installed, the header of a payment shows Payments and leads back to the Payments list.
- The daily system task PaymentStatus, shared by all payment modules, updates payment statuses when payment or due dates arrive.
- The currency of a payment is chosen with the currency symbol inside the Amount field; searching, filtering and sorting by amount compare the amounts in the base currency.
- The amount of a payment must be greater than zero; money returned is entered as a refund in the other payment module.
