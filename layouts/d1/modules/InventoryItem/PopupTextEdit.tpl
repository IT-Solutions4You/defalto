{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div id="ItemsPopupContainer" class="contentsDiv col-sm-12">
    <form id="InventoryItemPopupForm">
        <input type="hidden" name="module" value="{$MODULE}"/>
        <input type="hidden" name="record" value="{$RECORD}"/>
        <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
        <input type="hidden" name="source_record" value="{$SOURCE_RECORD}"/>
        <input type="hidden" name="item_type" value="{$ITEM_TYPE}"/>

        {if $HARD_FORMATTED_RECORD_STRUCTURE.productid neq ''}
            {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['productid'][1]}
            {assign var=FIELD_NAME value=$FIELD->get('name')}
            <div class="d-flex flex-row py-2">
                <div class="col-lg-12">
                    <input type="text" id="item_text" name="item_text" value="{$data.item_text}"
                           class="item_text form-control autoComplete" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)} {vtranslate({$ITEM_TYPE},$ITEM_TYPE)}"
                           data-rule-required=true {if !empty($data.item_text)}disabled="disabled"{/if}>
                </div>
            </div>
        {/if}
    </form>
</div>
