{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="modal-dialog modelContainer mailSentSuccessfully">
    <div class="modal-content" style="width:800px;">
        {assign var=HEADER_TITLE value=$TITLE}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="padding15px">
            <div class="widgetContainer_1">
                <div class="widget_contents p-3" id="popup_notifi_content">
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