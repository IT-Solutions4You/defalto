{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <input type=hidden name="_mlinkto" value="{$PARENT}">
	<input type=hidden name="_mlinktotype" value="{$LINKMODULE}">
	<input type=hidden name="_msguid" value="{$UID}">
	<input type=hidden name="_folder" value="{$FOLDER}">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_MAILMANAGER_ADD_ModComments', $MODULE)}
        <div class="modal-body" id='commentContainer'>
            <div class="container-fluid">
                {if $FIELD_MODEL}
                    {assign var=REFERENCE_LIST value=$FIELD_MODEL->getReferenceList()}
                    <div class="row py-2">
                        <div class="fieldLabel text-secondary {if $FIELD_MODEL->isTableFullWidth()}col-sm-2{else}col-sm-4{/if}">
                            <div class="d-flex">
                                {if !empty($REFERENCE_LIST)}
                                    {assign var=REFERENCED_MODULE_ID value=$FIELD_MODEL->get('fieldvalue')}
                                    {assign var=REFERENCED_MODULE_STRUCTURE value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($REFERENCED_MODULE_ID)}
                                    {if !empty($REFERENCED_MODULE_STRUCTURE)}
                                        {assign var=REFERENCED_MODULE_NAME value=$REFERENCED_MODULE_STRUCTURE->get('name')}
                                    {/if}
                                    <select class="select2 referenceModulesList" data-width="100%">
                                        {foreach item=REFERENCE_MODULE from=$REFERENCE_LIST}
                                            <option value="{$REFERENCE_MODULE}" {if $REFERENCE_MODULE eq $REFERENCED_MODULE_NAME} selected="selected" {/if}>{vtranslate($REFERENCE_MODULE, $REFERENCE_MODULE)}</option>
                                        {/foreach}
                                    </select>
                                {/if}
                                {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                            </div>
                        </div>
                        <div class="fieldValue {if $FIELD_MODEL->isTableFullWidth()}col-sm-10{else}col-sm-8{/if} {if $FIELD_MODEL->get('uitype') eq '56'}checkBoxType{/if}">
                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                        </div>
                    </div>
                {/if}
                <div class="row py-2" id="mass_action_add_comment">
                    <div class="col-lg-12">
                        <textarea class="form-control h-50vh" name="commentcontent" id="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" placeholder="{vtranslate('LBL_WRITE_YOUR_COMMENT_HERE', $MODULE)}..." data-rule-required="true">{$COMMENTCONTENT}</textarea>
                    </div>
                </div>
            </div>
        </div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </div>
</div>
{/strip}