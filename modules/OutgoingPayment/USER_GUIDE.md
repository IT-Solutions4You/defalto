# Outgoing Payments

Outgoing Payments records the money your company pays, for example to vendors for purchase orders. Every payment has its own number (PAYOUT0001, ...), amount with its currency, payment and due date, payment method and the document it pays.

## Paying a purchase order
1. Open the purchase order and click **Add Payment** (or **More → Add Payment** for the full form).
2. The form is prefilled with the open amount, the currency, the due date, the vendor and the contact. Change what you need and save.
3. The purchase order shows the payment in the Outgoing Payments widget; its **Paid** amount and **Balance** are updated. The status of the purchase order does not change.

## Statuses
- **Paid** – the payment date is today or in the past.
- **Waiting** – the payment date is in the future, or there is no payment date and the due date has not passed.
- **Pending** – there is no payment date and the due date has passed.
- **Created** – no dates.

A daily background job updates the statuses when the dates arrive. Only Paid payments count towards the paid amount of the document.

## Payments list
With the Payments list installed, purchase orders show the common **Payments** widget instead of the Outgoing Payments widget, and Outgoing Payments is not in the Sales menu: open the payments from **Payments**, from the quick create menu or from a document. Every payment shows the **Direction** Outgoing.

## Settings
Administrators choose the documents in **Settings → Outgoing Payments Integration**. Purchase orders are integrated by default.
