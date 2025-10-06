{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div id="formContainer">
    <div class="main-container container px-4 py-3">
        <div class="inner-container">
            <form class="rounded bg-body" name="step6" method="post" action="index.php">
                <input type=hidden name="module" value="Install"/>
                <input type=hidden name="view" value="Index"/>
                <input type=hidden name="mode" value="Step7"/>
                <input type=hidden name="auth_key" value="{$AUTH_KEY}"/>
                {include file='StepHeader.tpl'|@vtemplate_path:'Install' TITLE='LBL_SMTP_SERVER_CONFIG'}
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="container-fluid">
                                <div>
                                    <label class="row py-2">
                                        <span class="col-sm">
                                            <strong>{vtranslate('Server')}</strong>
                                        </span>
                                        <span class="col-sm">
                                            <input name="smtp_server" placeholder="ssl://smtp.example.com:465" class="form-control" value="{$DEFAULT_PARAMETERS['smtp_server']}">
                                        </span>
                                    </label>
                                    <label class="row py-2">
                                        <span class="col-sm">
                                            <strong>{vtranslate('Username')}</strong>
                                        </span>
                                        <span class="col-sm">
                                            <input name="smtp_username" placeholder="email.address@example.com" class="form-control" value="{$DEFAULT_PARAMETERS['smtp_username']}">
                                        </span>
                                    </label>
                                    <label class="row py-2">
                                        <span class="col-sm">
                                            <strong>{vtranslate('Password')}</strong>
                                        </span>
                                        <span class="col-sm">
                                            <input name="smtp_password" placeholder="********" type="password" class="form-control" value="{$DEFAULT_PARAMETERS['smtp_password']}">
                                        </span>
                                    </label>
                                    <label class="row py-2">
                                        <span class="col-sm">
                                            <strong>{vtranslate('From Email')}</strong>
                                        </span>
                                        <span class="col-sm">
                                            <input name="smtp_from_email" placeholder="email.address@example.com" type="email" class="form-control" value="{$DEFAULT_PARAMETERS['smtp_from_email']}">
                                        </span>
                                    </label>
                                    <label class="row py-2">
                                        <span class="col-sm">
                                            <strong>{vtranslate('Requires Authentication')}</strong>
                                        </span>
                                        <span class="col-sm">
                                            <input name="smtp_authentication" type="checkbox" checked="checked" value="on" class="form-check-input" {if 'on' eq $DEFAULT_PARAMETERS['smtp_authentication']}checked="checked"{/if}>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="button-container text-end p-3">
                    <input type="button" class="btn btn-primary me-2" value="{vtranslate('LBL_SKIP','Install')}" name="step7"/>
                    <input type="button" class="btn btn-primary active" value="{vtranslate('LBL_NEXT','Install')}" name="step7"/>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="progressIndicator" class="hide">
    <div class="main-container container px-4 py-3">
        <div class="inner-container">
            <div class="rounded bg-body">
                <div class="welcome-div text-center">
                    <div class="p-3">
                        <h3>{vtranslate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3>
                    </div>
                    <div class="p-3 row align-items-center justify-content-center">
                        <div class="col-auto">
                            <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status"></div>
                        </div>
                        <div class="col-auto fs-4">
                            {vtranslate('LBL_PLEASE_WAIT','Install')}...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>