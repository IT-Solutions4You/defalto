{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <style>
        .renderedTable {
            width: 100%;
            border-collapse: collapse;
        }
        .renderedTable, .renderedTable tr, .renderedTable td, .renderedTable th {
            border: 1px solid #e3e7eb;
        }
        .renderedTable td, .renderedTable th {
            padding: 7px 9px;
        }
        .reportingTableHeaderCell {
            background: #f4f6f8;
            color: #6c757d;
            font-size: 10px;
            text-transform: uppercase;
        }
        .reportingGroupHeader td {
            background: #f7f8fa;
            color: #6c757d;
        }
        .reportingGrandTotal td {
            background: #e7f0ff;
            border-top: 2px solid #8bb7f0;
        }
        .reportingMetrics {
            display: block;
            text-align: right;
        }
        .reportingPdfChart {
            margin: 14px 0 20px;
            padding: 14px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }
        .reportingPdfChart h4 {
            margin: 0 0 6px;
            color: #212529;
        }
        .reportingPdfChartDescription {
            margin: 0 0 12px;
            color: #495057;
        }
        .reportingPdfChartImage {
            display: block;
            width: 100%;
            margin: 0 0 12px;
            background: #fff;
        }
        .reportingPdfChartInfo {
            margin-top: 10px;
            padding: 10px 12px;
            background: #fff;
            border-left: 3px solid #0d6efd;
        }
        .reportingPdfChartInfo ul {
            margin: 5px 0 0 18px;
            padding: 0;
        }
        .reportingPdfChartInfo li {
            margin-bottom: 3px;
        }
    </style>
    <h3>{$RECORD->getName()}</h3>
    <p>{$RECORD->get('description')}</p>
    <br>
    {if 'above' eq $RECORD->getChartPosition()}
        {include file='ReportPDFChart.tpl'|vtemplate_path:$MODULE_NAME}
    {/if}
    {if $IS_SUMMARY_REPORT}
        {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getGroupedTableData() TABLE_ROW_TYPES=$RECORD->getGroupedTableRowTypes() TABLE_STYLE=$RECORD->getTableStyle() TABLE_GROUPS_COLLAPSIBLE=false TABLE_SCROLLABLE=false}
    {else}
        {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getTableData() TABLE_ROW_TYPES=$RECORD->getTableRowTypes() TABLE_STYLE=$RECORD->getTableStyle() TABLE_SCROLLABLE=false}
    {/if}
    {if 'below' eq $RECORD->getChartPosition()}
        {include file='ReportPDFChart.tpl'|vtemplate_path:$MODULE_NAME}
    {/if}
{/strip}
