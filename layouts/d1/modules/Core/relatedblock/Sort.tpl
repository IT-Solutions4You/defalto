{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="input-group align-items-center mb-3">
    {assign var=SORT_VALUE value=$RELATED_BLOCK_MODEL->getSort($SORT_ID)}
    <div class="input-group-text">
        Order By
    </div>
    <select name="sort_by[{$SORT_ID}]" class="form-select select2">
        <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
        {foreach key=SORT_FIELD item=SORT_LABEL from=$RELATED_MODULE_SORT_OPTIONS}
            <option value="{$SORT_FIELD}" {if $RELATED_BLOCK_MODEL->isSelectedSort($SORT_VALUE, $SORT_FIELD)}selected="selected"{/if} >{vtranslate($SORT_LABEL, $QUALIFIED_MODULE)}</option>
        {/foreach}
    </select>
    <label class="input-group-text w-15">
        <span class="form-check">
            <input class="form-check-input" type="radio" {if $RELATED_BLOCK_MODEL->isCheckedSort($SORT_VALUE, 'ASC')}checked="checked"{/if} value="ASC" name="sort_order[{$SORT_ID}]">
            <span class="form-check-label">ASC</span>
        </span>
    </label>
    <label class="input-group-text w-15">
        <span class="form-check">
            <input class="form-check-input" type="radio" {if $RELATED_BLOCK_MODEL->isCheckedSort($SORT_VALUE, 'DESC')}checked="checked"{/if} value="DESC" name="sort_order[{$SORT_ID}]">
            <span class="form-check-label">DESC</span>
        </span>
    </label>
</div>