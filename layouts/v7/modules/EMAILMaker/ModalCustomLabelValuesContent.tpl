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
    <div class="modal-dialog modelContainer">
        <div class="modal-content ">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$LBLKEY}
            <div class="modal-body">
                <div class="container-fluid CustomLabelModalContainer">
                    <form id="showCustomLabelValues" action="index.php" method="post" class="form-horizontal contentsBackground">
                        <input type="hidden" name="module" value="{$MODULE}"/>
                        <input type="hidden" name="action" value="SaveCustomLabels"/>
                        <input type="hidden" name="lblkey" value="{$LBLKEY}"/>
                        <div class="row-fluid">
                            {foreach name=langvals item=langvalsdata key=modulename from=$LANGVALSARR}
                                <div class="control-group">
                                    <label class="muted control-label">{$langvalsdata.label}</label>
                                    <div class="controls input-append">
                                        <input type="hidden" name="LblVal{$langvalsdata.id}" value="yes"/>
                                        <input type="text" name="LblVal{$langvalsdata.id}Value" class="inputElement" placeholder="{vtranslate('LBL_ENTER_CUSTOM_LABEL_VALUE', $MODULE)}" value="{$langvalsdata.value}"/>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </form>
                </div>
            </div>
            {if $LABELID eq ""}<input type="hidden" class="addCustomLabelView" value="true"/>{/if}
            {assign var=BUTTON_ID value="js-save-cl"}
            {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
        </div>
    </div>
{/strip}