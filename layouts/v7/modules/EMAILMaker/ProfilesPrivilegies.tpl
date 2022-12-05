{*<!--
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{assign var="ENABLE_IMAGE_PATH" value="{vimage_path('Enable.png')}"}
{assign var="DISABLE_IMAGE_PATH" value="{vimage_path('Disable.png')}"}
<div class="container-fluid">
    <form name="profiles_privilegies" action="index.php" method="post" class="form-horizontal">
        <br>
        <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_PROFILES','EMAILMaker')}</label>
        {if $MODE neq "edit"}
            <button class="btn pull-right" type="submit">{vtranslate('LBL_EDIT','EMAILMaker')}</button>{/if}
        <br clear="all">{vtranslate('LBL_PROFILES_DESC','EMAILMaker')}
        <hr>
        <input type="hidden" name="module" value="EMAILMaker"/>
        {if $MODE eq "edit"}
            <input type="hidden" name="action" value="IndexAjax"/>
            <input type="hidden" name="mode" value="SaveProfilesPrivilegies"/>
        {else}
            <input type="hidden" name="view" value="ProfilesPrivilegies"/>
            <input type="hidden" name="mode" value="edit"/>
        {/if}
        <br/>
        <div class="row-fluid">
            <label class="fieldLabel"><strong>{vtranslate('LBL_SETPRIVILEGIES','EMAILMaker')}:</strong></label><br>

            <table class="table table-striped table-bordered profilesEditView">
                <thead>
                <tr class="blockHeader">
                    <th style="border-left: 1px solid #DDD !important;" width="40%">{vtranslate('LBL_PROFILES','EMAILMaker')}</th>
                    <th style="border-left: 1px solid #DDD !important;" width="15%" align="center">{vtranslate('LBL_CREATE_EDIT','EMAILMaker')}</th>
                    <th style="border-left: 1px solid #DDD !important;" width="15%" align="center">{vtranslate('LBL_VIEW','EMAILMaker')}</th>
                    <th style="border-left: 1px solid #DDD !important;" width="15%" align="center">{vtranslate('LBL_DELETE','EMAILMaker')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=arr from=$PERMISSIONS}
                    {foreach key=profile_name item=profile_arr from=$arr}
                        <tr>
                            <td class="cellLabel">
                                {$profile_name}
                            </td>
                            <td class="cellText" align="center">
                                {if $MODE eq "edit"}
                                    <input type="checkbox" {$profile_arr.EDIT.checked} id="{$profile_arr.EDIT.name}" name="{$profile_arr.EDIT.name}" onclick="other_chk_clicked(this, '{$profile_arr.DETAIL.name}');"/>
                                {else}
                                    <img style="margin-left: 40%" class="alignMiddle" src="{if $profile_arr.EDIT.checked neq ""}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"/>
                                {/if}
                            </td>
                            <td class="cellText" align="center">
                                {if $MODE eq "edit"}
                                    <input type="checkbox" {$profile_arr.DETAIL.checked} id="{$profile_arr.DETAIL.name}" name="{$profile_arr.DETAIL.name}" onclick="view_chk_clicked(this, '{$profile_arr.EDIT.name}', '{$profile_arr.DELETE.name}');"/>
                                {else}
                                    <img style="margin-left: 40%" class="alignMiddle" src="{if $profile_arr.DETAIL.checked neq ""}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"/>
                                {/if}
                            </td>
                            <td class="cellText" align="center">
                                {if $MODE eq "edit"}
                                    <input type="checkbox" {$profile_arr.DELETE.checked} id="{$profile_arr.DELETE.name}" name="{$profile_arr.DELETE.name}" onclick="other_chk_clicked(this, '{$profile_arr.DETAIL.name}');"/>
                                {else}
                                    <img style="margin-left: 40%" class="alignMiddle" src="{if $profile_arr.DELETE.checked neq ""}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"/>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                {/foreach}
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
{literal}
    <script language="javascript" type="text/javascript">
        function view_chk_clicked(source_chk, edit_chk_id, delete_chk_id) {
            if (source_chk.checked == false) {
                document.getElementById(edit_chk_id).checked = false;
                document.getElementById(delete_chk_id).checked = false;
            }
        }

        function other_chk_clicked(source_chk, detail_chk) {
            if (source_chk.checked == true) {
                document.getElementById(detail_chk).checked = true;
            }
        }
    </script>
{/literal}    