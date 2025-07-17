{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
    {assign var=UITYPE value=$FIELD_MODEL->getUITypeModel()}
    {assign var=TAXES value=$UITYPE->getTaxes($RECORD_ID)}
    {assign var=IS_MASS_EDIT_VISIBLE value=false}
    {foreach from=$TAXES item=TAX_MODEL}
        {assign var=TAX_ID value=$TAX_MODEL->getId()}
        <div class="row align-items-center py-2">
            <div class="col-4 cursorDefault text-secondary" title="{$TAX_MODEL->getLabel()}">
                <label class="form-check">
                    <input type="hidden" name="{$FIELD_NAME}[{$TAX_ID}][checked]" value="0">
                    <input type="checkbox" name="{$FIELD_NAME}[{$TAX_ID}][checked]" value="1" {if $TAX_MODEL->isActiveForRecord()}checked="checked"{/if} class="form-check-input taxes h-13rem w-13rem" data-tax-name="taxes_{$TAX_ID}">
                    <span class="text-truncate ms-2">{$TAX_MODEL->getLabel()}</span>
                </label>
            </div>
            <div id="taxes_{$TAX_ID}" class="col {if !$TAX_MODEL->isActiveForRecord()}hide{/if}">
                <label class="input-group py-1">
                    <input type="text" class="form-control replaceCommaWithDot" name="{$FIELD_NAME}[{$TAX_ID}][percentage]" value="{$TAX_MODEL->getPercentage()}">
                    <span class="input-group-text">%</span>
                </label>
                {foreach from=$TAX_MODEL->getRegions() item=REGION_MODEL}
                    {assign var=REGION_ID value=$REGION_MODEL->getId()}
                    <label class="input-group py-1" title="{$REGION_MODEL->getName()}">
                        <span class="input-group-text w-50">{$REGION_MODEL->getName()}</span>
                        <input type="text" class="form-control replaceCommaWithDot" name="{$FIELD_NAME}[{$TAX_ID}][regions][{$REGION_ID}]" value="{$REGION_MODEL->getPercentage()}">
                        <span class="input-group-text">%</span>
                    </label>
                {/foreach}
            </div>
        </div>
    {/foreach}
{/strip}