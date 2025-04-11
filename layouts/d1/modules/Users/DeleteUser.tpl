{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Users/views/DeleteUser.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('Transfer records to user', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="deleteUser" name="deleteUser" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="userid" value="{$USERID}" />
                <div name='massEditContent'>
                    <div class="modal-body">
                        <div class="row form-group py-2">
                            <label class="control-label fieldLabel col-5">{vtranslate('User to be deleted', $MODULE)}</label>
                            <label class="control fieldValue col">{$DELETE_USER_NAME}</label>
                        </div>
                        <div class="row form-group py-2">
                           <label class="control-label fieldLabel col-5">{vtranslate('Transfer records to user', $MODULE)}</label>
                           <div class="controls fieldValue col">
                               <select class="select2 {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="tranfer_owner_id" data-validation-engine="validate[required]" >
                                   {foreach item=USER_MODEL key=USER_ID from=$USER_LIST}
                                       <option value="{$USER_ID}" >{$USER_MODEL->getName()}</option>
                                   {/foreach}
                               </select>
                           </div>
                        </div>
                        {if !$PERMANENT}
                            <div class="row form-group py-2">
                                <label class="control-label fieldLabel col-sm-5"></label>
                                <div class="controls fieldValue col-sm-8">
                                    <input type="checkbox" name="deleteUserPermanent" value="1" >
                                    <span class="mx-2">{vtranslate('LBL_DELETE_USER_PERMANENTLY',$MODULE)}</span>
                                    <i class="fa fa-question-circle" data-toggle="tooltip"  data-placement="right" title="{vtranslate('LBL_DELETE_USER_PERMANENTLY_INFO',$MODULE)}"></i>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}

