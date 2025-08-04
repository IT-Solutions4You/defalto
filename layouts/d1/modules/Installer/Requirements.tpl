{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="contents" id="requirementsContents">
        {include file="SourceModules.tpl"|vtemplate_path:$MODULE}
        <div>
            <h4>{vtranslate('LBL_PHP_REQUIREMENTS', $QUALIFIED_MODULE)}:</h4>
            <div class="container-fluid">
                <div class="row border py-2 fw-bold">
                    <div class="col"></div>
                    <div class="col">{vtranslate('LBL_CURRENT_VALUE', $QUALIFIED_MODULE)}</div>
                    <div class="col">{vtranslate('LBL_MINIMUM_REQ', $QUALIFIED_MODULE)}</div>
                    <div class="col">{vtranslate('LBL_RECOMMENDED_REQ', $QUALIFIED_MODULE)}</div>
                </div>
                {foreach from=$REQUIREMENTS->getPHPSettings() key=NAME item=DATA}
                    <div class="row border py-2 {if 'yes' eq $DATA['error']}text-danger{elseif 'yes' eq $DATA['warning']}text-warning{else}text-success{/if}">
                        <div class="col"><b>{vtranslate($NAME, $QUALIFIED_MODULE)}</b> {if $DATA['info']}({vtranslate($DATA['info'], $QUALIFIED_MODULE)}){/if}</div>
                        <div class="col">{$DATA['current']}</div>
                        <div class="col">{$DATA['minimum']}</div>
                        <div class="col">{$DATA['recommended']}</div>
                    </div>
                {/foreach}
            </div>
        </div>
        <br>
        <div>
            <h4>{vtranslate('LBL_DB_REQUIREMENTS', $QUALIFIED_MODULE)}:</h4>
            <div class="container-fluid">
                <div class="row border py-2 fw-bold">
                    <div class="col"></div>
                    <div class="col">{vtranslate('LBL_CURRENT_VALUE', $QUALIFIED_MODULE)}</div>
                    <div class="col">{vtranslate('LBL_RECOMMENDED_DESCRIPTION', $QUALIFIED_MODULE)}</div>
                    <div class="col"></div>
                </div>
                {foreach from=$REQUIREMENTS->getDBSettings() key=NAME item=DATA}
                    <div class="row border py-2 {if 'yes' eq $DATA['error']}text-danger{elseif 'yes' eq $DATA['warning']}text-warning{else}text-success{/if}">
                        <div class="col"><b>{vtranslate($NAME, $QUALIFIED_MODULE)}</b> {if $DATA['info']}({vtranslate($DATA['info'], $QUALIFIED_MODULE)}){/if}</div>
                        <div class="col">{$DATA['current']}</div>
                        <div class="col">
                            {vtranslate($DATA['recommended_description'], $QUALIFIED_MODULE)}
                        </div>
                        <div class="col"></div>
                    </div>
                {/foreach}
            </div>
        </div>
        <br>
        <div>
            <h4>{vtranslate('LBL_FILE_REQUIREMENTS', $QUALIFIED_MODULE)}:</h4>
            <div class="clearfix">
                <a class="btn btn-outline-secondary" href="index.php?module=Installer&parent=Settings&view=Requirements&scan=SubFolders">
                    {vtranslate('LBL_SCAN_SUB_FOLDERS', $QUALIFIED_MODULE)}
                </a>
            </div>
            <br>
            <div class="container-fluid">
                <div class="row border py-2 fw-bold">
                    <div class="col">{vtranslate('LBL_FILE_FOLDER', $QUALIFIED_MODULE)}</div>
                    <div class="col">{vtranslate('LBL_CURRENT_VALUE_WRITABLE', $QUALIFIED_MODULE)}</div>
                </div>
                {foreach from=$REQUIREMENTS->getFilePermissions() key=NAME item=DATA}
                    <div class="row border py-2 {if 'yes' eq $DATA['error']}text-danger{elseif 'yes' eq $DATA['warning']}text-warning{else}text-success{/if}">
                        <div class="col"><b>{vtranslate($NAME, $QUALIFIED_MODULE)}</b> {if $DATA['info']}({vtranslate($DATA['info'], $QUALIFIED_MODULE)}){/if}</div>
                        <div class="col">{$DATA['current']}</div>
                        <div class="col"></div>
                        <div class="col"></div>
                    </div>
                {/foreach}
            </div>
        </div>
        <br>
        <div>
            <h4>{vtranslate('LBL_USER_REQUIREMENTS', $QUALIFIED_MODULE)}:</h4>
            <div class="container-fluid">
                <div class="row border py-2 fw-bold">
                    <div class="col"></div>
                    <div class="col">{vtranslate('LBL_CURRENT_VALUE_ERROR', $QUALIFIED_MODULE)}</div>
                    <div class="col"></div>
                    <div class="col"></div>
                </div>
                {foreach from=$REQUIREMENTS->getUserSettings() key=NAME item=DATA}
                    <div class="row border py-2 {if 'yes' eq $DATA['error']}text-danger{elseif 'yes' eq $DATA['warning']}text-warning{else}text-success{/if}">
                        <div class="col"><b>{vtranslate($NAME, $QUALIFIED_MODULE)}</b> {if $DATA['info']}({vtranslate($DATA['info'], $QUALIFIED_MODULE)}){/if}</div>
                        <div class="col">{$DATA['current']}</div>
                        <div class="col"></div>
                        <div class="col"></div>
                    </div>
                {/foreach}
            </div>
        </div>
        <br>
    </div>
{/strip}
