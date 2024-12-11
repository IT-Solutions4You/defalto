{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
<div class="summaryWidgetContainer bg-body rounded mb-3">
    <div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
        <div class="widget_header border-1 border-bottom p-3 clearfix">
            <input type="hidden" name="relatedModule" value="{$DETAIL_VIEW_WIDGET->get('linkName')}"/>
            <h4 class="display-inline-block pull-left">{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4>
            {if $DETAIL_VIEW_WIDGET->get('action')}
                {if 'Documents' eq $DETAIL_VIEW_WIDGET->getLabel()}
                    {assign var=PARENT_ID value=$RECORD->getId()}
                    <div class="pull-right">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm text-secondary fw-bold" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-plus"></i>
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
                                        <span class="ms-2">{vtranslate('LBL_TO_SERVICE', 'Documents', vtranslate('LBL_VTIGER', 'Documents'))}</span>
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
                                        <span class="ms-2">{vtranslate('LBL_FROM_SERVICE', 'Documents', vtranslate('LBL_FILE_URL', 'Documents'))}</span>
                                    </a>
                                </li>
                                <li id="createDocument">
                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W',{$PARENT_ID},'{$MODULE_NAME}')">
                                        <i class="fa fa-file-text"></i>
                                        <span class="ms-2">{vtranslate('LBL_CREATE_NEW', 'Documents', vtranslate('SINGLE_Documents', 'Documents'))}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                {else}
                    <div class="pull-right">
                        <button class="btn btn-sm text-secondary fw-bold addButton createRecord" type="button" data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}">
                            <i class="fa fa-plus"></i>
                            <span class="mx-2">{vtranslate('LBL_ADD',$MODULE_NAME)}</span>
                            <span>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</span>
                        </button>
                    </div>
                {/if}
            {/if}
        </div>
        <div class="widget_filter">
            {if $DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_TASKS'}
                <div class="widgetFilterProjectTask p-3 pb-0">
                    {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ProjectTask')}
                    {assign var=PROGRESS_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskprogress')}
                    {assign var=STATUS_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskstatus')}
                    {if $PROGRESS_FIELD_MODEL->isViewableInDetailView()}
                        <div class="d-inline-block me-2">
                            {assign var=FIELD_INFO value=$PROGRESS_FIELD_MODEL->getFieldInfo()}
                            {assign var=PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
                            {assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
                            {assign var="SPECIAL_VALIDATOR" value=$PROGRESS_FIELD_MODEL->getValidator()}
                            <select class="select2 form-select w-25" name="{$PROGRESS_FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $PROGRESS_FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
                                <option value="">{vtranslate('LBL_SELECT_PROGRESS',$MODULE_NAME)}</option>
                                {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
                                    <option value="{$PICKLIST_NAME}" {if $PROGRESS_FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                    {if $STATUS_FIELD_MODEL->isViewableInDetailView()}
                        <div class="d-inline-block">
                            {assign var=FIELD_INFO value=$STATUS_FIELD_MODEL->getFieldInfo()}
                            {assign var=PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
                            {assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
                            {assign var="SPECIAL_VALIDATOR" value=$STATUS_FIELD_MODEL->getValidator()}
                            <select class="select2 form-select w-25" name="{$STATUS_FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $STATUS_FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
                                <option value="">{vtranslate('LBL_SELECT_STATUS',$MODULE_NAME)}</option>
                                {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
                                    <option value="{$PICKLIST_NAME}" {if $STATUS_FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
                                {/foreach}
                            </select>
                        </div>
                    {/if}
                </div>
            {/if}
            {if $DETAIL_VIEW_WIDGET->getLabel() eq 'HelpDesk'}
                <div class="widgetFilterHelpDesk p-3 pb-0">
                    <div class="d-inline-block">
                        {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('HelpDesk')}
                        {assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('ticketstatus')}
                        {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                        {assign var=PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
                        {assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
                        {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
                        <select class="select2 form-select w-25" name="{$FIELD_MODEL->get('name')}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} >
                            <option value="">{vtranslate('LBL_SELECT_STATUS',$MODULE_NAME)}</option>
                            {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
                                <option value="{$PICKLIST_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{$PICKLIST_VALUE}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/if}
        </div>
        <div class="widget_contents p-3">
        </div>
    </div>
</div>
{/strip}