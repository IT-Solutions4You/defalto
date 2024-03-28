{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="container-fluid">
    {assign var=ROW value='row_'|cat:$ROW_VAL}
    <div class="row SortFieldsSelectBoxes" style="padding-bottom: 1rem;">
        <div class="col-lg-6">
            <select class="select2 selectedSortFields relatedblockColumns" id="selectScol{$ROW}" style="width: 100%;">
                <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                {foreach key=SECONDARY_MODULE_NAME item=SECONDARY_MODULE from=$SECONDARY_MODULE_FIELDS}
                    {foreach key=BLOCK_LABEL item=BLOCK from=$SECONDARY_MODULE}
                        <optgroup label='{vtranslate($SECONDARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$SECONDARY_MODULE_NAME)}'>
                            {foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
                                <option value="{$FIELD_KEY}" {if $FIELD_KEY eq $SELECTED_SORT_FIELD_KEY}selected=""{/if}>{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                {/foreach}
            </select>
        </div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-6">
                    <div>
                        <span class="me-3">
                            <input type="radio" name="{$ROW}" class="sortOrder" value="Ascending" {if $SELECTED_SORT_FIELD_VALUE eq Ascending} checked="" {/if} />
                            <span class="ms-2">{vtranslate('LBL_ASCENDING')}</span>
                        </span>
                        <span class="me-3">
                            <input type="radio" name="{$ROW}" class="sortOrder" value="Descending" {if $SELECTED_SORT_FIELD_VALUE eq Descending} checked="" {/if}/>
                            <span class="ms-2">{vtranslate('LBL_DESCENDING')}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
{/strip}