{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Roles/views/DeleteAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_ROLE', $QUALIFIED_MODULE)}|cat:" - "|cat:{$RECORD_MODEL->getName()}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="roleDeleteForm" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="parent" value="Settings"/>
                <input type="hidden" name="action" value="Delete"/>
                <input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}"/>
                <div name="massEditContent">
                    <div class="modal-body">
                        <div class="py-2">
                            <div class="control-label fieldLabel">
                                <span>{vtranslate('LBL_TRANSFER_TO_OTHER_ROLE',$QUALIFIED_MODULE)}</span>
                                <span class="text-danger ms-2">*</span>
                            </div>
                        </div>
                        <div class="input-group fieldValue py-2">
                            <input id="transfer_record_display" data-rule-required='true' name="transfer_record_display" type="text" class="inputElement form-control" value="">
                            <input id="transfer_record" name="transfer_record" type="hidden" value="" class="sourceField" data-rule-required="true">
                            <a href="#" id="clearRole" class="input-group-text clearReferenceSelection hide cursorPointer" name="clearToEmailField">
                                <i class="fa fa-close"></i>
                            </a>
                            <span class="input-group-text cursorPointer relatedPopup" data-field="transfer_record" data-action="popup" data-url="{$RECORD_MODEL->getPopupWindowUrl()}&type=Transfer">
                                <i class="fa fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}



