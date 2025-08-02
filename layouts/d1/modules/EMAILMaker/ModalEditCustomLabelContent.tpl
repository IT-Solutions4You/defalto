{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
                    <div class="massEditTable">
                        <div class="row py-2">
                            <div class="fieldLabel col-lg-4 text-end text-muted">
                                <span>{vtranslate('LBL_KEY', $MODULE)}</span>
                                <span class="text-danger ms-2">*</span>
                            </div>
                            <div class="fieldValue col-lg">
                                {if $LABELID eq ""}
                                    <div class="input-group">
                                        <span class="input-group-text">C_</span>
                                        <input type="text" name="LblKey" class="inputElement form-control" placeholder="{vtranslate('LBL_ENTER_KEY', $MODULE)}" value="" data-rule-required="true"/>
                                    </div>
                                {else}
                                    C_{$CUSTOM_LABEL_KEY}
                                {/if}
                            </div>
                        </div>
                        <div class="row py-2">
                            <div class="fieldLabel col-lg-4 text-end text-muted">
                                <span>{vtranslate('LBL_VALUE', $MODULE)}</span>
                            </div>
                            <div class="fieldValue col-lg">
                                <input type="text" name="LblVal" class="inputElement form-control" placeholder="{vtranslate('LBL_ENTER_CUSTOM_LABEL_VALUE', $MODULE)}" value="{$CUSTOM_LABEL_VALUE}"/>
                            </div>
                        </div>
                    </div>
                </div>
                {if $LABELID eq ""}<input type="hidden" class="addCustomLabelView" value="true"/>{/if}
                {assign var=BUTTON_ID value="js-save-cl"}
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}