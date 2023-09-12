{strip}
<div class="summaryWidgetContainer bg-white rounded mb-3">
    <div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
        <div class="widget_header border-1 border-bottom p-3 clearfix">
            <input type="hidden" name="relatedModule" value="{$DETAIL_VIEW_WIDGET->get('linkName')}"/>
            <h4 class="display-inline-block pull-left">{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4>
            {if $DETAIL_VIEW_WIDGET->get('action')}
                {if 'Documents' eq $DETAIL_VIEW_WIDGET->getLabel()}
                    {assign var=PARENT_ID value=$RECORD->getId()}
                    <div class="pull-right">
                        <div class="dropdown">
                            <button type="button" class="btn text-secondary fw-bold" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-circle-plus"></i>
                                <span class="ms-2">{vtranslate('LBL_NEW_DOCUMENT', 'Documents')}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-header">
                                        <i class="fa fa-upload"></i>
                                        <span class="ms-2">{vtranslate('LBL_FILE_UPLOAD', 'Documents')}</span>
                                    </div>
                                </li>
                                <li id="VtigerAction">
                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.uploadTo('Vtiger',{$PARENT_ID},'{$MODULE_NAME}')">
                                        <i class="fa fa-home"></i>
                                        <span class="ms-2">{vtranslate('LBL_TO_SERVICE', 'Documents', {vtranslate('LBL_VTIGER', 'Documents')})}</span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <div class="dropdown-header">
                                        <i class="fa fa-link"></i>
                                        <span class="ms-2">{vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', 'Documents')}</span>
                                    </div>
                                </li>
                                <li id="shareDocument">
                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('E',{$PARENT_ID},'{$MODULE_NAME}')">
                                        <i class="fa fa-external-link"></i>
                                        <span class="ms-2">{vtranslate('LBL_FROM_SERVICE', 'Documents', {vtranslate('LBL_FILE_URL', 'Documents')})}</span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li id="createDocument">
                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W',{$PARENT_ID},'{$MODULE_NAME}')">
                                        <i class="fa fa-file-text"></i>
                                        <span class="ms-2">{vtranslate('LBL_CREATE_NEW', 'Documents', {vtranslate('SINGLE_Documents', 'Documents')})}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                {else}
                    <div class="pull-right">
                        <button class="btn text-secondary fw-bold addButton createRecord" type="button" data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}">
                            <i class="fa fa-plus"></i>
                            <span class="mx-2">{vtranslate('LBL_ADD',$MODULE_NAME)}</span>
                            <span>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</span>
                        </button>
                    </div>
                {/if}
            {/if}
        </div>
        <div class="widget_contents p-3">
        </div>
    </div>
</div>
{/strip}