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
        <form class="rounded bg-body" name="step3" method="get" action="index.php">
            <input type=hidden name="module" value="Install"/>
            <input type=hidden name="view" value="Index"/>
            <input type=hidden name="mode" value="Step4"/>
            {include file='StepHeader.tpl'|@vtemplate_path:'Install' TITLE='LBL_INSTALL_PREREQUISITES'}
            <div class="container p-3">
                <div class="button-container text-end">
                    <a href="#">
                        <input type="button" class="btn btn-primary" value="{vtranslate('LBL_RECHECK', 'Install')}" id='recheck'/>
                    </a>
                </div>
                <div class="row justify-content-around">
                    <div class="col">
                        <table class="table config-table">
                            <tr>
                                <th >{vtranslate('LBL_PHP_CONFIGURATION', 'Install')}</th>
                                <th class="w-25">{vtranslate('LBL_REQUIRED_VALUE', 'Install')}</th>
                                <th class="w-25">{vtranslate('LBL_PRESENT_VALUE', 'Install')}</th>
                            </tr>
                            {foreach key=CONFIG_NAME item=INFO from=$SYSTEM_PREINSTALL_PARAMS}
                                <tr>
                                    <td>{vtranslate($CONFIG_NAME, 'Install')}</td>
                                    <td>
                                        {if $INFO.1 eq 1}
                                            {vtranslate('LBL_YES', 'Install')}
                                        {else}
                                            {$INFO.1}
                                        {/if}
                                    </td>
                                    <td {if $INFO.2 eq false} class="no">
                                        {if $CONFIG_NAME = 'LBL_PHP_VERSION'}
                                            {$INFO.0}
                                        {else}
                                            {vtranslate('LBL_NO', 'Install')}
                                        {/if}
                                        {elseif ($INFO.2 eq true and $INFO.1 === true)} >
                                        {vtranslate('LBL_YES', 'Install')}
                                        {else} >
                                        {$INFO.0}
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                        {if $PHP_INI_CURRENT_SETTINGS}
                            <table class="table config-table">
                                <tr>
                                    <th>{vtranslate('LBL_PHP_RECOMMENDED_SETTINGS', 'Install')}</th>
                                    <th class="w-25"></th>
                                    <th class="w-25"></th>
                                </tr>
                                {foreach key=DIRECTIVE item=VALUE from=$PHP_INI_CURRENT_SETTINGS name=directives}
                                    <tr>
                                        <td>{$DIRECTIVE}</td>
                                        <td>{$PHP_INI_RECOMMENDED_SETTINGS[$DIRECTIVE]}</td>
                                        <td class="no">{$VALUE}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        {/if}
                        {if $FAILED_FILE_PERMISSIONS}
                            <table class="table config-table">
                                <tr>
                                    <th>{vtranslate('LBL_READ_WRITE_ACCESS', 'Install')}</th>
                                    <th class="w-50"></th>
                                </tr>
                                {foreach item=FILE_PATH key=FILE_NAME from=$FAILED_FILE_PERMISSIONS}
                                    <tr>
                                        <td nowrap>{$FILE_NAME} ({str_replace("./","",$FILE_PATH)})</td>
                                        <td class="no">{vtranslate('LBL_NO', 'Install')}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="button-container text-end p-3">
                <input type="button" class="btn btn-primary me-2" value="{vtranslate('LBL_BACK', 'Install')}" name="back"/>
                <input type="button" class="btn btn-primary active" value="{vtranslate('LBL_NEXT', 'Install')}" name="step4"/>
            </div>
        </form>
    </div>
</div>