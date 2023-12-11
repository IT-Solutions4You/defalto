{*<!--
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
-->*}
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