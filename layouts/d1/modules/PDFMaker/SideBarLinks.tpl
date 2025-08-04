{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="quickLinksDiv">
        {foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
            {assign var=SIDE_LINK_URL value=decode_html($SIDEBARLINK->getUrl())}
            {assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
            {assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
            {if $COUNT_OF_EXPLODED_URL gt 1}
                {assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
            {/if}
            {assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
            {assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
            {assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
            {assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
            {assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL)}
            <p onclick="window.location.href = '{$SIDEBARLINK->getUrl()}'" id="{$MODULE}_sideBar_link_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARLINK->getLabel())}"
               class="{if $LINK_LIST_VIEW and $IS_LINK_MODULE_NAME}selectedQuickLink {else}unSelectedQuickLink{/if}"><a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
                    <strong>{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}</strong>
                </a></p>
            {/foreach}
    </div>
{/strip}