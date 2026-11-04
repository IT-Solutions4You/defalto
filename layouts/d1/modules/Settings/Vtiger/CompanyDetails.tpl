{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Vtiger/views/CompanyDetails.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="px-4 pb-4">
        <div>
            <input type="hidden" id="supportedImageFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}'/>
            <input type="hidden" id="maximumLogoFileSize" value="{$MODULE_MODEL->getLogoMaxFileSize()}" data-size-megabytes="{$MODULE_MODEL->getLogoMaxFileSizeInMegabytes()}"/>
            {if !empty($ERROR_MESSAGE)}
                <div class="alert alert-danger mt-3" role="alert">
                    {vtranslate($ERROR_MESSAGE, $QUALIFIED_MODULE)}
                </div>
            {/if}
            <div class="container-fluid p-3 bg-body rounded">
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
                {foreach from=$MODULE_MODEL->getBlocks() item=BLOCK_NAME}
                    <div class="block bg-body rounded mt-3">
                        <div class="border-bottom p-3">
                            <span class="fs-4 fw-bold">{vtranslate($BLOCK_NAME,$QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="blockData p-3">
                            <div class="container-fluid">
                                {if 'LBL_COMPANY_LOGO' eq $BLOCK_NAME}
                                    <div class="row py-2 border-bottom">
                                        <div class="col-lg-3 fieldLabel"></div>
                                        <div class="col-lg-6 fieldValue">
                                            <div class="companyLogo">
                                                <img src="{$MODULE_MODEL->getLogoPath()}" width="240" alt="{vtranslate('LBL_COMPANY_LOGO', $QUALIFIED_MODULE)}"/>
                                            </div>
                                        </div>
                                    </div>
                                {else}
                                    {foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD_NAME}
                                        {if !$MODULE_MODEL->isBlockField($BLOCK_NAME, $FIELD_NAME)}{continue}{/if}
                                        <div class="row py-3 border-bottom">
                                            <div class="col-lg-3 fieldLabel">
                                                <label>{vtranslate($FIELD_NAME,$QUALIFIED_MODULE)}</label>
                                            </div>
                                            <div class="col-lg-6 fieldValue">
                                                {$MODULE_MODEL->getDisplayValue($FIELD_NAME)}
                                            </div>
                                        </div>
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="editViewContainer bg-body rounded mt-3">
                <form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}" id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
                    <input type="hidden" name="module" value="Vtiger"/>
                    <input type="hidden" name="parent" value="Settings"/>
                    <input type="hidden" name="action" value="CompanyDetailsSave"/>
                    {foreach from=$MODULE_MODEL->getBlocks() item=BLOCK_NAME}
                        <div class="border-bottom">
                            <div class="p-3">
                                <span class="fs-4 fw-bold">{vtranslate($BLOCK_NAME,$QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                        <div class="p-3">
                            {if 'LBL_COMPANY_LOGO' eq $BLOCK_NAME}
                                <div class="row form-group companydetailsedit py-2">
                                    <div class="col-lg-3 fieldLabel"></div>
                                    <div class="col-lg-6 fieldValue">
                                        <div class="company-logo-content rounded border mb-3">
                                            <div class="companyLogoPreview position-relative">
                                                <img src="{$MODULE_MODEL->getLogoPath()}" width="240" alt="{vtranslate('LBL_COMPANY_LOGO', $QUALIFIED_MODULE)}"/>
                                                {if $MODULE_MODEL->hasCustomLogo()}
                                                    <button type="button" class="removeCompanyLogo btn btn-sm btn-danger position-absolute top-0 end-0" title="{vtranslate('LBL_REMOVE_COMPANY_LOGO', $QUALIFIED_MODULE)}" aria-label="{vtranslate('LBL_REMOVE_COMPANY_LOGO', $QUALIFIED_MODULE)}">
                                                        <i class="fa fa-times" aria-hidden="true"></i>
                                                    </button>
                                                {/if}
                                            </div>
                                            <div class="companyLogoRemovalMessage alert alert-warning m-2 hide">
                                                {vtranslate('LBL_COMPANY_LOGO_WILL_BE_REMOVED', $QUALIFIED_MODULE)}
                                            </div>
                                            <hr>
                                            <input type="hidden" name="remove_logo" id="removeCompanyLogo" value="0"/>
                                            <input type="file" name="logo" id="logoFile" accept=".jpeg,.jpg,.png,.gif,image/jpeg,image/png,image/gif"/>
                                        </div>
                                        <div class="alert alert-info m-0">
                                            {vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
                                        </div>
                                    </div>
                                </div>
                            {else}
                                {foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD_NAME}
                                    {if !$MODULE_MODEL->isBlockField($BLOCK_NAME, $FIELD_NAME)}{continue}{/if}
                                    <div class="row form-group companydetailsedit py-2">
                                        <label class="col-lg-3 fieldLabel control-label">
                                            {vtranslate($FIELD_NAME,$QUALIFIED_MODULE)}{if $FIELD_NAME eq 'organizationname'}<span class="text-danger ms-2">*</span>{/if}
                                        </label>
                                        <div class="col-lg-6 fieldValue">
                                            {if $MODULE_MODEL->isTextareaField($FIELD_NAME)}
                                                <textarea class="form-control resize-vertical" rows="2" name="{$FIELD_NAME}">{$MODULE_MODEL->get($FIELD_NAME)}</textarea>
                                            {elseif $FIELD_NAME eq 'website'}
                                                <input type="text" class="form-control inputElement" data-rule-url="true" name="{$FIELD_NAME}" value="{$MODULE_MODEL->get($FIELD_NAME)}"/>
                                            {elseif $MODULE_MODEL->isCountryField($FIELD_NAME)}
                                                <select name="{$FIELD_NAME}" class="select2 form-select">
                                                    {foreach from=$MODULE_MODEL->getCountries() key=COUNTRY_CODE item=COUNTRY_NAME}
                                                        <option value="{$COUNTRY_CODE}" {if $COUNTRY_CODE eq $MODULE_MODEL->get($FIELD_NAME)}selected="selected"{/if}>{$COUNTRY_NAME}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                <input type="text" {if $FIELD_NAME eq 'organizationname'} data-rule-required="true" {/if} class="form-control inputElement" name="{$FIELD_NAME}" value="{$MODULE_MODEL->get($FIELD_NAME)}"/>
                                            {/if}
                                        </div>
                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    {/foreach}
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
