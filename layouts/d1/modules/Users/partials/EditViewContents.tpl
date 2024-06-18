{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}'/>
    {/if}
    <div name="editContent">
        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
            {if $BLOCK_LABEL neq 'LBL_CALENDAR_SETTINGS'}
                {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
                {include file=vtemplate_path($BLOCK->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
            {/if}
        {/foreach}
    </div>
{/strip}