{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="main-container container px-4 py-3">
    <div class="inner-container">
        <form class="rounded bg-body" name="step5" method="post" action="index.php">
            <input type=hidden name="module" value="Install"/>
            <input type=hidden name="view" value="Index"/>
            <input type=hidden name="mode" value="Step6"/>
            <input type=hidden name="auth_key" value="{$AUTH_KEY}"/>
            {include file='StepHeader.tpl'|vtemplate_path:'Install' TITLE='LBL_CONFIRM_CONFIGURATION_SETTINGS'}
            <div class="container p-3">
                {if $DB_CONNECTION_INFO['flag'] neq true}
                    <div class="row" id="errorMessage">
                        <div class="col-sm-8">
                            <div class="alert alert-error">
                                {$DB_CONNECTION_INFO['error_msg']}
                                {$DB_CONNECTION_INFO['error_msg_info']}
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="row">
                    <div class="col-sm-12">
                        <table class="table table-borderless config-table input-table">
                            <thead>
                            <tr>
                                <th class="w-25">{vtranslate('LBL_DATABASE_INFORMATION','Install')}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_DATABASE_TYPE','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{vtranslate('MySQL','Install')}</td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_DB_NAME','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{$INFORMATION['db_name']}</td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th class="w-25">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_URL','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><a href="#">{$SITE_URL}</a></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_CURRENCY','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{$INFORMATION['currency_name']}</td>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <th colspan="2">{vtranslate('LBL_ADMIN_USER_INFORMATION','Install')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_USERNAME','Install')}</td>
                                <td>{$INFORMATION['admin']}</td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_EMAIL','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{$INFORMATION['admin_email']}</td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_TIME_ZONE','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{$INFORMATION['timezone']}</td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_DATE_FORMAT','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>{$INFORMATION['dateformat']}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="button-container text-end p-3">
                <input type="button" class="btn btn-outline-secondary" value="{vtranslate('LBL_BACK','Install')}" {if $DB_CONNECTION_INFO['flag'] eq true} disabled="disabled" {/if} name="back"/>
                {if $DB_CONNECTION_INFO['flag'] eq true}
                    <input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step6"/>
                {/if}
            </div>
        </form>
    </div>
</div>