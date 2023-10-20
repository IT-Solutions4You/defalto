{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
    <input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}'/>
    <input type='hidden' name='pwd_regex' value= {ZEND_json::encode($PWD_REGEX)}/>
    {foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
        {if $BLOCK_LABEL_KEY neq 'LBL_CALENDAR_SETTINGS'}
            {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
            {if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
            <div class="mb-3 bg-body rounded block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
                {assign var=IS_HIDDEN value=$BLOCK->isHidden()}
                {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                <div class="p-3 border-bottom">
                    <h4>{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h4>
                </div>
                <div class="blockData p-3">
                    <div class="container-fluid detailview-table no-border">
                        <div class="row">
                            {foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
                                {assign var=IS_FULL_WIDTH value=$FIELD_MODEL->isTableFullWidth()}
                                {assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
                                {if !$FIELD_MODEL->isViewableInDetailView()}
                                    {continue}
                                {/if}
                                {if $FIELD_MODEL->getName() eq 'theme' or $FIELD_MODEL->getName() eq 'rowheight'}
                                    {continue}
                                {/if}
                                {if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
                                    <div class="col-lg-12 py-2">
                                        <div class="row">
                                            <div class="fieldLabel textOverflowEllipsis col-lg-2">
                                                <span class="muted">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</span>
                                            </div>
                                            <div class="fieldValue col-lg-10">
                                                <div id="imageContainer">
                                                    {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
                                                        {if !empty($IMAGE_INFO.url)}
                                                            <img class="rounded" src="{$IMAGE_INFO.url}" style="max-width: 10rem; max-height: 10rem">
                                                        {/if}
                                                    {/foreach}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {else}
                                    <div class="py-2 {if $IS_FULL_WIDTH}col-lg-12{else}col-lg-6{/if}">
                                        <div class="row py-2 border-bottom">
                                            <div class="fieldLabel textOverflowEllipsis {if $IS_FULL_WIDTH}col-lg-2{else}col-lg-4{/if}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
												<span class="muted">
													{if $MODULE_NAME eq 'Documents' && $FIELD_MODEL->get('label') eq "File Name" && $RECORD->get('filelocationtype') eq 'E'}
                                                        {vtranslate("LBL_FILE_URL",{$MODULE_NAME})}
                                                    {else}
                                                        {vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
                                                    {/if}
                                                    {if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
                                                        ({$BASE_CURRENCY_SYMBOL})
                                                    {/if}
												</span>
                                            </div>
                                            <div class="fieldValue {if $IS_FULL_WIDTH}col-lg-10{else}col-lg-8{/if}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}">
                                                {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
                                                {if $fieldDataType eq 'multipicklist'}
                                                    {assign var=FIELD_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
                                                {else}
                                                    {assign var=FIELD_DISPLAY_VALUE value=Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}
                                                {/if}
                                                <span class="value textOverflowEllipsis" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $fieldDataType eq 'email'}title="{$FIELD_MODEL->get('fieldvalue')}"{/if} >
												{if $FIELD_MODEL->getName() neq 'defaultlandingpage'}
                                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                                                {else}
                                                    {vtranslate($FIELD_MODEL->get('fieldvalue'),$FIELD_MODEL->get('fieldvalue'))}
                                                {/if}
											</span>
                                                {if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true'}
                                                    <span class="hide edit">
                                                        {if $fieldDataType eq 'multipicklist'}
                                                            <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}"/>
                                                        {else}
                                                            <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}"/>
                                                        {/if}
												    </span>
                                                    <span class="action pull-right"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    {/foreach}
{/strip}