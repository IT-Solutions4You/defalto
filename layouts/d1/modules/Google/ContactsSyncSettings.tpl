{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="modal-dialog modal-lg googleSettings" style="min-width: 800px;">
    <div class="modal-content" >
        {assign var=HEADER_TITLE value={vtranslate('LBL_FIELD_MAPPING', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <form class="form-horizontal" name="contactsyncsettings">
            <input type="hidden" name="module" value="{$MODULENAME}" />
            <input type="hidden" name="action" value="SaveSettings" />
            <input type="hidden" name="sourcemodule" value="{$SOURCE_MODULE}" />
            <input id="user_field_mapping" type="hidden" name="fieldmapping" value="fieldmappings" />
            <input id="google_fields" type="hidden" value='{Zend_Json::encode($GOOGLE_FIELDS)}' />
            <div class="modal-body">
                <div class="text-end">
                    <button id="googlesync_addcustommapping" class="btn btn-primary" type="button" data-bs-toggle="dropdown">
                        <span class="caret"></span>&nbsp;{vtranslate('LBL_ADD_CUSTOM_FIELD_MAPPING',$MODULENAME)}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                        <li class="addCustomFieldMapping" data-type="email" data-vtigerfields='{Zend_Json::encode($VTIGER_EMAIL_FIELDS)}'><a class="dropdown-item">{vtranslate('LBL_EMAIL',$MODULENAME)}</a></li>
                        <li class="addCustomFieldMapping" data-type="phone" data-vtigerfields='{Zend_Json::encode($VTIGER_PHONE_FIELDS)}'><a class="dropdown-item">{vtranslate('LBL_PHONE',$MODULENAME)}</a></li>
                        <li class="addCustomFieldMapping" data-type="url" data-vtigerfields='{Zend_Json::encode($VTIGER_URL_FIELDS)}'><a class="dropdown-item">{vtranslate('LBL_URL',$MODULENAME)}</a></li>
                        <li class="dropdown-divider"></li>
                        <li class="addCustomFieldMapping" data-type="custom" data-vtigerfields='{Zend_Json::encode($VTIGER_OTHER_FIELDS)}'><a class="dropdown-item">{vtranslate('LBL_CUSTOM',$MODULENAME)}</a></li>
                    </ul>
                </div>

                <div id="googlesyncfieldmapping" style="margin:15px;">
                    <table  class="table table-bordered">
                        <thead>
                            <tr>
                                <td><b>{vtranslate('APPTITLE',$MODULENAME)}</b></td>
                                <td><b>{vtranslate('EXTENTIONNAME',$MODULENAME)}</b></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                {assign var=FLDNAME value="salutationtype"}
                                <td>
                                    {vtranslate('Salutation',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('Name Prefix',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="firstname"}
                                <td>
                                    {vtranslate('First Name',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('First Name',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="lastname"}
                                <td>
                                    {vtranslate('Last Name',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('Last Name',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="title"}
                                <td>
                                    {vtranslate('Title',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('Job Title',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="account_id"}
                                <td>
                                    {vtranslate('Organization Name',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('Company',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['organizationname']['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="birthday"}
                                <td>
                                    {vtranslate('Date of Birth',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    {vtranslate('Birthday',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="email"}
                                <td>
                                    {vtranslate('Email',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['email']['name']}" />
                                    {assign var="GOOGLE_TYPES" value=$GOOGLE_FIELDS[$FLDNAME]['types']}
                                    <select class="google-type form-select" data-category="email">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[{$FLDNAME}]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Email',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true" />
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="secondaryemail"}
                                <td>
                                    {vtranslate('Secondary Email',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['email']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['email']['types']}
                                    <select class="google-type form-select" data-category="email">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING['secondaryemail']['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Email',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="mobile"}
                                <td>
                                    {vtranslate('Mobile Phone',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['phone']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['phone']['types']}
                                    <select class="stretched google-type form-select" data-category="phone">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Phone',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="phone"}
                                <td>
                                    {vtranslate('Office Phone',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['phone']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['phone']['types']}
                                    <select class="stretched google-type form-select" data-category="phone">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Phone',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}"
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="homephone"}
                                <td>
                                    {vtranslate('Home Phone',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}" />
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['phone']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['phone']['types']}
                                    <select class="stretched google-type form-select" data-category="phone">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Phone',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="mailingaddress"}
                                <td>
                                    {vtranslate('Mailing Address',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}">
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['address']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['address']['types']}
                                    <select class="stretched google-type form-select" data-category="address">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Address',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="otheraddress"}
                                <td>
                                    {vtranslate('Other Address',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}">
                                </td>
                                <td>
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS['address']['name']}" />
                                    {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['address']['types']}
                                    <select class="stretched google-type form-select" data-category="address">
                                        {foreach item=TYPE from=$GOOGLE_TYPES}
                                            <option value="{$TYPE}" {if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Address',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                        {/foreach}
                                    </select>&nbsp;&nbsp;
                                    <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                           value="{if $FIELD_MAPPING[$FLDNAME]['google_field_type'] eq 'custom'}{$FIELD_MAPPING[$FLDNAME]['google_custom_label']}{/if}" 
                                           data-rule-required="true"/>
                                </td>
                            </tr>
                            <tr>
                                {assign var=FLDNAME value="description"}
                                <td>
                                    {vtranslate('Description',$SOURCE_MODULE)}
                                    <input type="hidden" class="vtiger_field_name" value="{$FLDNAME}">
                                </td>
                                <td>
                                    {vtranslate('Note',$MODULENAME)}
                                    <input type="hidden" class="google_field_name" value="{$GOOGLE_FIELDS[$FLDNAME]['name']}" />
                                </td>
                            </tr>
                            {foreach key=VTIGER_FIELD_NAME item=CUSTOM_FIELD_MAP from=$CUSTOM_FIELD_MAPPING}
                                <tr>
                                    <td>
                                        {if $CUSTOM_FIELD_MAP['google_field_name'] eq 'gd:email'}
                                            <select class="stretched vtiger_field_name form-select" data-category="email">
                                                {foreach key=EMAIL_FIELD_NAME item=EMAIL_FIELD_LABEL from=$VTIGER_EMAIL_FIELDS}
                                                    <option value="{$EMAIL_FIELD_NAME}" {if $VTIGER_FIELD_NAME eq $EMAIL_FIELD_NAME}selected{/if}>{vtranslate($EMAIL_FIELD_LABEL,$SOURCE_MODULE)}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gd:phoneNumber'}
                                            <select class="stretched vtiger_field_name form-select" data-category="phone">
                                                {foreach key=PHONE_FIELD_NAME item=PHONE_FIELD_LABEL from=$VTIGER_PHONE_FIELDS}
                                                    <option value="{$PHONE_FIELD_NAME}" {if $VTIGER_FIELD_NAME eq $PHONE_FIELD_NAME}selected{/if}>{vtranslate($PHONE_FIELD_LABEL,$SOURCE_MODULE)}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gContact:userDefinedField'}
                                            <select class="stretched vtiger_field_name form-select" data-category="custom">
                                                {foreach key=OTHER_FIELD_NAME item=OTHER_FIELD_LABEL from=$VTIGER_OTHER_FIELDS}
                                                    <option value="{$OTHER_FIELD_NAME}" {if $VTIGER_FIELD_NAME eq $OTHER_FIELD_NAME}selected{/if}>{vtranslate($OTHER_FIELD_LABEL,$SOURCE_MODULE)}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gContact:website'}
                                            <select class="stretched vtiger_field_name form-select" data-category="url">
                                                {foreach key=URL_FIELD_NAME item=URL_FIELD_LABEL from=$VTIGER_URL_FIELDS}
                                                    <option value="{$URL_FIELD_NAME}" {if $VTIGER_FIELD_NAME eq $URL_FIELD_NAME}selected{/if}>{vtranslate($URL_FIELD_LABEL,$SOURCE_MODULE)}</option>
                                                {/foreach}
                                            </select>
                                        {/if}
                                    </td>
                                    <td>
                                        <div class="input-group">
                                        <input type="hidden" class="google_field_name" value="{$CUSTOM_FIELD_MAP['google_field_name']}" />
                                        {if $CUSTOM_FIELD_MAP['google_field_name'] eq 'gd:email'}
                                            {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['email']['types']}
                                            <select class="google-type col-sm-5 form-select" data-category="email">
                                                {foreach item=TYPE from=$GOOGLE_TYPES}
                                                    <option value="{$TYPE}" {if $CUSTOM_FIELD_MAP['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Email',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                                {/foreach}
                                            </select>&nbsp;&nbsp;
                                            <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $CUSTOM_FIELD_MAP['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                                   value="{if $CUSTOM_FIELD_MAP['google_field_type'] eq 'custom'}{$CUSTOM_FIELD_MAP['google_custom_label']}{/if}" data-rule-required="true"/>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gd:phoneNumber'}
                                            {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['phone']['types']}
                                            <select class="google-type form-select" data-category="phone">
                                                {foreach item=TYPE from=$GOOGLE_TYPES}
                                                    <option value="{$TYPE}" {if $CUSTOM_FIELD_MAP['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('Phone',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                                {/foreach}
                                            </select>&nbsp;&nbsp;
                                            <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $CUSTOM_FIELD_MAP['google_field_type'] neq 'custom'}hidden{else}visible{/if};"
                                                   value="{if $CUSTOM_FIELD_MAP['google_field_type'] eq 'custom'}{$CUSTOM_FIELD_MAP['google_custom_label']}{/if}" data-rule-required="true"/>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gContact:userDefinedField'}
                                            <input type="hidden" class="google-type" value="{$CUSTOM_FIELD_MAP['google_field_type']}">
                                            <input type="text" class="google-custom-label inputElement form-control" value="{$CUSTOM_FIELD_MAP['google_custom_label']}" data-rule-required="true"/>
                                        {elseif $CUSTOM_FIELD_MAP['google_field_name'] eq 'gContact:website'}
                                            {assign var=GOOGLE_TYPES value=$GOOGLE_FIELDS['url']['types']}
                                            <select class="google-type form-select" data-category="url">
                                                {foreach item=TYPE from=$GOOGLE_TYPES}
                                                    <option value="{$TYPE}" {if $CUSTOM_FIELD_MAP['google_field_type'] eq $TYPE}selected{/if}>{vtranslate('URL',$MODULENAME)} ({vtranslate($TYPE,$MODULENAME)})</option>
                                                {/foreach}
                                            </select>&nbsp;&nbsp;
                                            <input type="text" class="google-custom-label inputElement form-control" style="visibility:{if $CUSTOM_FIELD_MAP['google_field_type'] neq 'custom'}hidden{else}visible{/if}"
                                                   value="{if $CUSTOM_FIELD_MAP['google_field_type'] eq 'custom'}{$CUSTOM_FIELD_MAP['google_custom_label']}{/if}" data-rule-required="true"/>
                                        {/if}
                                        <a class="deleteCustomMapping btn btn-outline-secondary"><i title="Delete" class="text-secondary fa fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <br>
                    <br><br>
                </div>
                <div id="scroller_wrapper" class="bottom-fixed-scroll">
                    <div id="scroller" class="scroller-div"></div>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            {if $BUTTON_NAME neq null}
                {assign var=BUTTON_LABEL value=$BUTTON_NAME}
            {else}
                {assign var=BUTTON_LABEL value=vtranslate('LBL_SAVE', $MODULE)}
            {/if}
            <button id="save_syncsetting" class="btn btn-primary active me-2" name="saveButton">{vtranslate('LBL_SAVE', $MODULENAME)}</button>
            <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
	</div>
    </div>
</div>
{/strip}