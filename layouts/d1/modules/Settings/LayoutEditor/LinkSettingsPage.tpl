{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="px-4 pb-4" id="linkSettingsEditor" data-source-module="{$SOURCE_MODULE|escape:'html'}">
    <div class="bg-body rounded">
        <div class="container-fluid p-3 border-bottom">
            <div class="row align-items-center g-3">
                <div class="col-lg"><h4 class="m-0">{vtranslate('LBL_LINK_SETTINGS', 'Core')|escape:'html'}</h4></div>
                <label class="col-lg-auto" for="linkSettingsSourceModule">{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)|escape:'html'}</label>
                <div class="col-lg-4">
                    <select class="select2 form-select" name="sourceModule" id="linkSettingsSourceModule">
                        {foreach item=MODULE_NAME key=MODULE_LABEL from=$SUPPORTED_MODULES}
                            <option value="{$MODULE_NAME|escape:'html'}" {if $MODULE_NAME eq $SOURCE_MODULE}selected{/if}>{$MODULE_LABEL|escape:'html'}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="container-fluid p-3 linkSettingsContents">
            {include file='LinkSettings.tpl'|vtemplate_path:$QUALIFIED_MODULE}
        </div>
    </div>
</div>
{/strip}