{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Vtiger/views/OutgoingServerDetail.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="settingsPageDiv">
        <div class="detailViewContainer px-4 pb-4" id="OutgoingServerDetails">
            <input type="hidden" name="serverType" value="{$MODEL->get('server')}">
            <div class="">
                <div>
                    <div class="block bg-body rounded">
                        <div class="container-fluid pt-3 px-3 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-lg pb-3">
                                    <h4 class="m-0">{vtranslate('LBL_OUTGOING_SERVER', $QUALIFIED_MODULE)}</h4>
                                </div>
                                <div class="col-lg-auto pb-3">
                                    <button class="btn btn-outline-secondary editButton" data-url="{$MODEL->getEditViewUrl()}" type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-bottom">
                            <span class="fs-4 fw-bold">{vtranslate('LBL_MAIL_SERVER_SMTP', $QUALIFIED_MODULE)}</span>
                        </div>
                        <div class="p-3">
                            <div class="container-fluid">
                                <div class="row py-3 border-bottom">
                                    <div class="col-lg-3 fieldLabel">
                                        <label>{vtranslate('LBL_SERVER_NAME', $QUALIFIED_MODULE)}</label>
                                    </div>
                                    <div class="col-lg-4 fieldValue">
                                        <span>{$MODEL->get('server')}</span>
                                    </div>
                                </div>
                                <div class="row py-3 border-bottom">
                                    <div class="col-lg-3 fieldLabel">
                                        <label>{vtranslate('LBL_USER_NAME', $QUALIFIED_MODULE)}</label>
                                    </div>
                                    <div class="col-lg-4 fieldValue">
                                        <span>{$MODEL->get('server_username')}</span>
                                    </div>
                                </div>
                                <div class="serverLogin hide">
                                    <div class="row py-3 border-bottom">
                                        <div class="col-lg-3 fieldLabel">
                                            <label>{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label>
                                        </div>
                                        <div class="col-lg-4 fieldValue">
                                            <span class="password">{if $MODEL->get('server_password') neq ''}******{/if}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="oauthLogin hide">
                                    <div class="row py-3 border-bottom">
                                        <div class="col-lg-3 fieldLabel">
                                            <label>{vtranslate('LBL_CLIENT_ID', $QUALIFIED_MODULE)}</label>
                                        </div>
                                        <div class="col-lg-4 fieldValue">
                                            <span class="password">{$MODEL->get('client_id')}</span>
                                        </div>
                                    </div>
                                    <div class="row py-3 border-bottom">
                                        <div class="col-lg-3 fieldLabel">
                                            <label>{vtranslate('LBL_CLIENT_SECRET', $QUALIFIED_MODULE)}</label>
                                        </div>
                                        <div class="col-lg-4 fieldValue">
                                            <span class="password">{if $MODEL->get('client_secret') neq ''}******{/if}</span>
                                        </div>
                                    </div>
                                    <div class="row py-3 border-bottom">
                                        <div class="col-lg-3 fieldLabel">
                                            <label>{vtranslate('LBL_CLIENT_TOKEN', $QUALIFIED_MODULE)}</label>
                                        </div>
                                        <div class="col-lg-4 fieldValue">
                                            <span class="password">{if $MODEL->get('client_token') neq ''}******{/if}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row py-3 border-bottom">
                                    <div class="col-lg-3 fieldLabel">
                                        <label>{vtranslate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}</label>
                                    </div>
                                    <div class="col-lg-4 fieldValue">
                                        <span>{$MODEL->get('from_email_field')}</span>
                                    </div>
                                </div>
                                <div class="row py-3 border-bottom">
                                    <div class="col-lg-3 fieldLabel">
                                        <label>{vtranslate('LBL_REQUIRES_AUTHENTICATION', $QUALIFIED_MODULE)}</label>
                                    </div>
                                    <div class="col-lg-4 fieldValue">
                                        <span>{if $MODEL->isSmtpAuthEnabled()}{vtranslate('LBL_YES', $QUALIFIED_MODULE)} {else}{vtranslate('LBL_NO', $QUALIFIED_MODULE)}{/if}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}