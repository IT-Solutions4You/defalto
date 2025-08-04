{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Vtiger/views/TaxAjax.php *}
{strip}
	{assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
	<div class="taxRegionContainer modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTaxRegion" class="form-horizontal">
                {if $TAX_REGION_ID}
                    {assign var=TITLE value={{vtranslate('LBL_EDIT_REGION', $QUALIFIED_MODULE)}}}
                {else}
                    {assign var=TITLE value={{vtranslate('LBL_ADD_NEW_REGION', $QUALIFIED_MODULE)}}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                <input type="hidden" name="taxRegionId" value="{$TAX_REGION_ID}" />
                <div class="modal-body">
                    <div class="row nameBlock">
                        <div class="col-lg-3 text-end">
                            <label>{vtranslate('LBL_REGION_NAME', $QUALIFIED_MODULE)}</label>
                        </div>
                        <div class="col-lg-7">
                            <input class="inputElement form-control" type="text" name="name" placeholder="{vtranslate('LBL_ENTER_REGION_NAME', $QUALIFIED_MODULE)}" value="{$TAX_REGION_MODEL->getName()}" data-rule-required="true" />
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
	</div>
{/strip}