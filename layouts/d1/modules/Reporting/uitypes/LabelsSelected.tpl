{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
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