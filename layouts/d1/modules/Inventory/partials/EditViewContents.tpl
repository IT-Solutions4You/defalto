{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
                {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
                {include file=vtemplate_path($BLOCK->getUITypeModel()->getTemplateName(), $MODULE_NAME)}
            {/if}
        {/foreach}
    </div>
    {include file="partials/LineItemsEdit.tpl"|@vtemplate_path:'Inventory'}
{/strip}