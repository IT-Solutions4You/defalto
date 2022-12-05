{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div class="container-fluid">
    {assign var=ROW value='row_'|cat:$ROW_VAL}
    <div class="row SortFieldsSelectBoxes" style="padding-bottom: 1rem;">
        <div class="col-lg-6">
                <select class="select2 col-lg-11 selectedSortFields relatedblockColumns" id="selectScol{$ROW}" style="min-width: 50%;">
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
                    <div style="margin: 5px;">
                        <span style="margin-right: 5px;">
                            <input style='margin-right:5px;' type="radio" name="{$ROW}" class="sortOrder" value="Ascending" {if $SELECTED_SORT_FIELD_VALUE eq Ascending} checked="" {/if} />
                            <span>{vtranslate('LBL_ASCENDING','Reports')}</span>
                        </span>
                        <span>
                            <input style='margin-right:5px;;' type="radio" name="{$ROW}" class="sortOrder" value="Descending" {if $SELECTED_SORT_FIELD_VALUE eq Descending} checked="" {/if}/>
                            <span>{vtranslate('LBL_DESCENDING','Reports')}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
{/strip}