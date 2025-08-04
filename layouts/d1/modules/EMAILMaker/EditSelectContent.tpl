{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="container-fluid p-4 main-container main-container-{$MODULE}">
    <div class="editViewContainer bg-body rounded p-3">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
            <div class="row">
                <div class="col-xl-12">
                    <input type="hidden" name="module" value="EMAILMaker">
                    <input type="hidden" name="parenttab" value="{$PARENTTAB}">
                    <input type="hidden" name="templateid" value="{$SAVETEMPLATEID}">
                    <input type="hidden" name="action" value="SaveEMAILTemplate">
                    <input type="hidden" name="redirect" value="true">
                    <h3>{vtranslate('LBL_THEME_LIST','EMAILMaker')}</h3>
                    <p>{vtranslate('LBL_THEME_GENERATOR_DESCRIPTION','EMAILMaker')}</p>
                    <br>
                    <div class="col-sm-12 portal-dashboard">
                        <h3>{vtranslate('LBL_SELECT_THEME','EMAILMaker')}</h3>
                        <hr>
                        <div id="dashboardContent" class="show">
                            <div class="row">
                                <div class="col-lg-2 col-md-2 col-sm-2 mb-3">
                                    <div class="extension_container extensionWidgetContainer">
                                        <div class="extension_header p-2 fs-4">
                                            <a href="index.php?module=EMAILMaker&view=Edit&return_module=EMAILMaker&return_view=List">Blank</a>
                                        </div>
                                        <div>
                                            <div class="extension_contents m-0 p-2">
                                                <a href="index.php?module=EMAILMaker&view=Edit&return_module=EMAILMaker&return_view=List">
                                                    <img src="modules/EMAILMaker/templates/blank.png">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {foreach item=templatename key=templatenameid from=$EMAILTEMPLATES}
                                    <div class="col-lg-2 col-md-2 col-sm-2 mb-3">
                                        <div class="extension_container extensionWidgetContainer">
                                            <div class="extension_header p-2 fs-4">
                                                <a href="index.php?module=EMAILMaker&view=Edit&theme={$templatename}&return_module=EMAILMaker&return_view=List">{$templatename}</a>
                                            </div>
                                            <div class="extension_contents m-0 p-2">
                                                <a href="index.php?module=EMAILMaker&view=Edit&theme={$templatename}&return_module=EMAILMaker&return_view=List">
                                                    <img src="modules/EMAILMaker/templates/{$templatename}/image.png">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                                {foreach item=theme name=themes from=$EMAILTHEMES}
                                    <div class="col-lg-4 col-md-4 col-sm-4 mb-3">
                                        <div class="extension_container extensionWidgetContainer">
                                            <div class="extension_actions float-end p-2">
                                                {if $theme.edit neq ""}
                                                    {$theme.edit}
                                                {/if}
                                            </div>
                                            <div class="extension_header p-2 fs-4">
                                                <a href="index.php?module=EMAILMaker&view=Edit&themeid={$theme.themeid}&return_module=EMAILMaker&return_view=List" title="{$theme.themename}">{$theme.themename}</a>
                                            </div>
                                            <div class="extension_contents m-0 p-2">
                                                {$theme.description}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                                <div class="col-lg-2 col-md-2 col-sm-2 mb-3">
                                    <div class="extension_container extensionWidgetContainer">
                                        <div class="extension_header p-2 fs-4">
                                            <a href="index.php?module=EMAILMaker&view=Edit&theme=new&mode=EditTheme&return_module=EMAILMaker&return_view=List">{vtranslate('LBL_ADD_THEME','EMAILMaker')}</a>
                                        </div>
                                        <div class="extension_contents m-0 p-2">
                                            {vtranslate('LBL_ADD_THEME_INFO','EMAILMaker')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>