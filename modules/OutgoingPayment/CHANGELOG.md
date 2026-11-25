# OutgoingPayment changelog

## 1.0 - 2026-11-25

- New module for recording outgoing payments with automatic numbering (PAYOUT), statuses derived from the payment and due dates and amounts with their own currency.
- Payments can be created from a purchase order with the open amount, currency, due date, vendor and contact prefilled, in a quick form from the Add Payment button or in the full form from the More menu.
- Purchase orders show an Outgoing Payments widget, a related list and an Add Payment button; their paid amount and balance are updated from the paid payments.
- Vendors, organizations and contacts show their outgoing payments in a related list.
- Every payment shows its direction (Incoming or Outgoing) in a read-only field.
- With the Payments list installed, documents show the common Payments widget instead of the Outgoing Payments widget and the module is hidden in the Sales menu.
- Refunds: payments of this module integrated into a document paid by Incoming Payments are refunds; they are subtracted from the paid amount, the Settings page has a Refund column and documents offer Add Refund.
- Add Payment from a document prefills the total minus all payments, including the ones not received yet, as Cashflow did.
- Received payments of a sales order count on the invoices created from it (same organisation, split by due date).
- Opportunities (Potentials) can be integrated in the settings: total from Amount, due date from the expected close date.
- One payment can pay several documents: the amount is split by due date and can be changed per document in the payment form.
- Bulk payment of documents selected in a list (Pay selected): one payment for one organization, Pay Off for documents paid first, amounts per document, different currencies.
- Integration into document modules is configured in Settings → Outgoing Payments Integration.
- The amount of a payment must be greater than zero; money returned is entered as a refund in the other payment module.
- Credit notes are paid by outgoing payments by default (money returned to the customer); incoming payments on a credit note are its refunds.
- Payments keep the Invoiced, Canceled and Cancel statuses and a status changed while saving a document; the Payments widget labels advances deducted by an item of a final invoice.
- Undo Last Import of payments updates the paid amount, balance and status of their documents.
