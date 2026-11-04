# Sales Orders – User Guide

Sales Orders record what the customer has agreed to buy and provide the operational hand-off after a quote is accepted. A Sales Order can be linked to an organization, contact, opportunity, and quote and contains the confirmed products or services.

## What you can do

- Create a Sales Order directly or generate it from a Quote (**Generate Sales Order**) or an Opportunity (**Create Sales Order**).
- Confirm products or services, quantities, pricing, discounts, taxes, and totals (see the Inventory Items guide for how the item lines work).
- Record the order date, due date, carrier, the customer's purchase order number, billing and shipping addresses, and terms and conditions.
- Track order progress with statuses: **Created**, **Approved**, **Delivered**, or **Cancelled**.
- Export the order to PDF or send it by email with the PDF attached.
- Create an Invoice (**Create Invoice**) or a Purchase Order (**Create Purchase Order**) from the Sales Order; each is shown only when you are allowed to create that record type.
- See the Invoices created from the order in the **Invoice** related list, and keep Documents, Emails, and Appointments on the order.
- Enable recurring invoicing when the order represents a repeating service.

## Typical workflow

1. Create a Sales Order from the accepted quote, opportunity, or Sales Orders module.
2. Verify the customer, contact, source quote, order date, due date, and purchase order number.
3. Review every line item and the financial summary.
4. Save the order and update its status as it is approved and delivered.
5. Create an Invoice when the order should be billed.
6. For recurring services, enable recurring invoicing and verify the schedule before saving.

## Recurring invoicing

Recurring invoicing is useful for subscriptions, maintenance, and other repeating services. Tick **Enable Recurring** and fill in:

- **Frequency** – Daily, Weekly, Monthly, Quarterly, Half-Yearly, or Yearly.
- **Start Period** and **End Period** – the billing window. Without an end date, billing stops after ten years.
- **Payment Duration** – Net 7, 10, 14, 15, 30, 45, 60, or 90 days; it sets the due date of each generated invoice.

**Next Invoice Date** is filled in by the system: it starts at the Start Period (and is reset when the Start Period changes) and moves forward after each invoice. It cannot be edited.

Monthly and longer calendar-based schedules keep the day from the Start Period. If a month does not contain that day, the invoice is created on that month's last day and the original schedule day is restored in the next month that supports it. For example, a monthly schedule starting on 31 January runs on 28 February and then 31 March.

A scheduled job creates an invoice with the status **AutoCreated** on each billing date, copying the order's customer and items. Orders with the status **Cancelled** are skipped. Switching recurring off removes the schedule. Review the schedule whenever the order period or billing arrangement changes.

## Read-only orders

When a Sales Order is read-only (or you are not allowed to edit it), its item lines are locked as well. See the Inventory Items guide.

## Helpful tips

- Treat an approved Sales Order as the agreed commercial instruction for fulfilment and billing.
- Keep the related Quote selected so users can compare the order with the accepted offer.
- Use **Delivered** only when the delivery or service milestone has actually been completed.
- Decide whether the order is billed once or recurrently before creating the first invoice.

## Restricted conversion

If creating other documents from this sales order is restricted (for example by a workflow while the sales order waits for approval), the options to create an invoice or purchase order are hidden and opening them directly shows the reason. The restriction is listed in the **Restricted actions** box of the record.
