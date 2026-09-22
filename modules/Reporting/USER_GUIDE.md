# Defalto Reports – User Guide

Reports let you display selected CRM data, narrow the results with filters, and prepare an overview for further work or export. Summary reports also organize records into groups and display the results in a chart.

This guide uses the English labels shown in the application.

## 1. Choose a report type

| Type | Purpose | Example |
| --- | --- | --- |
| **Tabular** | A list of individual records with selected columns, filters, and optional calculations. | Invoices with their due dates and amounts. |
| **Summary** | Records organized into groups, with group calculations and a chart. | Total invoice amounts by status, or invoice counts by organization. |

The **Grouping** and **Charts** tabs are available in Summary reports. Use Tabular if you only need a list without a chart.

## 2. Create a report

1. Open the **Reporting** module.
2. Click the option to add a new record.
3. Select the **Report Type**.
4. Select the **Primary module** containing the records you want to report on, such as Invoice.
5. Click **Select**.
6. Complete the settings on each tab. Click a tab name or use **Back / Next** to move between tabs.
7. Review the preview and click **Save**.

For your first Summary report, follow this order: **Details → Columns → Calculations → Grouping → Charts → Filters → Sharing**.

## 3. Details

| Setting | How to use it |
| --- | --- |
| **Report Name** | Enter a descriptive name, such as “Invoices by status”. This is required. |
| **Folder** | Optionally select a folder to organize your reports. |
| **Description** | Briefly explain the purpose of the report or its filters. |
| **Max Entries** | In the current version, this field does not limit the records loaded. Use filters to narrow the results. |
| **Currency** | Select the report currency when working with monetary values. |

### Working with currencies

Select a report currency to view monetary values in a common currency. To keep calculations separate by their original currencies, enable **Keep calculations in original currencies**. Depending on the interface labels, this may appear as **Group calculations by currency**.

Enabling this option disables the common currency selector. When comparing results, check whether the amounts use one currency or are shown separately by currency.

## 4. Columns

Columns determine which values appear in the results table.

1. Review the default columns for the primary module.
2. Use **Click here to add column** to add another column.
3. Select the field you need. The available options may include related module fields, such as the organization name on an invoice.
4. Use the column menu to change its label, move it, or remove it.
5. Set ascending or descending sorting if you need a particular record order.

Changing a column label changes its name in the report. It does not rename the field throughout the CRM. Removing a column from a report does not delete the source data.

**Example invoice columns:** Invoice No, Subject, Organization Name, Status, Due Date, Grand Total, and Assigned To.

To calculate a sum or average for a field, first add that numeric field to the columns.

## 5. Calculations

Use the **Calculations** tab to select what the report should calculate. You can enable more than one calculation for a numeric column.

| Calculation | Meaning | Example |
| --- | --- | --- |
| **Count records** | Counts records. Does not require a numeric column. | Number of invoices with each status. |
| **Sum** | Adds the values of a selected field. | Total invoice amount. |
| **Avg** | Calculates the average value. | Average invoice amount. |
| **Min** | Shows the lowest value. | Lowest invoice amount. |
| **Max** | Shows the highest value. | Highest invoice amount. |

In a Summary report, calculations also provide group results and the available values for the chart's Y axis.

> **Important:** Adding a numeric column does not enable a calculation. Select a checkbox such as **Sum** for the required field, or enable **Count records**. You can then select that calculation for the Y axis.

## 6. Grouping – Summary reports

Grouping organizes records by a selected field.

1. Open the **Grouping** tab.
2. Select at least one field in **Group By**.
3. Add more grouping fields if needed.
4. For a date field, select an available interval, such as month or year.

**Examples:**

- **Status:** separate invoice groups for each status.
- **Organization Name:** an invoice overview by customer.
- **Invoice Date + month:** a monthly invoice overview.

The grouping selection determines the chart's X axis. Configure the X axis through the Grouping tab rather than typing a value on the Charts tab.

Use the arrow beside a group name to collapse or expand its records. **(Empty)** means that some records have no value in the grouping field.

## 7. Charts – Summary reports

Select at least one calculation and one grouping field before configuring a chart.

1. Open the **Charts** tab.
2. Select a **Chart Type**.
3. Set the **Chart Position** above or below the table.
4. Check the **X axis**, which follows your grouping settings.
5. Select a calculation for the **Y axis**, such as **Grand Total (Sum)** or **Count records**.
6. Review the preview.

