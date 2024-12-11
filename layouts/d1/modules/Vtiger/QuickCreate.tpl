{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/QuickCreateAjax.php *}
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form class="form-horizontal recordEditView" id="QuickCreate" name="QuickCreate" method="post" action="index.php">
                {assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate($SINGLE_MODULE, $MODULE)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                    
                <div class="modal-body">
                    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
                    {/if}
                    <input type="hidden" name="module" value="{$MODULE}">
                    <input type="hidden" name="action" value="SaveAjax">
                    <div class="quickCreateContent">
                        <div class="massEditTable">
                            <div class="row">
                                {foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
                                    {if $FIELD_MODEL->isTableCustomWidth()}
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                    {else}
                                        <div class="py-2 {if $FIELD_MODEL->isTableFullWidth()}col-lg-12{else}col-lg-6{/if}">
                                            <div class="row">
                                                <div class="fieldLabel col-lg-12 pb-2 text-secondary">
                                                    <span>{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</span>
                                                    {if $FIELD_MODEL->isMandatory() eq true}
                                                        <span class="text-danger ms-2">*</span>
                                                    {/if}
                                                </div>
                                                <div class="fieldValue col-lg-12">
                                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6">
                                <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col-6 text-end">
                                {if $BUTTON_NAME neq null}
                                    {assign var=BUTTON_LABEL value=$BUTTON_NAME}
                                {else}
                                    {assign var=BUTTON_LABEL value={vtranslate('LBL_SAVE', $MODULE)}}
                                {/if}
                                {assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
                                <button class="btn btn-primary me-2" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
                                <button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-primary active" type="submit" name="saveButton"><strong>{$BUTTON_LABEL}</strong></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
            {if $FIELDS_INFO neq null}
                <script type="text/javascript">
                    var quickcreate_uimeta = (function() {
                        var fieldInfo  = {$FIELDS_INFO};
                        return {
                            field: {
                                get: function(name, property) {
                                    if(name && property === undefined) {
                                        return fieldInfo[name];
                                    }
                                    if(name && property) {
                                        return fieldInfo[name][property]
                                    }
                                },
                                isMandatory : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].mandatory;
                                    }
                                    return false;
                                },
                                getType : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].type
                                    }
                                    return false;
                                }
                            },
                        };
                    })();
                </script>
            {/if}
    </div>
{/strip}