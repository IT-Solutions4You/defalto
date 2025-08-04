{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
                    <div class="row py-2">
                        <div class="fieldLabel text-secondary {if $FIELD_MODEL->isTableFullWidth()}col-sm-2{else}col-sm-4{/if}">
                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                            {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
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