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
    {foreach from=$TAXES item=TAX_MODEL}
        {assign var=TAX_ID value=$TAX_MODEL->getId()}
        <div class="py-2 col-lg-6">
            <div class="row">
                <div class="fieldLabel text-secondary col-sm-4">
                    <div class="d-flex">
                        <div>{$TAX_MODEL->getLabel()}</div>
                        <div class="w-auto ms-auto">
                            <label class="form-check">
                                <input type="hidden" name="taxes_data[{$TAX_ID}][checked]" value="0">
                                <input type="checkbox" name="taxes_data[{$TAX_ID}][checked]" value="1" {if $TAX_MODEL->isActiveForRecord()}checked="checked"{/if} class="form-check-input taxes" data-tax-name="taxes_{$TAX_ID}">
                            </label>
                        </div>
                    </div>
                </div>
                <div id="taxes_{$TAX_ID}" class="fieldValue col-sm-8 {if !$TAX_MODEL->isActiveForRecord()}hide{/if}">
                    <label class="input-group py-1">
                        <input type="text" class="form-control replaceCommaWithDot" name="taxes_data[{$TAX_ID}][percentage]" value="{$TAX_MODEL->getPercentage()}">
                        <span class="input-group-text">%</span>
                    </label>
                    {foreach from=$TAX_MODEL->getRegions() item=REGION_MODEL}
                        {assign var=REGION_ID value=$REGION_MODEL->getId()}
                        <label class="input-group py-1">
                            <span class="input-group-text w-25">{$REGION_MODEL->getName()}</span>
                            <input type="text" class="form-control replaceCommaWithDot" name="taxes_data[{$TAX_ID}][regions][{$REGION_ID}]" value="{$REGION_MODEL->getPercentage()}">
                            <span class="input-group-text">%</span>
                        </label>
                    {/foreach}
                </div>
            </div>
        </div>
    {/foreach}
{/strip}