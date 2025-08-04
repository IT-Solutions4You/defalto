{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modelContainer">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="transferOwner" method="post">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="action" value="SaveAjax">
                <input type="hidden" name="mode" value="transferOwner">
                <div name='massEditContent'>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label fieldLabel mb-3">{vtranslate('LBL_TRANSFER_OWNERSHIP_TO_USER', $MODULE)}</label>
                            <div class="controls fieldValue">
                                <select class="select2" name="record" style="width: 50%;">
                                    {foreach from=$USERS_MODEL key=USER_ID item=USER_MODEL}
                                        <option value="{$USER_ID}">{$USER_MODEL->getDisplayName()}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}

