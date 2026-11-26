{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="related-tabs d-none d-lg-block col-lg-3 col-xl-2 order-1 order-lg-2">
        <div class="record-related-menu bg-body rounded h-100 px-3">
            <div>
                <div class="fw-bold pt-3 px-3">{vtranslate('LBL_RELATED_MODULES', 'Core')}</div>
                <ul class="nav nav-pills flex-column py-3">
                    {assign var=RELATED_LINKS value=$DETAILVIEW_LINKS['DETAILVIEWRELATED']}
                    {if !empty($RELATED_LINKS)}
                        {foreach from=$RELATED_LINKS item=RELATED_LINK}
                            {if $RELATED_LINK->getRelatedModuleName() eq 'ModComments'}
                                {continue}
                            {/if}
                            {assign var=RELATEDMODULENAME value=$RELATED_LINK->getRelatedModuleName()}
                            {assign var=RELATEDFIELDNAME value=$RELATED_LINK->get('linkFieldName')}
                            {assign var=DETAILVIEWRELATEDLINKLBL value= vtranslate($RELATED_LINK->getLabel(),$RELATEDMODULENAME)}
                            <li class="tab-item nav-link fs-6 {if (trim($RELATED_LINK->getLabel())== trim($SELECTED_TAB_LABEL)) && ($RELATED_LINK->getId() == $SELECTED_RELATION_ID)}active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" data-label-key="{$RELATED_LINK->getLabel()}"
                                data-module="{$RELATEDMODULENAME}" data-relation-id="{$RELATED_LINK->getId()}" title="{$DETAILVIEWRELATEDLINKLBL|escape}" {if $RELATEDFIELDNAME}data-relatedfield ="{$RELATEDFIELDNAME}"{/if}>
                                <a href="index.php?{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" class="text-truncate" displaylabel="{$DETAILVIEWRELATEDLINKLBL}" recordsCount="" {if (trim($RELATED_LINK->getLabel())==trim($SELECTED_TAB_LABEL)) && ($RELATED_LINK->getId()==$SELECTED_RELATION_ID)}aria-current="page"{/if}>
                                    <div class="row">
                                        <div class="col-1 tab-icon">
                                            {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATEDMODULENAME)}
                                            {$RELATED_MODULE_MODEL->getModuleIcon('1rem')}
                                        </div>
                                        <div class="col tab-label text-truncate">{$DETAILVIEWRELATEDLINKLBL}</div>
                                        <div class="col-2">
                                            <div class="numberCircle badge rounded-pill text-bg-primary hide">0</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        {/foreach}
                    {/if}
                </ul>
            </div>
        </div>
    </div>
{/strip}
