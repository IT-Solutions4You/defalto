{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="modal-dialog modelContainer modal-lg">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_PROJECT_TASK_STATUS_COLOR', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form id="editColor" class="form-horizontal">
                <div class="modal-body">
                    <div class="form-group row py-2">
                        <label class="control-label col-lg-4 text-secondary" for="editFieldsList">{vtranslate('LBL_SELECT_STATUS', $MODULE)}</label>
                        <div class="controls col-lg">
                            <select id="editFieldsList" class="select2" name="taskstatus" style="min-width: 250px;">
                                {foreach from=$TASK_STATUS item=STATUS_NAME}
                                    {assign var=STATUS_NAME value=trim($STATUS_NAME)}
                                    <option value="{$STATUS_NAME}" {if $STATUS eq $STATUS_NAME} selected {/if} data-color="{$TASK_STATUS_COLOR[$STATUS_NAME]}">{vtranslate($STATUS_NAME,'ProjectTask')}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group row py-2">
                        <label class="control-label col-lg-4 text-secondary" for="selectedColor">{vtranslate('LBL_SELECT_PROJECT_TASK_STATUS_COLOR', $MODULE)}</label>
                        <div class="controls col-lg">
                            <input type="color" id="selectedColor" class="selectedColor form-control form-control-color" name="selectedColor" value="" />
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}