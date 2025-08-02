{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class='related-tabs col-lg-3 col-xl-2 order-2 pt-3'>
        <div class="bg-body rounded h-100 px-3">
            <div class="fw-bold pt-3 px-3">{vtranslate('LBL_NAVIGATION', $QUALIFIED_MODULE)}</div>
            <ul class="nav nav-pills flex-column py-3">
                {foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
                    {if empty($SELECTED_TAB_LABEL)}
                        {assign var=SELECTED_TAB_LABEL value=$RELATED_LINK->getLabel()}
                    {/if}
                    {assign var=RELATEDLINK_URL value=$RELATED_LINK->getUrl()}
                    {assign var=RELATEDLINK_LABEL value=$RELATED_LINK->getLabel()}
                    <li class="tab-item nav-link fs-6 {if $RELATEDLINK_LABEL==$SELECTED_TAB_LABEL}active{/if}" data-url="{$RELATEDLINK_URL}&tab_label={$RELATEDLINK_LABEL}&app={$SELECTED_MENU_CATEGORY}" data-label-key="{$RELATEDLINK_LABEL}" data-link-key="{$RELATED_LINK->get('linkKey')}">
                        <a href="{$RELATEDLINK_URL}&tab_label={$RELATEDLINK_LABEL}&app={$SELECTED_MENU_CATEGORY}" class="text-truncate">
                            <div class="row">
                                <div class="col-1 tab-icon">
                                    {$RELATED_LINK->get('linkicon')}
                                </div>
                                <div class="col tab-label text-truncate">{vtranslate($RELATEDLINK_LABEL,{$MODULE_NAME})}</div>
                                <div class="col-2">
                                    <div class="numberCircle badge text-bg-primary hide">0</div>
                                </div>
                            </div>
                        </a>
                    </li>
                {/foreach}
                {assign var=RELATED_LINKS value=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
                {if !empty($RELATED_LINKS)}
                    {foreach from=$RELATED_LINKS item=RELATED_LINK}
                        {assign var=RELATEDMODULENAME value=$RELATED_LINK->getRelatedModuleName()}
                        {assign var=RELATEDFIELDNAME value=$RELATED_LINK->get('linkFieldName')}
                        {assign var=DETAILVIEWRELATEDLINKLBL value= vtranslate($RELATED_LINK->getLabel(),$RELATEDMODULENAME)}
                        <li class="tab-item nav-link fs-6 {if (trim($RELATED_LINK->getLabel())== trim($SELECTED_TAB_LABEL)) && ($RELATED_LINK->getId() == $SELECTED_RELATION_ID)}active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" data-label-key="{$RELATED_LINK->getLabel()}"
                            data-module="{$RELATEDMODULENAME}" data-relation-id="{$RELATED_LINK->getId()}" {if $RELATEDMODULENAME eq "ModComments"} title {else} title="{$DETAILVIEWRELATEDLINKLBL}"{/if} {if $RELATEDFIELDNAME}data-relatedfield ="{$RELATEDFIELDNAME}"{/if}>
                            <a href="index.php?{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" class="text-truncate" displaylabel="{$DETAILVIEWRELATEDLINKLBL}" recordsCount="">
                                <div class="row">
                                    <div class="col-1 tab-icon">
                                        {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATEDMODULENAME)}
                                        {$RELATED_MODULE_MODEL->getModuleIcon('1rem')}
                                    </div>
                                    <div class="col tab-label text-truncate">{$DETAILVIEWRELATEDLINKLBL}</div>
                                    <div class="col-2">
                                        <div class="numberCircle badge text-bg-primary hide">0</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        {if isset($REQUEST_INSTANCE.relationId) && $RELATED_LINK->getId() eq $REQUEST_INSTANCE.relationId}
                            {assign var=MORE_TAB_ACTIVE value='true'}
                        {/if}
                    {/foreach}
                {/if}
            </ul>
        </div>
    </div>
{/strip}