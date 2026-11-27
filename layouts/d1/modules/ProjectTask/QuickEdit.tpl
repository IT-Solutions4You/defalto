{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

    <div class="modal-dialog modelContainer modal-lg">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate($SINGLE_MODULE, $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form id="projectTaskQuickEditForm" class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
                {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                    <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
                {/if}
                <input type="hidden" class="project-task-field-info" value="{Vtiger_Util_Helper::toSafeHTML($FIELDS_INFO)}">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="record" value="{$RECORD}">
                <div class="quickCreateContent">
                    <div class="modal-body">
                        <div class="container-fluid">
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
                                <div class="row py-2">
                                    <div class="fieldLabel col-lg-4">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                                    </div>
                                    <div class="fieldValue col-lg-8">
                                        {if $FIELD_MODEL->isEditable()}
                                            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                        {else}
                                            {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)|strip_tags|escape:'html':'UTF-8':false}
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
                {include file="partials/EditViewReturn.tpl"|vtemplate_path:$MODULE}
            </form>
        </div>

    </div>
{/strip}
