{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
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