# Incoming Payments – User Guide

## What the module does

Incoming Payments records the money your company receives for invoices (and, when enabled, sales orders). Every payment is a record with its own number (PAYIN0001, PAYIN0002, ...), amount, currency, dates, payment method and a link to the document it pays. The module keeps the paid amount, the balance and the status of the linked document up to date automatically.

## Creating a payment

1. Open the invoice and click **Add Payment** in the detail view for the quick form, or **More → Add Payment** for the full form with all fields. The **Add** button in the **Incoming Payments** widget of the summary view opens the quick form too; after saving, reload the page to see the new invoice status in the header.
2. The form is prefilled with the open amount of the invoice, its currency, the due date, the organization and the contact. Change what you need, for example when only a part of the invoice was paid.
3. Fill in the **Payment Date** when the money arrived. Leave it empty when you only expect the payment.
4. Save. The payment is listed in the **Incoming Payments** widget and related list of the invoice.

You can also create payments directly in the Incoming Payments module and pick the invoice in the **Reference** field: choose the module next to the field, type at least two characters of the invoice number or name, or use the magnifier to pick it from a list. A payment references one document; saving a payment with more documents shows a message. The **Reference** filter in the payment list finds payments by the document number or name.

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
- When you delete an invoice that has payments, the confirmation tells you how many payments are linked. The payments stay, only their link to the deleted invoice is removed, and their **Reference** becomes empty. Restoring the invoice from the recycle bin does not link the payments again.

## Other fields

- **Amount in Base Currency** is calculated automatically from the exchange rate of the payment currency. The rate is taken when the payment is created or its currency changes, so editing an old payment does not change its value.

## Settings

Administrators configure the integration in **Settings → Incoming Payments Integration** (also available from the gear menu of the Incoming Payments list). The **Underpayment threshold** at the top applies to all modules: an invoice whose balance is at most this amount counts as paid, for example 1 to ignore small differences. The amount is compared in the invoice currency without conversion; 0 means only fully paid invoices. Changing it updates the statuses of all invoices with payments. For each document module you choose whether it is active, which status field and values are used when the document becomes fully paid or unpaid again, and which fields hold the organization, the total, the paid amount and the balance. Every inventory module is listed automatically, including modules added later: quotes, sales orders, purchase orders and invoices today. Invoices are active by default, the others can be switched on. Status values are empty for them until you choose them, so their status does not change on payment until then.

## Tips

- Create one payment per received transfer. When a transfer pays several invoices, create one payment per invoice for now.
- Use the **Transaction ID** field for the bank reference so payments are easy to match with the bank statement.
- Filters on **Payment Status** and **Due Date** show expected and overdue payments.
- Payments created by Import get their status and update their invoices the same way as payments entered by hand.
- After changing the payment date directly in the detail view, reload the page to see the new status in the header.
