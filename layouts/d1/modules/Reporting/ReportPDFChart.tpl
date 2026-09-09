{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !empty($PDF_CHART['image'])}
        <div class="reportingPdfChart">
            <h4>{$PDF_CHART['title']|escape}</h4>
            <p class="reportingPdfChartDescription">{$PDF_CHART['description']|escape}</p>
            <img class="reportingPdfChartImage" src="{$PDF_CHART['image']}" alt="{$PDF_CHART['title']|escape}">
            {if !empty($PDF_CHART['insights'])}
                <div class="reportingPdfChartInfo">
                    <strong>{vtranslate('LBL_PDF_CHART_ANALYSIS', $MODULE_NAME)}</strong>
                    <ul>
                        {foreach from=$PDF_CHART['insights'] item=CHART_INSIGHT}
                            <li>{$CHART_INSIGHT|escape}</li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            <div class="reportingPdfChartInfo">
                <strong>{vtranslate('LBL_PDF_CHART_FILTERS', $MODULE_NAME)}</strong>
                {if !empty($PDF_CHART['filters'])}
                    <ul>
                        {foreach from=$PDF_CHART['filters'] item=CHART_FILTER}
                            <li>{$CHART_FILTER|escape}</li>
                        {/foreach}
                    </ul>
                {else}
                    <p>{vtranslate('LBL_PDF_CHART_NO_FILTERS', $MODULE_NAME)}</p>
                {/if}
            </div>
        </div>
    {/if}
{/strip}
