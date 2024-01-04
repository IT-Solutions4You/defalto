{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}

	<div name="editContent">
		{if $DUPLICATE_RECORDS}
			<div class="fieldBlockContainer bg-body rounded-top mb-3 p-3 duplicationMessageContainer">
				<h4 class="duplicationMessageHeader fw-bold"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></h4>
				<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
			</div>
		{/if}
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
			{if $BLOCK_FIELDS|php7_count gt 0}
				<div class="fieldBlockContainer mb-3 border-bottom {if 1 neq $smarty.foreach.blockIterator.iteration}{/if}" data-block="{$BLOCK_LABEL}">
					<h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
					{include file=vtemplate_path('blocks/Fields.tpl',$MODULE)}
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}
