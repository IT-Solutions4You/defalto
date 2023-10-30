{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="col-md">
        {if $MULTI_SELECT}
            {if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-outline-secondary" disabled="disabled"><strong>{vtranslate('LBL_ADD', $MODULE)}</strong></button>{/if}
        {else}
            &nbsp;
        {/if}
    </div>
    <div class="col-md-auto">
        {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
        {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
    </div>
{/strip}