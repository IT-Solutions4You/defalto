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
    <div name='editContent'>
        {if $DUPLICATE_RECORDS}
			<div class="fieldBlockContainer bg-body mb-3 rounded duplicationMessageContainer">
                <div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
                <div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
            </div>
        {/if}
        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
            {if $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}{continue}{/if}
            {if php7_count($BLOCK_FIELDS) gt 0}
                {include file=vtemplate_path($RECORD_STRUCTURE_MODEL->blockData[$BLOCK_LABEL]['template_name'], $MODULE_NAME)}
            {/if}
        {/foreach}
    </div>
    {include file="partials/LineItemsEdit.tpl"|@vtemplate_path:'Inventory'}
{/strip}