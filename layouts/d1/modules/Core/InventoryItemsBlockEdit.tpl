{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded">
        <form method="post" class="formRelatedBlockEdit">
            <input type="hidden" name="module" value="{$MODULE}">
            <input type="hidden" name="action" value="InventoryItemsBlock">
            <input type="hidden" name="record" value="{$RECORD_ID}">
            <input type="hidden" name="related_module" value="InventoryItem" id="relateModule">
            <input type="hidden" name="related_fields" value="{$RELATED_BLOCK_MODEL->get('related_fields')}" class="relateFields">
            <div class="container-fluid border-bottom">
                <div class="row">
                    <div class="col p-3">
                        <div class="fs-4">{vtranslate('LBL_INVENTORY_ITEMS_BLOCK_EDIT', $QUALIFIED_MODULE)}</div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 align-items-center">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_SELECT_COLUMNS', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <select name="related_fields_select" id="relateFieldsSelect" class="form-select select2" multiple="multiple" required>
                            {foreach from=$RELATED_BLOCK_MODEL->getRelatedFieldsOptions() item=FIELD_LABEL key=FIELD_MODULE}
                                <option value="{$FIELD_MODULE}" {if $RELATED_BLOCK_MODEL->isSelectedRelatedFields($FIELD_MODULE)}selected="selected"{/if}>{$FIELD_LABEL}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 align-items-center">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <input class="form-control" required name="name" type="text" value="{$RELATED_BLOCK_MODEL->get('name')}">
                    </div>
                </div>
                <div class="row py-3 border-bottom">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_BLOCK_STYLE', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <textarea name="content" class="form-control" id="content">{$RELATED_BLOCK_MODEL->getContent()}</textarea>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3">
                    <div class="col-6"></div>
                    <div class="col-6">
                        <button id="saveRelatedBlock" class="btn btn-primary active" type="submit" name="saveButton">
                            <strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>