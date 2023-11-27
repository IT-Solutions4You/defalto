{include file='ListViewPreProcess.tpl'|vtemplate_path:'Vtiger'}
{if !ITS4YouEmails_Module_Model::isPHPMailerInstalled()}
    <div class="padding15px">
        <div class="displayInlineBlock alert alert-danger">{vtranslate('LBL_INSTALL_PHPMAILER', $MODULE)}</div>
    </div>
{/if}
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