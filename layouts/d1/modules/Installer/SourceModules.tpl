{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="row">
    <div class="col-lg">
        <h4><b>{vtranslate($REQUIREMENTS_TITLE, $QUALIFIED_MODULE)}</b></h4>
    </div>
    <div class="col-lg-4">
        <select id="source_module" class="select2" style="width: 300px;">
            <optgroup label="{vtranslate('LBL_SYSTEM_REQUIREMENTS', $QUALIFIED_MODULE)}">
                <option value="index.php?module=Installer&view=Requirements">{vtranslate('LBL_SYSTEM', $QUALIFIED_MODULE)}</option>
            </optgroup>
            <optgroup label="{vtranslate('LBL_MODULE_REQUIREMENTS', $QUALIFIED_MODULE)}">
                {foreach from=$SOURCE_MODULES item=SOURCE_MODULE}
                    <option value="{$SOURCE_MODULE->getDefaultUrl()}" {if $SOURCE_MODULE_NAME eq $SOURCE_MODULE->getModuleName()} selected="selected" {/if}>{$SOURCE_MODULE->getModuleLabel()}</option>
                {/foreach}
            </optgroup>
        </select>
    </div>
</div>