{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !isset($FIELD_VALUE_KEY)}
        {assign var=FIELD_VALUE_KEY value=''}
    {/if}
    <div class="selectedLabels" data-field="{$FIELD_VALUE_KEY}" data-label="{$FIELD_VALUE}">
        <div class="row align-items-center py-2">
            <div class="col-sm-8">
                <div class="input-group">
                    <input class="form-control fieldLabel" type="text" {if $FIELD_VALUE_KEY} name="labels[{$FIELD_VALUE_KEY}]" {/if} value="{$FIELD_VALUE}">
                </div>
            </div>
        </div>
    </div>
{/strip}