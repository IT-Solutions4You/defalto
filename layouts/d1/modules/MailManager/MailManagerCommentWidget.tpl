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
                <div class="row" id="mass_action_add_comment">
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