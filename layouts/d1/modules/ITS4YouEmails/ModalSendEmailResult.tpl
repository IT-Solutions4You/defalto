{*<!--
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<div class="modal-dialog modelContainer mailSentSuccessfully">
    <div class="modal-content" style="width:800px;">
        {assign var=HEADER_TITLE value=$TITLE}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="padding15px">
            <div class="widgetContainer_1">
                <div class="widget_contents" id="popup_notifi_content">
                    {vtranslate('LBL_EMAILS_SENT_RESULT', $MODULE)}
                    <hr>
                    {vtranslate('LBL_TOTAL_EMAILS', $MODULE)}: {$RESULT['total']}
                    <br>
                    {if $RESULT['sent']}
                        {vtranslate('LBL_SENT_EMAILS', $MODULE)}: {$RESULT['sent']}
                        <br>
                    {/if}
                    {if $RESULT['error']}
                        {vtranslate('LBL_ERROR_EMAILS', $MODULE)}: {$RESULT['error']}
                        <br>
                        <span style="color: red; white-space: pre-line;">{$RESULT['error_message']}</span>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>