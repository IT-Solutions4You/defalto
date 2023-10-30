{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    {assign var=ROW value='row_'|cat:$ROW_VAL}
    <span id="SortFieldsSelectBoxes" class="span6">
            <select id="selectScol{$ROW}" class="span6 selectedSortFields">
                <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                {foreach key=SECONDARY_MODULE_NAME item=SECONDARY_MODULE from=$SECONDARY_MODULE_FIELDS}
                    {foreach key=BLOCK_LABEL item=BLOCK from=$SECONDARY_MODULE}
                        <optgroup label='{vtranslate($SECONDARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$SECONDARY_MODULE_NAME)}'>
                            {foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
                                    <option value="{$FIELD_KEY}"{if $FIELD_KEY eq $SELECTED_SORT_FIELD_KEY}selected=""{/if}>{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                {/foreach}
            </select>
    </span>
    <span class="span6">
        <div class="row-fluid">
            <span class="span6">
                <input style='margin:5px;' type="radio" name="{$ROW}" class="sortOrder" value="Ascending" {if $SELECTED_SORT_FIELD_VALUE eq Ascending} checked="" {/if} />&nbsp;<span>{vtranslate('LBL_ASCENDING','Reports')}</span>&nbsp;&nbsp;
                <input style='margin:5px;'type="radio" name="{$ROW}" class="sortOrder" value="Descending" {if $SELECTED_SORT_FIELD_VALUE eq Descending} checked="" {/if}/>&nbsp;<span>{vtranslate('LBL_DESCENDING','Reports')}</span>
            </span>
        </div>
    </span>
{/strip}