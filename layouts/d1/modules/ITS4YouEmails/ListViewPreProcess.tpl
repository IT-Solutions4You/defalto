{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
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