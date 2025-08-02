<label class="fieldLabel"><strong>{vtranslate('LBL_UNINSTALL_DESC',$MODULE)}:</strong></label><br>
{strip}
    <div class="container-fluid" id="Uninstall{$MODULE}Container">
        <form name="profiles_privilegies" action="index.php" method="post" class="form-horizontal">
            <br>
            <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_UNINSTALL',$MODULE)}</label>
            <br clear="all">
            <hr>
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="view" value=""/>
            <input type="hidden" name="license_key_val" id="license_key_val" value="{$LICENSE}"/>
            <br/>
            <div class="row-fluid">
                <label class="fieldLabel"><strong>{vtranslate('LBL_UNINSTALL_DESC',$MODULE)}:</strong></label><br>
                <table class="table table-bordered table-condensed themeTableColor">
                    <thead>
                    <tr class="blockHeader">
                        <th class="mediumWidthType">
                            <span class="alignMiddle">{vtranslate('LBL_UNINSTALL', $MODULE)}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="textAlignCenter">
                            <button id="uninstall_{$MODULE}_btn" type="button" class="btn btn-danger marginLeftZero">{vtranslate('LBL_UNINSTALL',$MODULE)}</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            {if $MODE eq "edit"}
                <div class="pull-right">
                    <button class="btn btn-success" type="submit">{vtranslate('LBL_SAVE',$MODULE)}</button>
                    <a class="cancelLink" onclick="window.history.back();" type="reset">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            {/if}
        </form>
    </div>
{/strip}