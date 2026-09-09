{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $IS_SUMMARY_REPORT && 'above' eq $RECORD->getChartPosition()}
    <div class="reportingChartPreview border-bottom p-3 mb-3">
        {include file='ReportChart.tpl'|vtemplate_path:$MODULE_NAME}
    </div>
{/if}
{include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME}
{if $IS_SUMMARY_REPORT && 'below' eq $RECORD->getChartPosition()}
    <div class="reportingChartPreview border-top p-3 mt-3">
        {include file='ReportChart.tpl'|vtemplate_path:$MODULE_NAME}
    </div>
{/if}
