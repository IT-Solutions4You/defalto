{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="related-tabs record-view-tabs d-flex align-items-center gap-2 px-3 bg-transparent">
        <ul class="nav nav-underline flex-nowrap text-nowrap overflow-x-auto gap-4" aria-label="{vtranslate('LBL_NAVIGATION', 'Core')|escape}">
            {assign var=ADDITIONAL_RECORD_TABS value=''}
            {foreach item=RECORD_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']|default:[]}
                {if empty($SELECTED_TAB_LABEL)}
                    {assign var=SELECTED_TAB_LABEL value=$RECORD_LINK->getLabel()}
                {/if}
                {assign var=RECORD_LINK_LABEL value=$RECORD_LINK->getLabel()}
                {assign var=RECORD_LINK_URL value=$RECORD_LINK->getUrl()}
                {capture assign=RECORD_TAB}
                <li class="tab-item record-view-tab nav-link border-3 py-3 px-2 {if $RECORD_LINK_LABEL==$SELECTED_TAB_LABEL}active text-primary{else}text-body-secondary{/if}" data-url="{$RECORD_LINK_URL|escape:'html':'UTF-8':false}&amp;tab_label={$RECORD_LINK_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" data-label-key="{$RECORD_LINK_LABEL|escape}" data-link-key="{$RECORD_LINK->get('linkKey')|escape}">
                    <a href="{$RECORD_LINK_URL|escape:'html':'UTF-8':false}&amp;tab_label={$RECORD_LINK_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" class="d-flex align-items-center gap-2 text-reset text-decoration-none" {if $RECORD_LINK_LABEL==$SELECTED_TAB_LABEL}aria-current="page"{/if}>
                        <span aria-hidden="true">{$RECORD_LINK->get('linkicon')}</span>
                        <span>{vtranslate($RECORD_LINK_LABEL, $MODULE_NAME)|escape}</span>
                    </a>
                </li>
                {/capture}
                {if $RECORD_LINK->get('linkKey') eq 'LBL_RECORD_SUMMARY' or $RECORD_LINK->get('linkKey') eq 'LBL_RECORD_DETAILS' or $RECORD_LINK_LABEL eq 'LBL_UPDATES' or $RECORD_LINK_LABEL eq 'LBL_SHARING_RECORD'}
                    {$RECORD_TAB}
                {else}
                    {assign var=ADDITIONAL_RECORD_TABS value=$ADDITIONAL_RECORD_TABS|cat:$RECORD_TAB}
                {/if}
            {/foreach}
            {foreach item=COMMENT_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']|default:[]}
                {if $COMMENT_LINK->getRelatedModuleName() eq 'ModComments'}
                    {assign var=COMMENT_LINK_LABEL value=$COMMENT_LINK->getLabel()}
                    {assign var=COMMENT_LINK_URL value=$COMMENT_LINK->getUrl()}
                    {assign var=COMMENT_LINK_ACTIVE value=(trim($COMMENT_LINK_LABEL)==trim($SELECTED_TAB_LABEL) && $COMMENT_LINK->getId()==$SELECTED_RELATION_ID)}
                    <li class="tab-item record-view-tab nav-link border-3 py-3 px-2 {if $COMMENT_LINK_ACTIVE}active text-primary{else}text-body-secondary{/if}" data-url="{$COMMENT_LINK_URL|escape:'html':'UTF-8':false}&amp;tab_label={$COMMENT_LINK_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" data-label-key="{$COMMENT_LINK_LABEL|escape}" data-module="ModComments" data-relation-id="{$COMMENT_LINK->getId()|escape}" {if $COMMENT_LINK->get('linkFieldName')}data-relatedfield="{$COMMENT_LINK->get('linkFieldName')|escape}"{/if}>
                        <a href="index.php?{$COMMENT_LINK_URL|escape:'html':'UTF-8':false}&amp;tab_label={$COMMENT_LINK_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" class="d-flex align-items-center gap-2 text-reset text-decoration-none" displaylabel="{vtranslate($COMMENT_LINK_LABEL, 'ModComments')|escape}" recordsCount="" {if $COMMENT_LINK_ACTIVE}aria-current="page"{/if}>
                            <i class="fa-regular fa-comment" aria-hidden="true"></i>
                            <span>{vtranslate($COMMENT_LINK_LABEL, 'ModComments')|escape}</span>
                            <span class="numberCircle badge text-bg-primary hide">0</span>
                        </a>
                    </li>
                {/if}
            {/foreach}
            {capture assign=RELATED_MENU_ITEMS}
            {foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWRELATED']|default:[]}
                {if $RELATED_LINK->getRelatedModuleName() neq 'ModComments'}
                    {assign var=RELATED_MODULE_NAME value=$RELATED_LINK->getRelatedModuleName()}
                    {assign var=RELATED_LABEL value=$RELATED_LINK->getLabel()}
                    {assign var=RELATED_URL value=$RELATED_LINK->getUrl()}
                    {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATED_MODULE_NAME)}
                    <li class="tab-item record-related-tab d-none" data-url="{$RELATED_URL|escape:'html':'UTF-8':false}&amp;tab_label={$RELATED_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" data-label-key="{$RELATED_LABEL|escape}" data-module="{$RELATED_MODULE_NAME|escape}" data-relation-id="{$RELATED_LINK->getId()|escape}" {if $RELATED_LINK->get('linkFieldName')}data-relatedfield="{$RELATED_LINK->get('linkFieldName')|escape}"{/if}>
                        <a href="index.php?{$RELATED_URL|escape:'html':'UTF-8':false}&amp;tab_label={$RELATED_LABEL|escape:'url'}&amp;app={$SELECTED_MENU_CATEGORY|escape:'url'}" class="dropdown-item d-flex align-items-center gap-2 fs-6 py-2" displaylabel="{vtranslate($RELATED_LABEL, $RELATED_MODULE_NAME)|escape}" recordsCount="">
                            <span aria-hidden="true">{$RELATED_MODULE_MODEL->getModuleIcon('1rem')}</span>
                            <span>{vtranslate($RELATED_LABEL, $RELATED_MODULE_NAME)|escape}</span>
                            <span class="numberCircle badge text-bg-primary hide">0</span>
                        </a>
                    </li>
                {/if}
            {/foreach}
            {/capture}
            {$ADDITIONAL_RECORD_TABS}
        </ul>
        <div class="dropdown record-related-more nav nav-underline flex-shrink-0 d-none">
            <button type="button" class="nav-link border-3 py-3 px-2 text-body-secondary text-nowrap" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" aria-label="{vtranslate('LBL_RELATED_RECORDS', 'Core')|escape}" title="{vtranslate('LBL_RELATED_RECORDS', 'Core')|escape}">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
                <span class="ms-2">{vtranslate('LBL_RELATED_RECORDS', 'Core')|escape}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end overflow-y-auto h-25vh-max">
                {$RELATED_MENU_ITEMS}
            </ul>
        </div>
    </div>
{/strip}
