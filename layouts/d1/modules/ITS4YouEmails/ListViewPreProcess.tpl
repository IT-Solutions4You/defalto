{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{include file='ListViewPreProcess.tpl'|vtemplate_path:'Vtiger'}
{if !ITS4YouEmails_Module_Model::isSendMailConfigured()}
    <div class="padding15px">
        <div class="displayInlineBlock alert alert-danger">
            {vtranslate('LBL_REQUIRED_SENDMAIL_FIX', $MODULE)}
            <br>
            <br>
            <pre>$ITS4YouEmails_Mailer = '{vglobal('Emails_Mailer')}';</pre>
        </div>
    </div>
{/if}