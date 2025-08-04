{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $ATTACHMENTS}
    <div class="container-fluid">
        <div class="mt-3 bg-body rounded">
            <div class="p-3 border-bottom">
                <span class="fs-4 fw-bold text-truncate">{vtranslate('LBL_ATTACHMENTS',$QUALIFIED_MODULE)}</span>
            </div>
            <div class="p-3">
                <table class="table table-borderless">
                    <thead>
                    <tr>
                        <th class="w-50 text-secondary">{vtranslate('File Name', $QUALIFIED_MODULE)}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$ATTACHMENTS item=ATTACHMENT}
                        <tr class="border-top">
                            <td>
                                <a target="_blank" class="text-truncate" href="{$ATTACHMENT['filenamewithpath']}">
                                    {$ATTACHMENT['attachment']}
                                </a>
                            </td>
                            <td>
                                <a class="text-secondary" title="{vtranslate('Download', $QUALIFIED_MODULE)}" href="index.php?module=ITS4YouEmails&action=DownloadFile&attachment_id={$ATTACHMENT['fileid']}&name={$ATTACHMENT['attachment']}">
                                    <i class="fa fa-download"></i>
                                </a>
                                {if !empty($ATTACHMENT['docid'])}
                                    <a class="text-secondary ms-2" title="{vtranslate('Preview', $QUALIFIED_MODULE)}" href="javascript:void(0)" onclick="Vtiger_Header_Js.previewFile(event,{$ATTACHMENT['docid']})" data-filelocationtype="I" data-filename="{$ATTACHMENT['attachment']}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
{/if}