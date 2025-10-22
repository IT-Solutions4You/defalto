{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !isset($FIELD_VALUE['name'])}
        {$FIELD_VALUE['name'] = ''}
    {/if}
    {if !isset($FIELD_VALUE['label'])}
        {$FIELD_VALUE['label'] = ''}
    {/if}
    {if !isset($FIELD_VALUE['sum'])}
        {$FIELD_VALUE['sum'] = ''}
    {/if}
    {if !isset($FIELD_VALUE['avg'])}
        {$FIELD_VALUE['avg'] = ''}
    {/if}
    {if !isset($FIELD_VALUE['min'])}
        {$FIELD_VALUE['min'] = ''}
    {/if}
    {if !isset($FIELD_VALUE['max'])}
        {$FIELD_VALUE['max'] = ''}
    {/if}
    {assign var=VALUE_NAME value=$FIELD_VALUE['name']}
    <div class="selectedCalculations" data-name="{$FIELD_VALUE['name']}" data-label="{$FIELD_VALUE['label']}">
        <div class="row align-items-center py-2">
            <div class="col-sm-8">
                <div class="input-group">
                    <input class="form-control fieldLabel readonly" readonly="readonly" {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][label]" {/if} value="{$FIELD_VALUE['label']}">
                    <input class="form-control fieldValue" type="hidden" {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][name]" {/if} value="{$FIELD_VALUE['name']}">
                    <label class="input-group-text fieldSum">
                        <input class="form-check-input m-0" type="checkbox" {if 'Yes' eq $FIELD_VALUE['sum']} checked="checked" {/if} {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][sum]" {/if} value="Yes">
                        <span class="ms-2">{vtranslate('LBL_SUM', $QUALIFIED_MODULE)}</span>
                    </label>
                    <label class="input-group-text fieldAvg">
                        <input class="form-check-input m-0" type="checkbox" {if 'Yes' eq $FIELD_VALUE['avg']} checked="checked" {/if} {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][avg]" {/if} value="Yes">
                        <span class="ms-2">{vtranslate('LBL_AVG', $QUALIFIED_MODULE)}</span>
                    </label>
                    <label class="input-group-text fieldMin">
                        <input class="form-check-input m-0" type="checkbox" {if 'Yes' eq $FIELD_VALUE['min']} checked="checked" {/if} {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][min]" {/if} value="Yes">
                        <span class="ms-2">{vtranslate('LBL_MIN', $QUALIFIED_MODULE)}</span>
                    </label>
                    <label class="input-group-text fieldMax">
                        <input class="form-check-input m-0" type="checkbox" {if 'Yes' eq $FIELD_VALUE['max']} checked="checked" {/if} {if $VALUE_NAME} name="calculation[{$VALUE_NAME}][max]" {/if} value="Yes">
                        <span class="ms-2">{vtranslate('LBL_MAX', $QUALIFIED_MODULE)}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
{/strip}