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
    {assign var=TAXES value=$UITYPE->getDetailTaxes($RECORD->getId())}
    {foreach from=$TAXES item=TAX_MODEL}
        {assign var=TAX_ID value=$TAX_MODEL->getId()}
        <div id="{$MODULE}_{$REQUEST_INSTANCE.view}_{$FIELD_MODEL->getName()}" class="py-2 col-lg-6">
            <div class="row py-2 border-bottom border-light-subtle h-100 align-items-center">
                <div id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}" class="fieldLabel text-secondary col-sm-4">
                    {$TAX_MODEL->getLabel()}
                </div>
                <div id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}" class="fieldValue fw-semibold col-lg-8 {if !$TAX_MODEL->isActiveForRecord()}hide{/if}">
                    <label class="input-group py-1">
                        {$TAX_MODEL->getPercentage()} %
                    </label>
                    {foreach from=$TAX_MODEL->getRegions() item=REGION_MODEL}
                        {assign var=REGION_ID value=$REGION_MODEL->getId()}
                        <label class="input-group py-1">
                            {$REGION_MODEL->getName()}: {$REGION_MODEL->getPercentage()} %
                        </label>
                    {/foreach}
                </div>
            </div>
        </div>
    {/foreach}
{/strip}