| Chart type | Suitable use |
| --- | --- |
| **Bar** | Comparing groups, such as amounts by status. |
| **Line** | Showing changes over time, such as monthly results. |
| **Pie** | Showing each group's share of a total. |
| **Doughnut** | Showing shares of a total in a ring layout. |

You can select multiple available calculations for the Y axis. Start with one to keep the chart easy to read. Disabling a calculation on the Calculations tab, or removing its column, removes the corresponding Y-axis option.

### Why is the Y-axis selector disabled?

The selector is disabled when no calculation is enabled. A message explains that you must first select a calculation.

**Solution:** Open **Calculations**, enable **Count records** or a calculation such as **Sum** for a numeric field, then return to **Charts**. The Y-axis selector becomes available.

## 8. Filters

Filters determine which records are included in the report.

1. Click **Add Condition**.
2. Select a field, an operator, and a value.
3. Add more conditions as needed.
4. Check that the results match your requirements.

| Condition group | How it works |
| --- | --- |
| **All Conditions** | A record must satisfy every condition in this group. |
| **Any Conditions** | A record must satisfy at least one condition in this group. |

If you use both groups, a record must satisfy every condition in All Conditions and at least one condition in Any Conditions.

**Example:** To include invoices for a specific period, set both the start and end date conditions under All Conditions. Choose the date field that matches your purpose: invoice date and due date represent different events.

## 9. Sharing

On the **Sharing** tab, check the report owner in **Assigned To** and select the main sharing option.

| Option | Purpose |
| --- | --- |
| **Private** | Does not add other members through report sharing. |
| **All** | Makes the report available to all users through its sharing settings. |
| **Selected** | Lets you choose specific members, such as users, groups, or roles. |

For Selected sharing, choose at least one member. Report sharing grants viewing access; it does not itself grant permission to edit the report. Overall access also depends on CRM permissions.

## 10. Preview, save, and export

### Preview while editing

A preview appears below the form once the required settings are complete. It updates when settings change. You can also click **Load data**.

Use the preview to check the data, configuration, and appearance. Save the report when you finish editing; exports use the saved settings.

Use **Column widths** to adjust the table layout. Scroll horizontally to view wider tables.

### Save

Click **Save**. If the button is disabled, review the marked tabs and their messages. Common missing settings include the report name, a column, currency, grouping, a Y-axis calculation, or sharing members.

After saving, open the report and check the results against the saved filters. To change the report, open its edit view and save your changes again.

### Export

From the saved report's detail view, use:

- **Export XLS** for a spreadsheet you can work with in Excel.
- **Export PDF** for a document suitable for reading and sharing.

Save your changes and check the filters and currency before exporting. Exports use the saved report.

## 11. Example: total invoice amounts by status

1. Create a **Summary** report with **Invoice** as the primary module.
2. Name it **Invoices by status** and check the currency.
3. Keep columns such as Invoice No, Organization Name, Status, and Grand Total.
4. On **Calculations**, enable **Sum** for Grand Total.
5. On **Grouping**, select **Status**.
6. On **Charts**, select a Bar chart and **Grand Total (Sum)** for the Y axis.
7. Add date filters if you only need a specific period.
8. Configure sharing, review the preview, and save the report.

The result is a table grouped by status and a chart showing the total amount for each group. To compare invoice counts instead of amounts, enable **Count records** and select it for the Y axis.

## 12. Troubleshooting

| Situation | What to check |
| --- | --- |
| **The Y-axis selector is grey and does not respond.** | Enable at least one calculation on the Calculations tab. |
| **A field is missing from Calculations.** | Add it to Columns. Sum, Avg, Min, and Max are available for numeric fields. |
| **The X axis is empty.** | Select a field on the Grouping tab. |
| **The chart has no data.** | Check grouping, the selected Y-axis calculation, and whether the filters return records. |
| **Grouping and Charts tabs are missing.** | These tabs are available for Summary reports. |
| **Save is disabled.** | Review the marked tabs and complete the missing settings. |
| **Fewer records appear than expected.** | Check the filters, primary module, and availability of source records. |
| **Amounts differ from expectations.** | Check the currency, grouping by original currency, selected field, and filters. |
| **A colleague cannot see the report.** | Check report sharing and their CRM permissions. |

**Before sharing a report:** check its name, primary module, columns, filters, calculations, currency, and sharing recipients. For a Summary report, also check grouping and both chart axes.
