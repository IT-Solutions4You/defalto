{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
    <div class="container-fluid d-flex flex-column h-100">
        <input type="hidden" id="recordId" value="0">
        <input type="hidden" id="mmFrom" value='{implode(',', $MAIL->getFrom())}'>
        <input type="hidden" id="mmSubject" value='{Vtiger_Functions::jsonEncode($MAIL->getSubject())}'>
        <input type="hidden" id="mmMsgNo" value="{$MAIL->getMsgNo()}">
        <input type="hidden" id="mmMsgUid" value="{$MAIL->getUid()}">
        <input type="hidden" id="mmFolder" value="{$FOLDER->getName()}">
        <input type="hidden" id="mmTo" value='{implode(',', $MAIL->getTo())}'>
        <input type="hidden" id="mmCc" value='{implode(',', $MAIL->getCC())}'>
        <input type="hidden" id="mmDate" value="{$MAIL->getDate()}">
        <input type="hidden" id="mmUserName" value="{$USERNAME}">
        <input type="hidden" id="mmAttchmentCount" value="{$ATTACHMENTS_COUNT}">
        <div class="row" id="mailManagerActions">
            <div class="col mb-2" id="relationBlock">
                <div class="spinner-border text-secondary" role="status">
                    <span class="sr-only">{vtranslate('Loading relations', $QUALIFIED_MODULE)}...</span>
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-secondary mailPagination me-2" data-folder='{$FOLDER->getName()}' data-muid='{$MAIL->getUid()}' data-type="prev" disabled="disabled">
                    <i class="fa fa-caret-left"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary mailPagination" data-folder='{$FOLDER->getName()}' data-muid='{$MAIL->getUid()}' data-type="next" disabled="disabled">
                    <i class="fa fa-caret-right"></i>
                </button>
            </div>
        </div>
        <div class="row border-bottom py-3">
            <div class="col-lg-12">
                <h5 class="mmMailSubject fw-bold m-0">{$MAIL->getSubject()}</h5>
            </div>
        </div>
        <div class="row py-3">
            <div class="col-lg-1">
                <div class="mmFirstNameChar bg-primary text-white p-3 rounded text-center">
                    {assign var=NAME value=$MAIL->getFrom()}
                    {assign var=FIRST_CHAR value=strtoupper(substr($NAME[0], 0, 1))}
                    {if $FOLDER->isSentFolder()}
                        {assign var=NAME value=$MAIL->getTo()}
                        {assign var=FIRST_CHAR value=strtoupper(substr($NAME[0], 0, 1))}
                    {/if}
                    <strong>{$FIRST_CHAR}</strong>
                </div>
            </div>
            <div class="col-lg-6">
                <div>
                    <span class="mmDisplayName me-2">
                        {if $FOLDER->isSentFolder()}
                            {implode(', ', $MAIL->getTo())}
                        {else}
                            {$NAME[0]}
                        {/if}
                    </span>
                    {if $ATTACHMENTS_COUNT}
                        <i class="fa fa-paperclip fontSize20px"></i>
                    {/if}
                </div>
                <div>
                    {assign var=FROM value=$MAIL->getFrom()}
                    <table>
                        <tr>
                            <td class="muted input-info-addon pe-2">{vtranslate('LBL_FROM', $MODULE)}</td>
                            <td class="displayEmailValues">{$FROM[0]}</td>
                        </tr>
                        <tr>
                            <td class="muted input-info-addon pe-2">{vtranslate('LBL_TO', $MODULE)}</td>
                            <td class="displayEmailValues">{foreach from=$MAIL->getTo() item=TO_VAL}{$TO_VAL}<br>{/foreach}</td>
                        </tr>
                        {if $MAIL->getCC()}
                            <tr>
                                <td class="muted input-info-addon pe-2">{vtranslate('LBL_CC_SMALL', $MODULE)}</td>
                                <td class="displayEmailValues">{foreach from=$MAIL->getCC() item=CC_VAL}{$CC_VAL}<br>{/foreach}</td>
                            </tr>
                        {/if}
                        {if $MAIL->getBCC()}
                            <tr>
                                <td class="muted input-info-addon pe-2">{vtranslate('LBL_BCC_SMALL', $MODULE)}</td>
                                <td class="displayEmailValues">{foreach from=$MAIL->getBCC() item=BCC_VAL}{$BCC_VAL}<br>{/foreach}</td>
                            </tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div class="col-lg-5 text-end">
                <span class="mmDetailDate">
                    {Vtiger_Util_Helper::formatDateTimeIntoDayString($MAIL->getDate(), true)}
                </span>
            </div>
        </div>
        <div class="text-end border-bottom pb-3">
            <span class="btn btn-outline-secondary mmDetailAction me-2" onclick='javascript:ITS4YouEmails_MassEdit_Js.replyEmail({$MAIL->getUid()},"MailManager");' title="{vtranslate('Reply', $MODULE)}">
                <i class="fa-solid fa-reply"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" onclick='javascript:ITS4YouEmails_MassEdit_Js.replyAllEmail({$MAIL->getUid()},"MailManager");' title="{vtranslate('Reply all', $MODULE)}">
                <i class="fa-solid fa-reply-all"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" onclick='javascript:ITS4YouEmails_MassEdit_Js.forwardEmail({$MAIL->getUid()},"MailManager");' title="{vtranslate('Forward', $MODULE)}">
                <i class="fa-solid fa-reply fa-flip-horizontal"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmPrint" title="{vtranslate('LBL_Print', $MODULE)}">
                <i class="fa fa-print"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmDelete" title="{vtranslate('LBL_Delete', $MODULE)}">
                <i class="fa fa-trash-o"></i>
            </span>
        </div>
        <div class="row pt-3 h-75 overflow-hidden">
            <div class="col-lg-12 mmEmailContainerDiv">
                <div id="mmBody" class="h-100">{$MAIL->getBody(false)}</div>
            </div>
        </div>
        {if $ATTACHMENTS_COUNT}
            <div class="row border-top">
                <div class="col-lg-12">
                    <div class="py-3">
                        <strong class="me-2">{vtranslate('LBL_Attachments',$MODULE)}</strong>
                        <span>({$ATTACHMENTS_COUNT}&nbsp;{vtranslate('LBL_FILES', $MODULE)})</span>
                    </div>
                    {foreach item=ATTACHMENT from=$ATTACHMENTS}
                        {assign var=ATTACHMENT_NAME value=$ATTACHMENT['filename']}
                        {assign var=ATTACHMENT_ID value=$ATTACHMENT['attachment_id']}
                        <div class="mb-2">
                            <i class="fa {$MAIL->getAttachmentIcon($ATTACHMENT_NAME)}"></i>
                            <span class="ms-2">{$ATTACHMENT_NAME}</span>
                            <span class="ms-2">({Vtiger_Functions::formatBytes($ATTACHMENT['size'])})</span>
                            {if !empty($ATTACHMENT['attachment_url'])}
                                <a href="{$ATTACHMENT['attachment_url']}" class="px-2">
                                    <i class="fa fa-download"></i>
                                </a>
                            {/if}
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}
    </div>
{/strip}
