{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {foreach item=tax key=count from=$TAXCLASS_DETAILS}
        {if $tax.check_value eq 1}
            {assign var=check_value value="checked"}
            {assign var=show_value value="visible"}
        {else}
            {assign var=check_value value=""}
            {assign var=show_value value="hidden"}
        {/if}
        {assign var=TAX_CHECK_NAME value=$tax.check_name}
        {assign var=TAX_TAX_NAME value=$tax.taxname}
        {assign var=IS_DETAIL_VIEW value=in_array($REQUEST_INSTANCE->get('view'), ['Edit'])}
        <div class="py-2 col-lg-6">
            <div class="row">
                <div class="fieldLabel text-secondary pb-2 {if $IS_DETAIL_VIEW}col-sm-4{else}col-sm-12{/if}">
                    <div class="d-flex">
                        <div class="taxLabel alignBottom">
                            <label>
                                <span>{vtranslate($tax.taxlabel, $MODULE)}</span>
                                <span class="ms-1">(%)</span>
                                <input type="checkbox" name="{$TAX_CHECK_NAME}" id="{$TAX_CHECK_NAME}" class="taxes form-check-input ms-2" data-tax-name={$TAX_TAX_NAME} {$check_value}>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="fieldValue {if $IS_DETAIL_VIEW}col-sm-8{else}col-sm-12{/if}">
                    <div class="Vtiger_ProductTax_UIType">
                        {if $tax.type eq 'Fixed'}
                            <input type="text" id="{$TAX_TAX_NAME}" class="form-control inputElement {if $show_value eq "hidden"}hide{else}show{/if}" name="{$TAX_TAX_NAME}" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true"/>
                        {else}
                            <div class="{if $show_value eq "hidden"}hide{/if}" id="{$TAX_TAX_NAME}">
                                <div class="regionsList">
                                    <div class="input-group">
                                        <label class="input-group-text w-25">{vtranslate('LBL_DEFAULT', $QUALIFIED_MODULE)}</label>
                                        <input class="form-control inputElement" type="text" name="{$TAX_TAX_NAME}_defaultPercentage" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true"/>
                                    </div>
                                    {assign var=i value=0}
                                    {foreach item=REGIONS_INFO name=i from=$tax.regions}
                                        <div class="input-group pt-2">
                                            {foreach item=TAX_REGION_ID from=$REGIONS_INFO['list']}
                                                {assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel({$TAX_REGION_ID})}
                                                <span class="input-group-text w-25">{$TAX_REGION_MODEL->getName()}</span>
                                                <input type="hidden" name="{$TAX_TAX_NAME}_regions[{$i}][list][]" value="{$TAX_REGION_MODEL->getId()}"/>
                                            {/foreach}
                                            <input class="form-control inputElement" type="text" name="{$TAX_TAX_NAME}_regions[{$i}][value]" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true"/>
                                        </div>
                                        {assign var=i value=$i+1}
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
{/strip}