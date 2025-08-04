{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
                        <div>
                            {foreach name=langvals item=langvalsdata key=modulename from=$LANGVALSARR}
                                <div class="row py-2">
                                    <div class="col-4 text-muted">{$langvalsdata.label}</div>
                                    <div class="col">
                                        <input type="hidden" name="LblVal{$langvalsdata.id}" value="yes"/>
                                        <input type="text" name="LblVal{$langvalsdata.id}Value" class="inputElement form-control" placeholder="{vtranslate('LBL_ENTER_CUSTOM_LABEL_VALUE', $MODULE)}" value="{$langvalsdata.value}"/>
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