{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded">
        <form method="post" class="formRelatedBlockEdit">
            <input type="hidden" name="module" value="{$MODULE}">
            <input type="hidden" name="action" value="RelatedBlock">
            <input type="hidden" name="record" value="{$RECORD_ID}">
            <input type="hidden" name="related_fields" value="{$RELATED_BLOCK_MODEL->get('related_fields')}" class="relateFields">
            <div class="container-fluid border-bottom">
                <div class="row">
                    <div class="col p-3">
                        <div class="fs-4">{vtranslate('LBL_RELATED_BLOCK_EDIT', $QUALIFIED_MODULE)}</div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 align-items-center">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_RELATED_MODULE', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <select name="related_module" id="relateModule" class="form-select select2" required>
                            <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                            {foreach from=$RELATED_BLOCK_MODEL->getRelatedModuleOptions() item=RELATED_LABEL key=RELATED_MODULE}
                                <option value="{$RELATED_MODULE}" {if $RELATED_BLOCK_MODEL->isSelectedRelatedModule($RELATED_MODULE)}selected="selected"{/if}>{$RELATED_LABEL}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 align-items-center border-bottom">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_SELECT_COLUMNS', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <select name="related_fields_select" id="relateFieldsSelect" class="form-select select2" multiple="multiple" required>
                            {foreach from=$RELATED_BLOCK_MODEL->getRelatedFieldsOptions() item=FIELD_LABEL key=FIELD_MODULE}
                                <option value="{$FIELD_MODULE}" {if $RELATED_BLOCK_MODEL->isSelectedRelatedField($FIELD_MODULE)}selected="selected"{/if}>{$FIELD_LABEL}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 border-bottom">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_FILTERS', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        <textarea name="filters" class="hide">{$RELATED_BLOCK_MODEL->getFiltersJSON()}</textarea>
                        {include file='AdvanceFilter.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE
                        SOURCE_MODULE=$RELATED_BLOCK_MODEL->getRelatedModuleName()
                        ADVANCE_CRITERIA=$RELATED_BLOCK_MODEL->getAdvanceCriteria()
                        ADVANCED_FILTER_OPTIONS=Vtiger_Field_Model::getAdvancedFilterOptions()
                        ADVANCED_FILTER_OPTIONS_BY_TYPE=Vtiger_Field_Model::getAdvancedFilterOpsByFieldType()}
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row py-3 border-bottom">
                    <div class="col-lg-3 text-secondary fieldLabel">{vtranslate('LBL_SORTING', $QUALIFIED_MODULE)}</div>
                    <div class="col-lg fieldValue">
                        {include file='relatedblock/Sort.tpl'|vtemplate_path:$QUALIFIED_MODULE SORT_ID=0}
                        {include file='relatedblock/Sort.tpl'|vtemplate_path:$QUALIFIED_MODULE SORT_ID=1}
                        {include file='relatedblock/Sort.tpl'|vtemplate_path:$QUALIFIED_MODULE SORT_ID=2}
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
                        <textarea name="content" class="form-control ckeditor">{$RELATED_BLOCK_MODEL->getContent()}</textarea>
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