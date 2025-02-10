{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="record" value="{$RECORD}">
                <input type="hidden" name="action" value="SaveTask">
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
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
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