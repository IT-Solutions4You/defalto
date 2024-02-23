{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<form class="px-4 pb-4" id="detailView" data-name-fields="{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}" method="POST">
    <div class="contents bg-body rounded">
    {foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
        <div class="block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}">
            {assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
            {if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
            {assign var=IS_HIDDEN value=$BLOCK->isHidden()}
            {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
            <input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}'/>
            <div class="container-fluid border-bottom p-3">
                <div class="row align-items-center">
                    <div class="col-lg">
                        <h4 class="m-0">{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h4>
                    </div>
                    <div class="col-lg-auto">
                        <div class="detailViewButtoncontainer">
                            <div class="btn-group">
                                <a class="btn btn-outline-secondary" href="{$RECORD->getCalendarSettingsEditViewUrl()}">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="blockData p-3">
                <div class="container-fluid detailview-table">
                    <div class="row">
                        {foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
                            {assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
                            {if !$FIELD_MODEL->isViewableInDetailView()}
                                {continue}
                            {/if}
                            <div class="py-2 col-lg-6 border-bottom">
                                <div class="row">
                                    {if $FIELD_MODEL->get('uitype') eq "83"}
                                        {foreach item=tax key=count from=$TAXCLASS_DETAILS}
                                            <div class="py-2 col-sm-4 fieldLabel {$WIDTHTYPE}">
                                                <span class='muted'>{vtranslate($tax.taxlabel, $MODULE)}(%)</span>
                                            </div>
                                            <div class="py-2 col-sm fieldValue {$WIDTHTYPE}">
                                                <span class="value text-truncate" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
                                                    {if $tax.check_value eq 1}
                                                        {$tax.percentage}
                                                    {else}
                                                        0
                                                    {/if}
                                                </span>
                                            </div>
                                        {/foreach}
                                    {elseif $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
                                        <div class="py-2 col-sm-4 fieldLabel {$WIDTHTYPE}">
                                            <span class="text-muted">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</span>
                                        </div>
                                        <div class="py-2 col-sm fieldValue {$WIDTHTYPE}">
                                            <div id="imageContainer">
                                                {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
                                                    {if !empty($IMAGE_INFO.url)}
                                                        <img class="rounded" src="{$IMAGE_INFO.url}" style="max-width: 10rem; max-height: 10rem">
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    {else}
                                        <div class="py-2 col-sm-4 fieldLabel text-truncate {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getName() eq 'description' or $FIELD_MODEL->get('uitype') eq '69'} style='width:8%'{/if}>
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
                                        <div class="py-2 col-sm fieldValue {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $fieldDataType eq 'reminder' or $fieldDataType eq 'recurrence'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>

                                            {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
                                            {if $fieldDataType eq 'multipicklist'}
                                                {assign var=FIELD_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
                                            {else}
                                                {assign var=FIELD_DISPLAY_VALUE value=Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}
                                            {/if}

                                            <span class="value text-truncate" data-field-type="{$FIELD_MODEL->getFieldDataType()}"  {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_MODEL->get('uitype') eq '21'} style="white-space:normal;" {/if}>
                                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                                            </span>
                                            {if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true'}
                                                <div class="hide edit calendar-timezone clearfix">
                                                    {if $fieldDataType eq 'multipicklist'}
                                                        <input type="hidden" class="fieldBasicData" data-name="{$FIELD_MODEL->get('name')}[]" data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}"/>
                                                    {else}
                                                        <input type="hidden" class="fieldBasicData" data-name="{$FIELD_MODEL->get('name')}" data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}"/>
                                                    {/if}
                                                </div>
                                                <span class="action float-end"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
{/strip}