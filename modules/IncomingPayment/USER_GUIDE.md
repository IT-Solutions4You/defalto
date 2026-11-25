# Incoming Payments – User Guide

## What the module does

Incoming Payments records the money your company receives for invoices (and, when enabled, sales orders). Every payment is a record with its own number (PAYIN0001, PAYIN0002, ...), amount, currency, dates, payment method and a link to the document it pays. The module keeps the paid amount, the balance and the status of the linked document up to date automatically.

## Creating a payment

1. Open the invoice and click **Add Payment** in the detail view for the quick form, or **More → Add Payment** for the full form with all fields. The **Add** button in the **Incoming Payments** widget of the summary view opens the quick form too; after saving, reload the page to see the new invoice status in the header.
2. The form is prefilled with the open amount of the invoice, its currency, the due date, the organization and the contact. Change what you need, for example when only a part of the invoice was paid. The amount must be greater than zero; money you return is a refund (see below).
3. Fill in the **Payment Date** when the money arrived. Leave it empty when you only expect the payment.
4. Save. The payment is listed in the **Incoming Payments** widget and related list of the invoice. Organizations, vendors and contacts also have an **Incoming Payments** related list with the payments that name them.

You can also create payments directly in the Incoming Payments module and pick the invoice in the **Reference** field: choose the module next to the field, type at least two characters of the invoice number or name, or use the magnifier to pick it from a list. Choosing the document does not fill in the organization or the contact; enter them yourself when you need them. A payment can reference several documents (see below). The **Reference** filter in the payment list finds payments by the document number or name.

The detail view of a payment has a tab for every module integrated in Settings, for example **Invoices**, listing the documents the payment is applied to. To change the document, edit the **Reference** field.

## Statuses

The status is calculated from the dates and cannot be edited:

| Status | Meaning |
|---|---|
| Created | No payment date and no due date |
| Waiting | The payment date is in the future, or no payment date and the due date has not passed yet |
| Pending | No payment date and the due date has passed |
| Received | The payment date is today or in the past |

Only **Received** payments count towards the paid amount of the invoice. A daily background job moves payments from Waiting to Received when their payment date arrives, and from Waiting to Pending when the due date passes.

## What happens on the invoice

- **Received** shows the sum of received payments in the invoice currency; payments in other currencies are converted with their exchange rate.
- **Balance** is the invoice total minus the received amount. An overpaid invoice shows a negative balance.
- When the balance reaches zero, or drops to the underpayment threshold set in Settings, the invoice status changes to **Paid**. The balance still shows the real underpayment. If a payment is deleted or its date moves to the future, the status returns to **Created**.
- While the integration is active, the **Received** field of the invoice is read-only; edit the payments instead.
- When you delete an invoice that has payments, the confirmation tells you how many payments are linked. The payments stay, only their link to the deleted invoice is removed, and their **Reference** becomes empty. Restoring the invoice from the recycle bin does not link the payments again; its received amount, balance and status are recalculated without them.

## Other fields
- **Direction** is always Incoming; the Payments list uses it to tell incoming from outgoing payments.

- **Amount** holds the currency of the payment: click the currency symbol inside the field to choose another currency. A new payment starts with your currency, or with the currency of the invoice when it is created from an invoice.
- **Amount in Base Currency** is calculated automatically from the exchange rate of the payment currency. The rate is taken when the payment is created or its currency changes, so editing an old payment does not change its value. Searching, filtering and sorting by **Amount** compare the amounts in the base currency, so payments in different currencies are ordered correctly.

## Settings

Administrators configure the integration in **Settings → Incoming Payments Integration** (also available from the gear menu of the Incoming Payments list). The **Underpayment threshold** at the top applies to all modules: an invoice whose balance is at most this amount counts as paid, for example 1 to ignore small differences. The amount is compared in the invoice currency without conversion; 0 means only fully paid invoices. Changing it updates the statuses of all invoices with payments. For each document module you choose whether it is active, which status field and values are used when the document becomes fully paid or unpaid again, and which fields hold the organization, the total, the paid amount and the balance. Every inventory module is listed automatically, including modules added later: quotes, sales orders, purchase orders and invoices today. Invoices are active by default, the others can be switched on. Status values are empty for them until you choose them, so their status does not change on payment until then.

## One payment for several invoices
- Add more invoices to the **Reference** field of a payment. Below the field the payment form shows the amount for each invoice: the invoice with the earliest due date gets its open amount first, the last one gets the rest. You can change the amounts; what is not split goes to the last invoice when you save.
- **Pay selected** in the invoice list creates one payment for the selected invoices. They must belong to one organization. In the window enter the amount, currency and payment date; mark **Pay off** for invoices that should be paid first and change the amounts per invoice if needed.

## Sales orders and opportunities
- A payment of a sales order also counts on the invoices created from that order (field Sales Order on the invoice), when they have the same organization. With several invoices the order payments go to the invoice with the earliest due date first, each invoice gets at most its open amount and the last one gets the rest. The invoice widget shows them as **Payments of SO…**. The sales order must be switched on in the settings.
- Opportunities can be switched on in **Settings → Incoming Payments Integration**: the total is the opportunity Amount, the due date of a new payment is the expected close date. The Received and Balance fields are created on the opportunity.

## Proforma invoices
- Proforma invoices can be switched on in **Settings → Incoming Payments Integration**. They get the Received and Balance fields and the Paid status when they are paid.
- A **tax document** created from a paid proforma (Invoice Type Tax Document) counts the proforma payments up to the proforma total, so it is paid as soon as the advance is received.
- A **final invoice** deducts the proforma total by a minus item. The proforma payments are therefore not counted again; only what was paid above the proforma total counts. The widget shows the advance as **Advance deducted by an item, paid on PINV…**. When all deducted proformas are paid and the rest of the invoice is paid too (or the invoice total is 0), the invoice gets the paid status.
- Payments never change the statuses Invoiced, Canceled and Cancel, and a status you or an extension change while saving a document stays as it is; only its paid amount and balance are recalculated.

## Refunds
- Money you return to a customer is an outgoing payment on the invoice: click **Add Refund**. The form is prefilled with the paid amount. A received refund lowers the **Received** amount of the invoice, and a fully paid invoice can go back to its unpaid status.
- Money a vendor returns on a purchase order is an incoming payment marked as a refund in **Settings → Incoming Payments Integration** (column **Refund**, purchase orders by default). Each document must be paid by exactly one payment module: the settings refuse to save a document marked as a refund in both modules, or paid by both.
- **Add Payment** prefills the total minus all payments of the invoice, also the ones not received yet.

## Tips
- With the Payments list installed, the invoice shows the common **Payments** widget with incoming and outgoing payments instead of the Incoming Payments widget, and Incoming Payments is not in the Sales menu: open the payments from **Payments**, from the quick create menu or from a document.

- Create one payment per received transfer. When a transfer pays several invoices, put all of them into the Reference field or use **Pay selected** in the invoice list.
- Use the **Transaction ID** field for the bank reference so payments are easy to match with the bank statement.
- Filters on **Payment Status** and **Due Date** show expected and overdue payments.
- Payments created by Import get their status and update their invoices the same way as payments entered by hand.
- After changing the payment date directly in the detail view, reload the page to see the new status in the header.
- When the Payments list is installed, the top of a payment shows Payments, and clicking it opens the Payments list with your last filter, page and sorting.
