{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div id="massEditContainer" class="modal-dialog modelContainer">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_CHANGE_USERNAME', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="changeUsername" name="changeUsername" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="userid" value="{$USER_MODEL->getId()}" />
                <input type="hidden" name="username" value="{$USER_MODEL->get('user_name')}" />
                <div name="massEditContent">
                    <div class="modal-body ">
                        <div class="form-group my-3">
                            <label class="fieldLabel w-100">
                                <span class="me-2">{vtranslate('New Username', $MODULE)}</span>
                                <span class="text-danger">*</span>
                                <input type="text" class="form-control " name="new_username" data-rule-required="true" data-rule-illegal="true"/>
                            </label>
                        </div>

                        <div class="form-group my-3">
                            <label class="fieldLabel w-100">
                                <span class="me-2">{vtranslate('LBL_NEW_PASSWORD', $MODULE)}</span>
                                <span class="text-danger">*</span>
                                <input type="password" class="form-control" name="new_password" data-rule-required="true"/>
                            </label>
                        </div>

                        <div class="form-group my-3">
                            <label class="fieldLabel w-100">
                                <span class="me-2">{vtranslate('LBL_CONFIRM_PASSWORD', $MODULE)}</span>
                                <span class="text-danger">*</span>
                                <input type="password" class="form-control" name="confirm_password" data-rule-required="true"/>
                            </label>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>    
{/strip}
