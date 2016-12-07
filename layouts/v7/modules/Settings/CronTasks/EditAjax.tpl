{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="modal-dialog modelContainer">
        {assign var=HEADER_TITLE value={vtranslate($RECORD_MODEL->get('name'), $QUALIFIED_MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
            <form class="form-horizontal" id="cronJobSaveAjax" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="action" value="SaveAjax" />
                <input  type="hidden" name="record" value="{$RECORD}" />
                <input  type="hidden" name="cronjob" value="{$RECORD_MODEL->get('name')}" />
                <input  type="hidden" name="oldstatus" value="{$RECORD_MODEL->get('status')}" />

                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</label>
                        <div class="controls fieldValue col-xs-6">
                            <select class="select2" name="status">
                                <option {if $RECORD_MODEL->get('status') eq 1} selected="" {/if} value="1">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
                                <option {if $RECORD_MODEL->get('status') eq 0} selected="" {/if} value="0">{vtranslate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
                            </select>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>	
    </div>
{/strip}	
