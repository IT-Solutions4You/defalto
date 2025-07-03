{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="modal-dialog modal-lg">
        <div id="convertLeadContainer" class="modelContainer modal-content">
            {if !$CONVERT_LEAD_FIELDS['Accounts'] && !$CONVERT_LEAD_FIELDS['Contacts']}
                <input type="hidden" id="convertLeadErrorTitle" value="{vtranslate('LBL_CONVERT_ERROR_TITLE',$MODULE)}"/>
                <input id="convertLeadError" class="convertLeadError" type="hidden" value="{vtranslate('LBL_CONVERT_LEAD_ERROR',$MODULE)}"/>
            {else}
                {assign var=HEADER_TITLE value={vtranslate('LBL_CONVERT_LEAD', $MODULE)}|cat:" "|cat:{$RECORD->getName()}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <form class="form-horizontal" id="convertLeadForm" method="post" action="index.php">
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="view" value="SaveConvertLead"/>
                    <input type="hidden" name="record" value="{$RECORD->getId()}"/>
                    <input type="hidden" name="modules" value=''/>
                    <input type="hidden" name="imageAttachmentId" value="{$IMAGE_ATTACHMENT_ID}">
                    <input type="hidden" name="transferModule" value="Contacts"/>
                    {assign var=LEAD_COMPANY_NAME value=$RECORD->get('company')}
                    <div class="modal-body accordion container-fluid" id="leadAccordion">
                        {foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_LEAD_FIELDS}
                            <div class="row">
                                <div class="col-lg-12 moduleContent">
                                    <div class="convertLeadModules">
                                        <div id="{$MODULE_NAME}_FieldInfo" class="fieldInfo {$MODULE_NAME}_FieldInfo {if $IS_CHECKED_MODULE}show{/if}">
                                            {foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
                                                <div class="row py-2">
                                                    <div class="fieldLabel col-lg-4 text-end">
                                                        <label class='muted'>
                                                            <span class="me-2">{vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}</span>
                                                            {if $FIELD_MODEL->isMandatory() eq true}
                                                                <span class="text-danger">*</span>
                                                            {/if}
                                                        </label>
                                                    </div>
                                                    <div class="fieldValue col-lg-8">
                                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName())}
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        <div class="defaultFields">
                            <div class="row py-2">
                                {assign var=FIELD_MODEL value=$ASSIGN_TO}
                                <div class="fieldLabel col-lg-4 text-secondary text-end">
                                    <label>
                                        <span>{vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}</span>
                                        <span class="text-danger ms-2">*</span>
                                    </label>
                                </div>
                                <div class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </div>
                            </div>
                        </div>
                    </div>
                    {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
                </form>
            {/if}
        </div>
    </div>
{/strip}
