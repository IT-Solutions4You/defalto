{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
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