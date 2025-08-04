{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="btn-toolbar btn-group-sm">
	{foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
		{assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
		{if $RECORD_LINK->getIcon() eq 'icon-pencil'}
			<a class="text-secondary btn" {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
				<i class="fa fa-pencil" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
			</a>
		{/if}
		{if $RECORD_LINK->getIcon() eq 'icon-trash'}
			<a class="text-secondary btn" {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")}" {else} href='{$RECORD_LINK_URL}' {/if}>
				<i class="fa fa-trash" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}" ></i>
			</a>
		{/if}
	{/foreach}
</div>