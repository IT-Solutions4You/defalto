{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="table-actions">
        <span>
            <a class="btn text-secondary" href="#">
                <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}" />
            </a>
        </span>
        <span>
            {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
                {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
                <a class="btn text-secondary" {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                    <i class="fa fa-pencil" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
                </a>
            {/foreach}
        </span>
    </div>
{/strip}        
