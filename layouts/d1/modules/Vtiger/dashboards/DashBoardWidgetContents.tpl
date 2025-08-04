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
        <input class="yAxisFieldType" type="hidden" value="{$YAXIS_FIELD_TYPE}" />
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-10">
                    <div class="widgetChartContainer" name="chartcontent" style="height:220px;min-width:300px; margin: 0 auto" ></div>
                </div>
            </div>
        </div>
    {else}
        <span class="noDataMsg p-2">
            {vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
        </span>
    {/if}
{/strip}