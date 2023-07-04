<div class="row conditionRow">
    <div class="col-lg-8">
        <select class="select2 form-control" name="sortcolumnname">
            <option value="">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SORT_RECORD_STRUCTURE}
                <optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
                    {foreach item=FIELD_MODEL from=$BLOCK_FIELDS}
                        {if $FIELD_MODEL->getFieldDataType() neq 'reference' && $FIELD_MODEL->getFieldDataType() neq 'file' && $FIELD_MODEL->getFieldDataType() neq 'multipicklist'}
                            <option value="{$FIELD_MODEL->get('column')}" {if $FIELD_MODEL->get('column') eq $ORDER_BY}selected{/if}>{vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}</option>
                        {/if}
                    {/foreach}
                </optgroup>
            {/foreach}
        </select>
    </div>
    <div class="col-lg-4">
        <select class="select2 form-control" name="sortorder">
            <option value="ASC" {if 'ASC' eq $SORT_ORDER}selected{/if}>{vtranslate('ASC')}</option>
            <option value="DESC" {if 'DESC' eq $SORT_ORDER}selected{/if}>{vtranslate('DESC')}</option>
        </select>
    </div>
</div>