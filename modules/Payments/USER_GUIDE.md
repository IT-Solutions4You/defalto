# Payments

Payments shows all your payments in one list: incoming payments from customers and outgoing payments to vendors.

## Working with the list
- Open **Payments** in the Sales menu.
- Use filters, column selection, the search row above the columns and sorting as in any other list. Saved filters are shared with nobody unless you share them.
- Click a payment to open it. It opens in its own module (Incoming Payments or Outgoing Payments), where you can edit or delete it.
- Use **Add Incoming Payment** or **Add Outgoing Payment** to create a new payment.
- The **Direction** column shows whether a payment is incoming (green) or outgoing (red); you can filter, search and sort by it.
- Select payments and choose **Delete** to delete them; each payment is deleted in its own module, documents are recalculated and payments you may not delete are skipped.
- **Export** in the list menu writes incoming and outgoing payments into one CSV or Excel file.

## Payments on a document
- Invoices, orders and other integrated documents show one **Payments** widget with the payments of all directions: direction, number, payment date, status and amount.
- Below the payments the widget shows the Total, Paid and Balance of the document and **Expected**, the payments that are not paid yet.
- On an invoice created from a sales order the widget also shows **Payments of SO…**: the part of the order payments that counts on this invoice.
- On a final invoice that deducts a proforma by a minus item the widget shows **Advance deducted by an item, paid on PINV…** with everything paid on the proforma; only the part above the proforma total counts in Paid. A tax document of a proforma shows the proforma payments as **Payments of PINV…**.
- A refund (money returned on the document) is marked **Refund** in the widget and lowers the Paid amount.
- **Add Payment** in the widget header opens the payment form prefilled from the document; when the document has refunds switched on you choose **Add Payment** or **Add Refund**.

## Menu
- With Payments installed, the Sales menu shows only Payments; Incoming Payments and Outgoing Payments are hidden there but still work (quick create, links, related lists). An administrator can show them again in the menu editor.

## Good to know
- Payments only lists payments. Creating, editing, deleting and importing happen in the payment modules.
- You see only the payments you are allowed to see in their own module.
- Global search finds a payment once, in its own module.
- All payment directions share the statuses Created, Waiting, Pending and Paid. A row shows the status as its payment module names it (an incoming payment shows Received); the status filter and search offer Paid.
