{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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