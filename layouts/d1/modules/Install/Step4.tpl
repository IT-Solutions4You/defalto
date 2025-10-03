{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="main-container container px-4 py-3">
    <div class="inner-container">
        <form class="bg-body rounded" name="step4" method="post" action="index.php">
            <input type=hidden name="module" value="Install"/>
            <input type=hidden name="view" value="Index"/>
            <input type=hidden name="mode" value="Step5"/>
            {include file='StepHeader.tpl'|@vtemplate_path:'Install' TITLE='LBL_SYSTEM_CONFIGURATION'}
            <div class="container-fluid p-3">
                <div class="row hide" id="errorMessage"></div>
                <div class="row">
                    <div class="col-sm">
                        <input type='hidden' name='pwd_regex' value= {ZEND_json::encode($PWD_REGEX)}/>
                        <div class="container-fluid config-table">
                            <div class="fw-bold">
                            <div class="row py-2">
                                <div class="col">{vtranslate('LBL_DATABASE_INFORMATION', 'Install')}</div>
                            </div>
                            </div>
                            <div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_DATABASE_TYPE', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    {vtranslate('MySQL', 'Install')}
                                    {if function_exists('mysqli_connect')}
                                        <input type="hidden" value="mysqli" name="db_type">
                                    {else}
                                        <input type="hidden" value="mysql" name="db_type">
                                    {/if}
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_HOST_NAME', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$DB_HOSTNAME}" name="db_hostname"></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_USERNAME', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$DB_USERNAME}" name="db_username"></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_PASSWORD','Install')}</div>
                                <div class="col-lg-6"><input type="password" class="form-control" value="{$DB_PASSWORD}" name="db_password"></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_DB_NAME', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$DB_NAME}" name="db_name"></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">
                                    <label>
                                        <input type="checkbox" name="create_db" class="form-check-input me-2"/>
                                        <span class="fw-bold">{vtranslate('LBL_CREATE_NEW_DB','Install')}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="row py-2 hide" id="root_user">
                                <div class="col-lg-6">{vtranslate('LBL_ROOT_USERNAME', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="" name="db_root_username"></div>
                            </div>
                            <div class="row py-2 hide" id="root_password">
                                <div class="col-lg-6">{vtranslate('LBL_ROOT_PASSWORD', 'Install')}</div>
                                <div class="col-lg-6"><input type="password" class="form-control" value="" name="db_root_password"></div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="container-fluid config-table">
                            <div class="fw-bold">
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</div>
                            </div>
                            </div>
                            <div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_CURRENCIES','Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    <select name="currency_name" class="select2" >
                                        {foreach key=CURRENCY_NAME item=CURRENCY_INFO from=$CURRENCIES}
                                            <option value="{$CURRENCY_NAME}" {if $CURRENCY_NAME eq $DEFAULT_PARAMETERS['currency_name']} selected {/if}>{$CURRENCY_NAME} ({$CURRENCY_INFO.1})</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('Decimal Separator','Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    <select name="currency_decimal_separator" class="select2" >
                                        {html_options options=$DECIMAL_SEPARATORS selected=$DECIMAL_SEPARATOR}
                                    </select>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('Digit Grouping Separator','Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    <select name="currency_grouping_separator" class="select2" >
                                        {html_options options=$GROUPING_SEPARATORS selected=$GROUPING_SEPARATOR}
                                    </select>
                                </div>
                            </div>
                            </div>
                            <div class="fw-bold">
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_ADMIN_INFORMATION', 'Install')}</div>
                            </div>
                            </div>
                            <div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_USERNAME', 'Install')}</div>
                                <div class="col-lg-6">admin<input type="hidden" name="{$ADMIN_NAME}" value="admin"/></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_PASSWORD', 'Install')}<span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="password"/></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_RETYPE_PASSWORD', 'Install')} <span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    <input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="retype_password"/>
                                    <div id="passwordError" class="no text-danger"></div>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('First Name', 'Install')}</div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$ADMIN_FIRSTNAME}" name="firstname"/></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('Last Name', 'Install')} <span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$ADMIN_LASTNAME}" name="lastname"/></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_EMAIL','Install')} <span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6"><input type="text" class="form-control" value="{$ADMIN_EMAIL}" name="admin_email"></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">{vtranslate('LBL_DATE_FORMAT','Install')} <span class="no text-danger ms-2">*</span></div>
                                <div class="col-lg-6">
                                    <select class="select2"  name="dateformat">
                                        {html_options options=$DATE_FORMATS selected=$DATE_FORMAT}
                                    </select>
                                </div>
                            </div>
                            <div class="row py-2">
                                <div class="col-lg-6">
                                    {vtranslate('LBL_TIME_ZONE','Install')} <span class="no text-danger ms-2">*</span>
                                </div>
                                <div class="col-lg-6">
                                    <div>
                                        <select class="select2" name="timezone">
                                            {html_options options=$TIMEZONES selected=$TIMEZONE}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="button-container text-end p-3">
                <input type="button" class="btn btn-outline-primary me-2" value="{vtranslate('LBL_BACK','Install')}" name="back"/>
                <input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step5"/>
            </div>
        </form>
    </div>
</div>