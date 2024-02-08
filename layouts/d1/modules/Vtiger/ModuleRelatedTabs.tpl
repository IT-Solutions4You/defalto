{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class='related-tabs col-lg-3 col-xl-2 order-2 pt-3'>
        <div class="bg-body rounded h-100 px-3">
            <ul class="nav nav-pills flex-column py-3">
                {foreach item=RELATED_LINK from=$DETAILVIEW_LINKS['DETAILVIEWTAB']}
                    {assign var=RELATEDLINK_URL value=$RELATED_LINK->getUrl()}
                    {assign var=RELATEDLINK_LABEL value=$RELATED_LINK->getLabel()}
                    {assign var=RELATED_TAB_LABEL value={vtranslate('SINGLE_'|cat:$MODULE_NAME, $MODULE_NAME)}|cat:" "|cat:$RELATEDLINK_LABEL}
                    <li class="tab-item nav-link fs-6 {if $RELATEDLINK_LABEL==$SELECTED_TAB_LABEL}active{/if}" data-url="{$RELATEDLINK_URL}&tab_label={$RELATED_TAB_LABEL}&app={$SELECTED_MENU_CATEGORY}" data-label-key="{$RELATEDLINK_LABEL}" data-link-key="{$RELATED_LINK->get('linkKey')}">
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
                        {assign var="DETAILVIEWRELATEDLINKLBL" value= vtranslate($RELATED_LINK->getLabel(),$RELATEDMODULENAME)}
                        <li class="tab-item nav-link fs-6 {if (trim($RELATED_LINK->getLabel())== trim($SELECTED_TAB_LABEL)) && ($RELATED_LINK->getId() == $SELECTED_RELATION_ID)}active{/if}" data-url="{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" data-label-key="{$RELATED_LINK->getLabel()}"
                            data-module="{$RELATEDMODULENAME}" data-relation-id="{$RELATED_LINK->getId()}" {if $RELATEDMODULENAME eq "ModComments"} title {else} title="{$DETAILVIEWRELATEDLINKLBL}"{/if} {if $RELATEDFIELDNAME}data-relatedfield ="{$RELATEDFIELDNAME}"{/if}>
                            <a href="index.php?{$RELATED_LINK->getUrl()}&tab_label={$RELATED_LINK->getLabel()}&app={$SELECTED_MENU_CATEGORY}" class="text-truncate" displaylabel="{$DETAILVIEWRELATEDLINKLBL}" recordsCount="">
                                <div class="row">
                                    <div class="col-1 tab-icon">
                                        {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($RELATEDMODULENAME)}
                                        {$RELATED_MODULE_MODEL->getModuleIcon('1rem')}
                                    </div>
                                    <div class="col tab-label text-truncate">{$RELATED_LINK->getLabel()}</div>
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