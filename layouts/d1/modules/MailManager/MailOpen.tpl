{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="container-fluid">
        <input type="hidden" id="mmFrom" value='{implode(',', $MAIL->from())}'>
        <input type="hidden" id="mmSubject" value='{Vtiger_Functions::jsonEncode($MAIL->subject())}'>
        <input type="hidden" id="mmMsgNo" value="{$MAIL->msgNo()}">
        <input type="hidden" id="mmMsgUid" value="{$MAIL->uniqueid()}">
        <input type="hidden" id="mmFolder" value="{$FOLDER->name()}">
        <input type="hidden" id="mmTo" value='{implode(',', $MAIL->to())}'>
        <input type="hidden" id="mmCc" value='{implode(',', $MAIL->cc())}'>
        <input type="hidden" id="mmDate" value="{$MAIL->date()}">
        <input type="hidden" id="mmUserName" value="{$USERNAME}">
        {assign var=ATTACHMENT_COUNT value=(php7_count($ATTACHMENTS) - php7_count($INLINE_ATT))}
        <input type="hidden" id="mmAttchmentCount" value="{$ATTACHMENT_COUNT}">
        <div id="mailManagerActions">
            <div class="row">
                <div class="col-lg" id="relationBlock"></div>
                <div class="col-lg-auto">
                    <span class="pull-right">
                        <button type="button" class="btn btn-outline-secondary mailPagination me-2" {if $MAIL->msgno() < $FOLDER->count()}data-folder='{$FOLDER->name()}' data-msgno='{$MAIL->msgno(1)}'{else}disabled="disabled"{/if}>
                            <i class="fa fa-caret-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary mailPagination" {if $MAIL->msgno() > 1}data-folder='{$FOLDER->name()}' data-msgno='{$MAIL->msgno(-1)}'{else}disabled="disabled"{/if}>
                            <i class="fa fa-caret-right"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h5 class="mmMailSubject fw-bold my-3">{$MAIL->subject()}</h5>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-1">
                <div class="mmFirstNameChar bg-primary text-white p-3 rounded text-center">
                    {assign var=NAME value=$MAIL->from()}
                    {assign var=FIRST_CHAR value=strtoupper(substr($NAME[0], 0, 1))}
                    {if $FOLDER->isSentFolder()}
                        {assign var=NAME value=$MAIL->to()}
                        {assign var=FIRST_CHAR value=strtoupper(substr($NAME[0], 0, 1))}
                    {/if}
                    <strong>{$FIRST_CHAR}</strong>
                </div>
            </div>
            <div class="col-lg-6">
                <div>
                    <span class="mmDisplayName me-2">
                        {if $FOLDER->isSentFolder()}
                            {implode(', ', $MAIL->to())}
                        {else}
                            {$NAME[0]}
                        {/if}
                    </span>
                    {if $ATTACHMENT_COUNT}
                        <i class="fa fa-paperclip fontSize20px"></i>
                    {/if}
                </div>
                <div>
                    {assign var=FROM value=$MAIL->from()} 
                    <table>
                        <tr>
                            <td class="muted input-info-addon pe-2">{vtranslate('LBL_FROM', $MODULE)}</td>
                            <td class="displayEmailValues">{$FROM[0]}</td>
                        </tr>
                        <tr>
                            <td class="muted input-info-addon pe-2">{vtranslate('LBL_TO', $MODULE)}</td>
                            <td class="displayEmailValues">{foreach from=$MAIL->to() item=TO_VAL}{$TO_VAL}<br>{/foreach}</td>
                        </tr>
                        {if $MAIL->cc()}
                            <tr>
                                <td class="muted input-info-addon pe-2">{vtranslate('LBL_CC_SMALL', $MODULE)}</td>
                                <td class="displayEmailValues">{foreach from=$MAIL->cc() item=CC_VAL}{$CC_VAL}<br>{/foreach}</td>
                            </tr>
                        {/if}
                        {if $MAIL->bcc()}
                            <tr>
                                <td class="muted input-info-addon pe-2">{vtranslate('LBL_BCC_SMALL', $MODULE)}</td>
                                <td class="displayEmailValues">{foreach from=$MAIL->bcc() item=BCC_VAL}{$BCC_VAL}<br>{/foreach}</td>
                            </tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div class="col-lg-5 text-end">
                <span class="mmDetailDate">
                    {Vtiger_Util_Helper::formatDateTimeIntoDayString($MAIL->date(), true)}
                </span>
            </div>
        </div>
        <div class="text-end">
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmPrint" title="{vtranslate('LBL_Print', $MODULE)}">
                <i class="fa fa-print"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmReply" title="{vtranslate('LBL_Reply', $MODULE)}">
                <i class="fa fa-reply"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmReplyAll" title="{vtranslate('LBL_Reply_All', $MODULE)}">
                <i class="fa fa-reply-all"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmForward" title="{vtranslate('LBL_Forward', $MODULE)}">
                <i class="fa fa-share"></i>
            </span>
            <span class="btn btn-outline-secondary mmDetailAction me-2" id="mmDelete" title="{vtranslate('LBL_Delete', $MODULE)}">
                <i class="fa fa-trash-o"></i>
            </span>
        </div>
        <hr>
        <div class="row mt-3">
            <div class="col-lg-12 mmEmailContainerDiv">
                <div id="mmBody">{$BODY}</div>
            </div>
        </div>
        {if $ATTACHMENT_COUNT}
            <hr class="mmDetailHr">
            <div class='col-lg-12 padding0px'>
                <span><strong>{vtranslate('LBL_Attachments',$MODULE)}</strong></span>
                <span>&nbsp;&nbsp;({php7_count($ATTACHMENTS) - php7_count($INLINE_ATT)}&nbsp;{vtranslate('LBL_FILES', $MODULE)})</span>
                <br><br>
                {foreach item=ATTACHVALUE from=$ATTACHMENTS name="attach"}
                    {assign var=ATTACHNAME value=$ATTACHVALUE['filename']}
                    {if $INLINE_ATT[$ATTACHNAME] eq null}
                        {assign var=DOWNLOAD_LINK value=$ATTACHNAME|@escape:'url'}
						{assign var=ATTACHID value=$ATTACHVALUE['attachid']}
                        <span>
                            <i class="fa {$MAIL->getAttachmentIcon($ATTACHVALUE['path'])}"></i>&nbsp;&nbsp;
                            <a href="index.php?module={$MODULE}&view=Index&_operation=mail&_operationarg=attachment_dld&_muid={$MAIL->muid()}&_atid={$ATTACHID}&_atname={$DOWNLOAD_LINK|@escape:'htmlall':'UTF-8'}">
                                {$ATTACHNAME}
                            </a>
                            <span>&nbsp;&nbsp;({$ATTACHVALUE['size']})</span>
                            <a href="index.php?module={$MODULE}&view=Index&_operation=mail&_operationarg=attachment_dld&_muid={$MAIL->muid()}&_atid={$ATTACHID}&_atname={$DOWNLOAD_LINK|@escape:'htmlall':'UTF-8'}">
                                &nbsp;&nbsp;<i class="fa fa-download"></i>
                            </a>
                        </span>
                        <br>
                    {/if}
                {/foreach}
            </div>
        {/if}
    </div>
{/strip}
