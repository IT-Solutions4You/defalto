{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{strip}
    <div class="modal-dialog">
        <div class="modal-content CustomLabelModalContainer">
            {if $LABELID neq ""}
                {assign var=HEADER_TITLE value=vtranslate('LBL_EDIT_CUSTOM_LABEL', $MODULE)}
            {else}
                {assign var=HEADER_TITLE value=vtranslate('LBL_ADD_NEW_CUSTOM_LABEL', $MODULE)}
            {/if}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="$HEADER_TITLE ({$CURR_LANG.label})"}
            <form id="editCustomLabel" class="form-horizontal contentsBackground">
                <input type="hidden" name="labelid" value="{$LABELID}"/>
                <input type="hidden" name="langid" value="{$LANGID}"/>
                <div class="modal-body">
                    <table class="massEditTable table no-border">
                        <tr>
                            <td class="fieldLabel col-lg-2">
                                <label class="muted pull-right">
                                    {vtranslate('LBL_KEY', $MODULE)}<span class="redColor">*</span>
                                </label>
                            </td>
                            <td class="fieldValue col-lg-4" colspan="3">
                                {if $LABELID eq ""}
                                    <div class="input-group"><span class="input-group-addon">C_</span>
                                    <input type="text" name="LblKey" class="inputElement" placeholder="{vtranslate('LBL_ENTER_KEY', $MODULE)}" value="" data-rule-required="true"/></div>{else}C_{$CUSTOM_LABEL_KEY}{/if}
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldLabel col-lg-2">
                                <label class="muted pull-right">
                                    {vtranslate('LBL_VALUE', $MODULE)}
                                </label>
                            </td>
                            <td class="fieldValue col-lg-4" colspan="3">
                                <input type="text" name="LblVal" class="inputElement" placeholder="{vtranslate('LBL_ENTER_CUSTOM_LABEL_VALUE', $MODULE)}" value="{$CUSTOM_LABEL_VALUE}"/></td>
                        </tr>
                    </table>
                </div>
                {if $LABELID eq ""}<input type="hidden" class="addCustomLabelView" value="true"/>{/if}
                {assign var=BUTTON_ID value="js-save-cl"}
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}