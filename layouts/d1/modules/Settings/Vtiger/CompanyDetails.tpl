{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/CompanyDetails.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="px-4 pb-4">
        <div class="bg-body rounded">
            <input type="hidden" id="supportedImageFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}'/>
            <div class="container-fluid p-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="m-0">{vtranslate('LBL_COMPANY_DETAILS', $QUALIFIED_MODULE)}</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group editbutton-container">
                            <button id="updateCompanyDetails" class="btn btn-outline-secondary ">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="CompanyDetailsContainer" class="detailViewContainer {if !empty($ERROR_MESSAGE)}hide{/if}">
                <div class="block">
                    <div class="border-bottom p-3">
                        <span class="fs-4 fw-bold">{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</span>
                    </div>
                    <div class="blockData p-3">
                        <div class="container-fluid">
                            <div class="row py-3 border-bottom">
                                <div class="col-lg-3 fieldLabel"></div>
                                <div class="col-lg-6 fieldValue">
                                    <div class="companyLogo">
                                        {if $MODULE_MODEL->getLogoPath()}
                                            <img src="{$MODULE_MODEL->getLogoPath()}" width="240"/>
                                        {else}
                                            {vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block">
                    <div class="border-bottom p-3">
                        <span class="fs-4 fw-bold">{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</span>
                    </div>
                    <div class="blockData p-3">
                        <div class="container-fluid">
                            {foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
                                {if $FIELD neq 'logoname' && $FIELD neq 'logo' }
                                    <div class="row py-3 border-bottom">
                                        <div class="col-lg-3 fieldLabel">
                                            <label>{vtranslate($FIELD,$QUALIFIED_MODULE)}</label>
                                        </div>
                                        <div class="col-lg-6 fieldValue">
                                            {if $FIELD eq 'address'} {decode_html($MODULE_MODEL->get($FIELD))|nl2br} {else} {decode_html($MODULE_MODEL->get($FIELD))} {/if}
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            <div class="editViewContainer">
                <form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}" id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
                    <input type="hidden" name="module" value="Vtiger"/>
                    <input type="hidden" name="parent" value="Settings"/>
                    <input type="hidden" name="action" value="CompanyDetailsSave"/>
                    <div class="border-bottom">
                        <div class="border-bottom p-3">
                            <span class="fs-4 fw-bold">{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="p-3">
                            <div class="row form-group companydetailsedit py-2">
                                <div class="col-lg-3 fieldLabel"></div>
                                <div class="col-lg-6 fieldValue">
                                    <div class="company-logo-content rounded border mb-3">
                                        <img src="{$MODULE_MODEL->getLogoPath()}" width="240"/>
                                        <hr>
                                        <input type="file" name="logo" id="logoFile"/>
                                    </div>
                                    <div class="alert alert-info m-0">
                                        {vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-bottom p-3">
                            <span class="fs-4 fw-bold">{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="p-3">
                            {foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
                                {if $FIELD neq 'logoname' && $FIELD neq 'logo' }
                                    <div class="row form-group companydetailsedit py-2">
                                        <label class="col-lg-3 fieldLabel control-label ">
                                            {vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'organizationname'}&nbsp;<span class="redColor">*</span>{/if}
                                        </label>
                                        <div class="col-lg-6 fieldValue">
                                            {if $FIELD eq 'address'}
                                                <textarea class="form-control resize-vertical" rows="2" name="{$FIELD}">{$MODULE_MODEL->get($FIELD)}</textarea>
                                            {elseif $FIELD eq 'website'}
                                                <input type="text" class="form-control inputElement" data-rule-url="true" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
                                            {else}
                                                <input type="text" {if $FIELD eq 'organizationname'} data-rule-required="true" {/if} class="form-control inputElement" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                    <div class="container-fluid py-3">
                        <div class="row">
                            <div class="col text-end">
                                <a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
{/strip}
