{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="chartSeriesRow row align-items-end g-2 mb-2">
        <div class="chartSeriesAggregationContainer col-12 col-lg-4">
            <label class="form-label small text-secondary">{vtranslate('LBL_CHART_AGGREGATION', $QUALIFIED_MODULE)}</label>
            <select class="chartSeriesAggregation form-select" disabled aria-disabled="true">
                {foreach from=$AGGREGATIONS key=AGGREGATION item=AGGREGATION_LABEL}
                    <option value="{$AGGREGATION}" {if $AGGREGATION eq $SERIES['aggregation']}selected{/if}>{$AGGREGATION_LABEL}</option>
                {/foreach}
            </select>
        </div>
        <div class="chartSeriesFieldContainer col-12 col-lg">
            <label class="form-label small text-secondary">{vtranslate('LBL_CHART_VALUE_FIELD', $QUALIFIED_MODULE)}</label>
            <select class="chartSeriesField select2 form-select" data-placeholder="{vtranslate('LBL_CHART_SELECT_VALUE_FIELD', $QUALIFIED_MODULE)}" disabled aria-disabled="true">
                <option
                    value=""
                    data-count-label="{vtranslate('LBL_CHART_VALUE_NOT_REQUIRED', $QUALIFIED_MODULE)}"
                >{vtranslate('LBL_CHART_SELECT_VALUE_FIELD', $QUALIFIED_MODULE)}</option>
                {foreach from=$Y_AXIS_OPTIONS key=FIELD_NAME item=FIELD_LABEL}
                    <option value="{Vtiger_Util_Helper::toSafeHTML($FIELD_NAME)}" {if $FIELD_NAME eq $SERIES['field']}selected{/if}>{$FIELD_LABEL}</option>
                {/foreach}
            </select>
        </div>
    </div>
{/strip}
