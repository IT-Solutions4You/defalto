{*<!--
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<div class="container-fluid" id="licenseContainer">
    <form action="index.php" method="post" class="form-horizontal">
        <br>
        <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_EXTENSIONS','EMAILMaker')}</label>
        <br clear="all">
        <hr>
        <input type="hidden" name="module" value="EMAILMaker"/>
        <input type="hidden" name="view" value=""/>
        <br/>
        <div class="row-fluid">
            <label class="fieldLabel"><strong>{vtranslate('LBL_AVAILABLE_EXTENSIONS','EMAILMaker')}:</strong></label>
            {foreach item=arr key=extname from=$EXTENSIONS_ARR}
                <br>
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr class="blockHeader">
                        <th colspan="2">
                            <div class="textAlignLeft">{vtranslate($arr.label, 'EMAILMaker')}
                                {if $arr.download neq ""}
                                    <span class="pull-right">
                                        <a class="btn" href="{$arr.download}">{vtranslate('LBL_DOWNLOAD', 'EMAILMaker')}</a>
                                    </span>
                                {/if}
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="padding5per">
                            <div class="padding10">
                                {vtranslate($arr.desc, 'EMAILMaker')}
                                {if $arr.exinstall neq ""}
                                    <br>
                                    <br>
                                    <b>{vtranslate('LBL_INSTAL_EXT', 'EMAILMaker')}</b>
                                    <br>
                                    {vtranslate($arr.exinstall, 'EMAILMaker')}
                                {/if}
                                {if $arr.manual neq ""}
                                    <br>
                                    <br>
                                    <b> <a href="{$arr.manual}" style="cursor: pointer">{vtranslate($arr.manual_label, 'EMAILMaker')}</a></b>
                                {/if}
                                {if $arr.install_info neq ""}
                                    <br>
                                    <br>
                                    <div id="install_{$extname}_info" class="fontBold{if $arr.install_info eq ""} hide{/if}">{$arr.install_info}</div>
                                {/if}
                                {if $arr.install neq ""}
                                    <br>
                                    <button type="button" id="install_{$extname}_btn" class="btn btn-success" data-extname="{$extname}" data-url="{$arr.install}">{vtranslate('LBL_INSTALL_BUTTON', 'Install')}</button>
                                {/if}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            {/foreach}
        </div>
        {if $MODE eq "edit"}
            <div class="pull-right">
                <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
                <a class="cancelLink" onclick="window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
            </div>
        {/if}
    </form>
</div>
<script language="javascript" type="text/javascript">
    {if $ERROR eq 'true'}
    alert('{vtranslate('ALERT_DOWNLOAD_ERROR', 'EMAILMaker')}');
    {/if}
</script>
   