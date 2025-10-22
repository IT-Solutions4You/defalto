{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if php7_count($DATA) gt 0 }
        <input class="widgetData" type=hidden value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
        <input class="yAxisFieldType" type="hidden" value="{if isset($YAXIS_FIELD_TYPE)}{$YAXIS_FIELD_TYPE}{/if}" />
        <div class="chartContainer h-100 px-2">
            <canvas class="widgetChartContainer h-100 w-100 mx-auto" name="chartcontent"></canvas>
        </div>
    {else}
        <span class="noDataMsg p-2">
            {vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}
{/strip}