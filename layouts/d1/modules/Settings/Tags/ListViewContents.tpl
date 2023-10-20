{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    {include file="ListViewContents.tpl"|vtemplate_path:'Settings:Vtiger'}

    <div id="editTagContainer" class="hide modal-dialog modelContainer">
        <input type="hidden" name="id" value="" />
        <div class="modal-content">
            {assign var="HEADER_TITLE" value={vtranslate('LBL_EDIT_TAG', $QUALIFIED_MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="editTagContents modal-body">
                <div class="row">
                    <div class="col-lg-4"></div>
                    <div class="col-lg">
                        <div class="my-3">
                            <input type="text" name="tagName" class="inputElement form-control" value=""/>
                        </div>
                        <div class="checkbox my-3">
                            <label class="form-check">
                                <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                                <input class="form-check-input" type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" style="vertical-align: text-top;"/>
                                <span class="ms-2">{vtranslate('LBL_SHARE_TAGS',$MODULE)}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col text-end">
                            <a href="#" class="btn btn-primary cancelLink cancelSaveTag" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <div class="col">
                            <button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-primary active saveTag" type="submit" name="saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}