{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

<div id="ItemsPopupContainer" class="contentsDiv col-sm-12">
    <form id="InventoryItemPopupForm">
        <input type="hidden" name="module" value="{$MODULE}"/>
        <input type="hidden" name="record" value="{$RECORD}"/>
        <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
        <input type="hidden" name="source_record" value="{$SOURCE_RECORD}"/>
        <input type="hidden" name="item_type" value="{$ITEM_TYPE}"/>
        <div class="d-flex flex-row py-2">
            <div class="col-lg-12">
                <input type="text" id="item_text" name="item_text" value="{$DATA.item_text}"
                       class="item_text form-control autoComplete" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)} {vtranslate({$ITEM_TYPE},$ITEM_TYPE)}"
                       data-rule-required=true>
            </div>
        </div>
        {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.sequence.1}
        {assign var=FIELD_NAME value=$FIELD->get('name')}
        <input type="hidden" name="{$FIELD_NAME}" id="{$FIELD_NAME}" value="{$DATA.$FIELD_NAME}" />
        <input type="hidden" name="insert_after_sequence" value="{$INSERT_AFTER_SEQUENCE}" />
        <input type="hidden" id="lineItemType" name="lineItemType" value="Text" class="lineItemType">
    </form>
</div>
