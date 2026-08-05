{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="reportingChartContainer">
        <div class="border-bottom pb-3 mb-3">
            <h4 class="m-0">
                <i class="fa-solid fa-chart-column text-secondary me-2"></i>
                {vtranslate('LBL_CHART', $MODULE_NAME)}
            </h4>
        </div>
        {if $HAS_CHART_DATA}
            <textarea class="reportingChartData visually-hidden">{$CHART_DATA_JSON}</textarea>
            <div class="reportingChart ratio ratio-21x9">
                <canvas aria-label="{vtranslate('LBL_CHART', $MODULE_NAME)}" role="img"></canvas>
            </div>
        {else}
            <div class="alert alert-info mb-0">{vtranslate('LBL_NO_CHART_DATA', $MODULE_NAME)}</div>
        {/if}
    </div>
{/strip}
