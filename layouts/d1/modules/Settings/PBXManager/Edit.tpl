{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="px-4 pb-4">
        <div class="rounded bg-body">
            <div class="widget_header border-bottom p-3">
                <h4>{vtranslate('LBL_PBXMANAGER', $QUALIFIED_MODULE)}</h4>
            </div>
            <div class="container-fluid">
                {assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
                <form id="MyModal" class="form-horizontal" data-detail-url="{$MODULE_MODEL->getDetailViewUrl()}">
                    <input type="hidden" name="module" value="PBXManager"/>
                    <input type="hidden" name="action" value="SaveAjax"/>
                    <input type="hidden" name="parent" value="Settings"/>
                    <input type="hidden" name="id" value="{$RECORD_ID}">
                    <div class="blockData">
                        <div class="container-fluid p-3">
                            {assign var=FIELDS value=PBXManager_PBXManager_Connector::getSettingsParameters()}
                            {foreach item=FIELD_TYPE key=FIELD_NAME from=$FIELDS}
                                <div class="row my-3">
                                    <div class="col-lg-3 fieldLabel control-label">
                                        <label>
                                            <span>{vtranslate($FIELD_NAME, $QUALIFIED_MODULE)}</span>
                                            <span class="text-danger ms-2">*</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-4">
                                        <input class="inputElement form-control fieldValue" type="{$FIELD_TYPE}" name="{$FIELD_NAME}" data-rule-required="true" value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
                                    </div>
                                </div>
                            {/foreach}
                            <div class="row my-3">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-4">
                                    <div class="alert alert-info">
                                        <p>
                                            <b>{vtranslate('LBL_NOTE', $QUALIFIED_MODULE)}</b>
                                            <span class="ms-2">{vtranslate('LBL_INFO_WEBAPP_URL', $QUALIFIED_MODULE)}</span>
                                        </p>
                                        <p>{vtranslate('LBL_FORMAT_WEBAPP_URL', $QUALIFIED_MODULE)}</p>
                                        <p>{vtranslate('LBL_FORMAT_INFO_WEBAPP_URL', $QUALIFIED_MODULE)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-overlay-footer modal-footer py-3">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col text-end">
                                    <a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/strip}