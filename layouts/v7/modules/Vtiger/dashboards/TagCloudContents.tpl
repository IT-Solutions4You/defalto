{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
	<div class="tagsContainer" id="tagCloud">
		{foreach item=TAG_MODEL from=$TAGS name=tagCounter}
			{assign var=TAG_LABEL value=$TAG_MODEL->getName()}
			{assign var=TAG_ID value=$TAG_MODEL->getId()}
			<span class="tag" title="{$TAG_MODEL->getName()}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_ID}">
				<span class="tagName display-inline-block textOverflowEllipsis cursorPointer" data-tagid="{$TAG_ID}">{$TAG_MODEL->getName()}</span>
			</span>
		{/foreach}
	</div>
{/strip}	