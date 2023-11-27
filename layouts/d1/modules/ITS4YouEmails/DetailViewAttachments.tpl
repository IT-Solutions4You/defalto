{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
{if $ATTACHMENTS}
    <div class="block">
        <div>
            <h4 class="textOverflowEllipsis maxWidth50">{vtranslate('LBL_ATTACHMENTS',$QUALIFIED_MODULE)}</h4>
        </div>
        <hr>
        <div class="padding20px">
            <table class="table no-border">
                <thead>
                <tr>
                    <th>{vtranslate('File Name', $QUALIFIED_MODULE)}</th>
                    <th>{vtranslate('Actions', $QUALIFIED_MODULE)}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$ATTACHMENTS item=ATTACHMENT}
                    <tr>
                        <td>
                            <a target="_blank" href="{$ATTACHMENT['filenamewithpath']}">
                                {$ATTACHMENT['attachment']}
                            </a>
                        </td>
                        <td>
                            <a title="{vtranslate('Download', $QUALIFIED_MODULE)}" href="index.php?module=Emails&action=DownloadFile&attachment_id={$ATTACHMENT['fileid']}&name={$ATTACHMENT['attachment']}"><i class="fa fa-download"></i></a>
                            {if !empty($ATTACHMENT['docid'])}
                                &nbsp;&nbsp;
                                <a title="{vtranslate('Preview', $QUALIFIED_MODULE)}" href="javascript:void(0)" onclick="Vtiger_Header_Js.previewFile(event,{$ATTACHMENT['docid']})" data-filelocationtype="I" data-filename="{$ATTACHMENT['attachment']}"><i class="fa fa-eye"></i></a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    <br>
{/if}