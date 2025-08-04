{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="table-actions d-flex align-items-center">
        {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
            {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
            <a class="btn text-secondary" {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};
                    if (event.stopPropagation){ldelim}
                    event.stopPropagation();{rdelim} else{ldelim}
                    event.cancelBubble = true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                <i class="{if $RECORD_LINK->getLabel() eq 'LBL_EDIT'}fa fa-pencil{elseif $RECORD_LINK->getLabel() eq 'LBL_DELETE'}fa fa-trash{else}{$RECORD_LINK->getIcon()}{/if}" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
            </a>
        {/foreach}
    </div>
{/strip}