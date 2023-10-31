{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="tagsContainer" id="tagCloud">
		{foreach item=TAG_MODEL key=TAG_ID from=$TAGS}
			{assign var=TAG_LABEL value=$TAG_MODEL->getName()}
			<span class="tag" title="{$TAG_LABEL}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_ID}">
				<span class="tagName display-inline-block text-truncate cursorPointer" data-tagid="{$TAG_ID}">{$TAG_LABEL}</span>
			</span>
		{/foreach}
	</div>
{/strip}