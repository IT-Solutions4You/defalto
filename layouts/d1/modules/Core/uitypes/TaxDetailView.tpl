{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
    {assign var=UITYPE value=$FIELD_MODEL->getUITypeModel()}
    {assign var=TAXES value=$UITYPE->getDetailTaxes($RECORD->getId())}
    {foreach from=$TAXES item=TAX_MODEL}
        {assign var=TAX_ID value=$TAX_MODEL->getId()}
        {if $TAX_MODEL->isActiveForRecord()}
            <div class="row align-items-center py-2">
                <div class="col-4">
                    {$TAX_MODEL->getLabel()}
                </div>
                <div class="col-8">
                    <label class="input-group">
                        {$TAX_MODEL->getPercentage()} %
                    </label>
                    {foreach from=$TAX_MODEL->getRegions() item=REGION_MODEL}
                        {assign var=REGION_ID value=$REGION_MODEL->getId()}
                        <label class="input-group">
                            {$REGION_MODEL->getName()}: {$REGION_MODEL->getPercentage()} %
                        </label>
                    {/foreach}
                </div>
            </div>
        {/if}
    {/foreach}
{/strip}