{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="settingsIndexPage px-4 pb-4">
        <div class="p-3 bg-body rounded h-list">
            <div>
                <h4>{vtranslate('LBL_SUMMARY',$MODULE)}</h4>
            </div>
            <hr>
            <div class="row justify-content-around">
				<div class="col-lg-2 col-md-4 col-sm-4 settingsSummary text-center">
					<a href="index.php?module=Users&parent=Settings&view=List" class="d-flex flex-column justify-content-center h-100 w-100 bg-body-secondary rounded text-center py-5 border">
                        <h2 class="summaryCount">{$USERS_COUNT}</h2>
                        <p class="summaryText">{vtranslate('LBL_ACTIVE_USERS',$MODULE)}</p>
					</a>
				</div>
                <div class="col-lg-2 col-md-4 col-sm-4 settingsSummary">
					<a href="index.php?module=Workflows&parent=Settings&view=List&parentblock=LBL_AUTOMATION" class="d-flex flex-column justify-content-center h-100 w-100 bg-body-secondary rounded text-center py-5 border">
						<h2 class="summaryCount">{$ACTIVE_WORKFLOWS}</h2>
						<p class="summaryText">{vtranslate('LBL_WORKFLOWS_ACTIVE',$MODULE)}</p>
					</a>
				</div>
                <div class="col-lg-2 col-md-4 col-sm-4 settingsSummary">
					<a href="index.php?module=ModuleManager&parent=Settings&view=List" class="d-flex flex-column justify-content-center h-100 w-100 bg-body-secondary rounded text-center py-5 border">
						<h2 class="summaryCount">{$ACTIVE_MODULES}</h2>
						<p class="summaryText">{vtranslate('LBL_MODULES',$MODULE)}</p>
					</a>
				</div>
            </div>
            <br>
            <br>
            <h4>{vtranslate('LBL_SETTINGS_SHORTCUTS',$MODULE)}</h4>
            <hr>
            <div id="settingsShortCutsContainer" class="container-fluid">
                <div class="row">
                    {assign var=COUNTER value=0}
                    {foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
                        {include file='SettingsShortCut.tpl'|@vtemplate_path:$MODULE}
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
{/strip}