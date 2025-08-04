{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/PriceBooks/views/ListPriceUpdate.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer modal-content modal-md" id="listPriceUpdateContainer">
        {assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_LIST_PRICE', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal" id="listPriceUpdate" method="post" action="index.php">
			<div class="modal-body">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="action" value="RelationAjax" />
                <input type="hidden" name="src_record" value="{$PRICEBOOK_ID}" />
                <input type="hidden" name="relid" value="{$REL_ID}" />
                <div class="row">
                    <label class="col-lg">
                        <span class="text-secondary me-2">{vtranslate('LBL_EDIT_LIST_PRICE',$MODULE)}</span>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg">
                        <input type="text" name="currentPrice" value="{$CURRENT_PRICE}" data-rule-required="true" class="inputElement form-control replaceCommaWithDot" data-rule-currency="true"/>
                    </div>
                </div>
			</div>
			{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
		</form>
        </div>
    </div>
{/strip}