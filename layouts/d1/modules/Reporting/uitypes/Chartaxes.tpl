{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
    {assign var=CHART_CONFIGURATION value=$UITYPE_MODEL->getConfiguration($FIELD_MODEL->get('fieldvalue'), $RECORD)}
    {assign var=X_AXIS_FIELD value=$CHART_CONFIGURATION['x']['field']}
    {assign var=X_AXIS_INTERVAL value=$CHART_CONFIGURATION['x']['interval']}
    {assign var=X_AXIS_DISPLAY_VALUE value=$UITYPE_MODEL->getXAxisDisplayValue($RECORD)}
    <div class="reportingChartAxes py-2 col-lg-12">
        <input
            class="chartConfigurationValue"
            name="chart_config"
            type="hidden"
            value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($CHART_CONFIGURATION))}"
        >
        <div class="row align-items-start">
            <div class="chartXAxisLabel fieldLabel text-secondary col-sm-2 pt-2">
                <label for="reportingChartXAxisField">{vtranslate('LBL_CHART_X_AXIS', $QUALIFIED_MODULE)}</label>
            </div>
            <div class="chartXAxisValue fieldValue col-sm-10">
                <input
                    class="chartXAxisField form-control readonly"
                    id="reportingChartXAxisField"
                    data-field="{Vtiger_Util_Helper::toSafeHTML($X_AXIS_FIELD)}"
                    data-interval="{Vtiger_Util_Helper::toSafeHTML($X_AXIS_INTERVAL)}"
                    data-placeholder="{vtranslate('LBL_CHART_SELECT_X_AXIS', $QUALIFIED_MODULE)}"
                    readonly
                    aria-readonly="true"
                    value="{Vtiger_Util_Helper::toSafeHTML($X_AXIS_DISPLAY_VALUE)}"
                >
            </div>
        </div>
        <div class="row align-items-start mt-3">
            <div class="chartYAxisLabel fieldLabel text-secondary col-sm-2 pt-2">
                <span>{vtranslate('LBL_CHART_Y_AXIS', $QUALIFIED_MODULE)}</span>
            </div>
            <div class="chartYAxisValue fieldValue col-sm-10">
                <div class="small text-secondary mb-2">{vtranslate('LBL_CHART_Y_FROM_CALCULATIONS', $QUALIFIED_MODULE)}</div>
                <div class="chartCalculationWarning alert alert-warning mb-2 d-none" role="status">
                    {vtranslate('LBL_CHART_SELECT_CALCULATION_FIRST', $QUALIFIED_MODULE)}
                </div>
                <select
                    class="chartYAxisCalculation select2 form-select"
                    data-count-label="{vtranslate('LBL_COUNT_RECORDS', $QUALIFIED_MODULE)}"
                    data-sum-label="{vtranslate('LBL_SUM', $QUALIFIED_MODULE)}"
                    data-avg-label="{vtranslate('LBL_AVG', $QUALIFIED_MODULE)}"
                    data-min-label="{vtranslate('LBL_MIN', $QUALIFIED_MODULE)}"
                    data-max-label="{vtranslate('LBL_MAX', $QUALIFIED_MODULE)}"
                    data-placeholder="{vtranslate('LBL_CHART_SELECT_CALCULATION', $QUALIFIED_MODULE)}"
                    multiple
                >
                    {foreach from=$CHART_CONFIGURATION['series'] item=SELECTED_SERIES}
                        {assign var=SELECTED_SERIES_VALUE value=$SELECTED_SERIES['aggregation']|cat:':'|cat:$SELECTED_SERIES['field']}
                        <option value="{Vtiger_Util_Helper::toSafeHTML($SELECTED_SERIES_VALUE)}" selected>{Vtiger_Util_Helper::toSafeHTML($SELECTED_SERIES_VALUE)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
{/strip}
