# Invoices – User Guide

Invoices are the billing records used to request payment for delivered products or services. An invoice can be linked to an organization, contact, opportunity, Sales Order, quote and purchase order and contains the items and amounts being billed.

## What you can do

- Create an invoice directly, or generate it from a Quote (**Generate Invoice**), a Sales Order (**Create Invoice**) or an Opportunity (**Create Invoice**).
- Add products and services with quantities, prices, discounts, and taxes (see the Inventory Items guide for how the item lines work).
- Record the invoice date, due date, customer number, and billing and shipping addresses.
- Distinguish a regular invoice from a **Final Invoice** or **Credit Invoice** where those options are available.
- Track the invoice with statuses: **AutoCreated**, **Created**, **Approved**, **Sent**, **Credit Invoice**, **Paid**, and **Cancel**.
- Review the amount received and the remaining balance.
- Export the invoice to PDF or send it by email with the PDF attached.
- Generate a Purchase Order from the invoice when you are allowed to create Purchase Orders.
- Keep related Assets, Documents, Emails, and Appointments on the invoice.

## Typical workflow

1. Generate an invoice from the related Quote, Sales Order or Opportunity, or create it in the Invoices module.
2. Confirm the customer, source documents, invoice date, and due date.
3. Verify all line items, discounts, taxes, currency, and the grand total.
4. Save the invoice and move it through **Approved** and **Sent**.
5. Send the PDF to the customer.
6. When payment is received, record the received amount and update the status to **Paid** when appropriate.

## Amounts and payment tracking

The invoice summary shows the subtotal, discounts, taxes, grand total, received amount, and balance. The balance is the amount still outstanding, so check it after every payment update.

## Statuses

- **AutoCreated** – the invoice was generated automatically by a recurring Sales Order. Its invoice date is the scheduled billing date and its due date is that date plus the Sales Order's payment duration.
- **Created**, **Approved**, **Sent** – the normal preparation and issuing steps. Use **Sent** to separate an invoice issued to the customer from one that is only prepared internally.
- **Credit Invoice** – an invoice issued as a credit.
- **Paid** – payment has been received.
- **Cancel** – the invoice is no longer valid.

## Read-only invoices

When an invoice is read-only (or you are not allowed to edit it), its item lines are locked as well. See the Inventory Items guide.

## Helpful tips

- Check the due date before sending the invoice.
- Keep the source Quote or Sales Order selected so the invoice can be compared with the agreed offer.
- Do not delete an incorrect invoice when your accounting process requires a traceable correction; use **Credit Invoice** or **Cancel** and issue a new invoice.
- Preview the PDF before sending it externally.
