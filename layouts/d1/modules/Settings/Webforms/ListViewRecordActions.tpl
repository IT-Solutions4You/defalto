{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}{strip}
<!--LIST VIEW RECORD ACTIONS-->
<div class="table-actions">
   {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
      {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
         <span>
            <a class="btn text-secondary" {if stripos($RECORD_LINK_URL, 'javascript:')===0}onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};" {else}href="{$RECORD_LINK_URL}" {/if}>
               <i class="{$RECORD_LINK->getIcon()}" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
            </a>
         </span>
   {/foreach}
</div>
{/strip}