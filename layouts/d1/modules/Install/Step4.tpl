{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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
                        <table class="table table-borderless config-table input-table">
                            <thead>
                            <tr>
                                <th class="w-50">{vtranslate('LBL_DATABASE_INFORMATION', 'Install')}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_DATABASE_TYPE', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>
                                    {vtranslate('MySQL', 'Install')}
                                    {if function_exists('mysqli_connect')}
                                        <input type="hidden" value="mysqli" name="db_type">
                                    {else}
                                        <input type="hidden" value="mysql" name="db_type">
                                    {/if}
                                </td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_HOST_NAME', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="{$DB_HOSTNAME}" name="db_hostname"></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_USERNAME', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="{$DB_USERNAME}" name="db_username"></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_PASSWORD','Install')}</td>
                                <td><input type="password" class="form-control" value="{$DB_PASSWORD}" name="db_password"></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_DB_NAME', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="{$DB_NAME}" name="db_name"></td>
                            </tr>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="create_db"/>
                                        <span>{vtranslate('LBL_CREATE_NEW_DB','Install')}</span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="hide" id="root_user">
                                <td>{vtranslate('LBL_ROOT_USERNAME', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="" name="db_root_username"></td>
                            </tr>
                            <tr class="hide" id="root_password">
                                <td>{vtranslate('LBL_ROOT_PASSWORD', 'Install')}</td>
                                <td><input type="password" class="form-control" value="" name="db_root_password"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm">
                        <table class="table table-borderless config-table input-table">
                            <thead>
                            <tr>
                                <th class="w-50">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_CURRENCIES','Install')}<span class="no text-danger ms-2">*</span></td>
                                <td>
                                    <select name="currency_name" class="select2" >
                                        {foreach key=CURRENCY_NAME item=CURRENCY_INFO from=$CURRENCIES}
                                            <option value="{$CURRENCY_NAME}" {if $CURRENCY_NAME eq 'USA, Dollars'} selected {/if}>{$CURRENCY_NAME} ({$CURRENCY_INFO.1})</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table class="table table-borderless config-table input-table">
                            <thead>
                            <tr>
                                <th class="w-50">{vtranslate('LBL_ADMIN_INFORMATION', 'Install')}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{vtranslate('LBL_USERNAME', 'Install')}</td>
                                <td>admin<input type="hidden" name="{$ADMIN_NAME}" value="admin"/></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_PASSWORD', 'Install')}<span class="no text-danger ms-2">*</span></td>
                                <td><input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="password"/></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_RETYPE_PASSWORD', 'Install')} <span class="no text-danger ms-2">*</span></td>
                                <td>
                                    <input type="password" class="form-control" value="{$ADMIN_PASSWORD}" name="retype_password"/>
                                    <div id="passwordError" class="no text-danger"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>{vtranslate('First Name', 'Install')}</td>
                                <td><input type="text" class="form-control" value="" name="firstname"/></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('Last Name', 'Install')} <span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="{$ADMIN_LASTNAME}" name="lastname"/></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_EMAIL','Install')} <span class="no text-danger ms-2">*</span></td>
                                <td><input type="text" class="form-control" value="{$ADMIN_EMAIL}" name="admin_email"></td>
                            </tr>
                            <tr>
                                <td>{vtranslate('LBL_DATE_FORMAT','Install')} <span class="no text-danger ms-2">*</span></td>
                                <td>
                                    <select class="select2"  name="dateformat">
                                        <option value="mm-dd-yyyy">mm-dd-yyyy</option>
                                        <option value="dd-mm-yyyy">dd-mm-yyyy</option>
                                        <option value="yyyy-mm-dd">yyyy-mm-dd</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    {vtranslate('LBL_TIME_ZONE','Install')} <span class="no text-danger ms-2">*</span>
                                </td>
                                <td>
                                    <div>
                                        <select class="select2" name="timezone">
                                            {foreach item=TIMEZONE from=$TIMEZONES}
                                                <option value="{$TIMEZONE}" {if $TIMEZONE eq 'America/Los_Angeles'}selected{/if}>{vtranslate($TIMEZONE, 'Users')}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
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