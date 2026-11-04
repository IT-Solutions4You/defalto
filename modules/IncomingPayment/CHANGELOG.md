# IncomingPayment changelog

## 1.0 - 2026-10-29

- New module for recording incoming payments with automatic numbering, statuses derived from the payment and due dates and base currency amounts.
- Payments can be created directly from an invoice with the open amount, currency, due date, organization and contact prefilled, in a quick form from the Add Payment button or in the full form from the More menu.
- Invoices show an Incoming Payments widget, a related list and an Add Payment button; their received amount, balance and status are updated automatically from the received payments.
- The detail view of a payment lists its documents in a tab for every integrated module.
- Organizations, vendors and contacts show their incoming payments in a related list.
- Deleting an invoice with payments warns about the linked payments and removes the link from them; a restored invoice recalculates its received amount and balance without them.
- Integration into document modules is configured in Settings → Incoming Payments Integration, which lists every inventory module automatically.
- An underpayment threshold in the settings marks documents with a small remaining balance as paid while the balance keeps the real amount.
- A daily job updates payment statuses when payment or due dates arrive.
