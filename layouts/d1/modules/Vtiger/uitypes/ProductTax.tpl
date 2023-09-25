{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
********************************************************************************/
-->*}

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
        <div class="py-2 col-lg-6">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-4 fieldLabel">
                        <div class="d-flex">
                            <div class="taxLabel alignBottom">
                                <span>{vtranslate($tax.taxlabel, $MODULE)}</span>
                                <span class="ps-1">(%)</span>
                            </div>
                            <div class="ps-2">
                                <input type="checkbox" name="{$TAX_CHECK_NAME}" id="{$TAX_CHECK_NAME}" class="taxes form-check" data-tax-name={$TAX_TAX_NAME} {$check_value}>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 fieldValue">
                        <div class="Vtiger_ProductTax_UIType">
                            {if $tax.type eq 'Fixed'}
                                <input type="text" id="{$TAX_TAX_NAME}" class="form-control inputElement {if $show_value eq "hidden"}hide{else}show{/if}" name="{$TAX_TAX_NAME}" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true"/>
                            {else}
                                <div class="{if $show_value eq "hidden"}hide{/if}" id="{$TAX_TAX_NAME}" style="width:70%;">
                                    <div class="regionsList">
                                        <table class="table table-bordered themeTableColor">
                                            <tr>
                                                <td class="{$WIDTHTYPE}" style="width:70%">
                                                    <label>{vtranslate('LBL_DEFAULT', $QUALIFIED_MODULE)}</label>
                                                </td>
                                                <td class="{$WIDTHTYPE}" style="text-align: center; width:30%;">
                                                    <input class="form-control inputElement" type="text" name="{$TAX_TAX_NAME}_defaultPercentage" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true" style="width: 80px;"/>
                                                </td>
                                            </tr>
                                            {assign var=i value=0}
                                            {foreach item=REGIONS_INFO name=i from=$tax.regions}
                                                <tr>
                                                    <td>
                                                        {foreach item=TAX_REGION_ID from=$REGIONS_INFO['list']}
                                                            {assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel({$TAX_REGION_ID})}
                                                            <input type="hidden" name="{$TAX_TAX_NAME}_regions[{$i}][list][]" value="{$TAX_REGION_MODEL->getId()}"/>
                                                            <span class="label label-info displayInlineBlock" style="margin: 2px 1px;">{$TAX_REGION_MODEL->getName()}</span>
                                                        {/foreach}
                                                    </td>
                                                    <td class="{$WIDTHTYPE}" style="text-align: center;">
                                                        <input class="form-control inputElement" type="text" name="{$TAX_TAX_NAME}_regions[{$i}][value]" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true" style="width: 80px;"/>
                                                    </td>
                                                </tr>
                                                {assign var=i value=$i+1}
                                            {/foreach}
                                        </table>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
{/strip}