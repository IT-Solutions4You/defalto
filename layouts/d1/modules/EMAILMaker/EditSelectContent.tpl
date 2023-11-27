{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
<div class="main-container main-container-{$MODULE}">
    <div class='editViewContainer '>

        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
            <div class="row-fluid">
                <div class="col-xs-12">
                    <input type="hidden" name="module" value="EMAILMaker">
                    <input type="hidden" name="parenttab" value="{$PARENTTAB}">
                    <input type="hidden" name="templateid" value="{$SAVETEMPLATEID}">
                    <input type="hidden" name="action" value="SaveEMAILTemplate">
                    <input type="hidden" name="redirect" value="true">
                    <br>
                    <label class="pull-left themeTextColor font-x-x-large">{vtranslate('LBL_THEME_LIST','EMAILMaker')}</label>
                    <br clear="all">
                    <hr>
                    <div class="row-fluid">
                        <label class="fieldLabel"><strong>{vtranslate('LBL_THEME_GENERATOR_DESCRIPTION','EMAILMaker')}</strong></label><br>
                    </div>
                    <br>


                    <div class="col-sm-12 portal-dashboard">
                        <div id="dashboardContent" class="show"><h4>{vtranslate('LBL_SELECT_THEME','EMAILMaker')}</h4>
                            <hr class="hrHeader">

                            <div class="row-fluid">
                                <div class="col-lg-2 col-md-2 col-sm-2 " style="margin-bottom:10px;">
                                    <div class="extension_container extensionWidgetContainer">
                                        <div class="extension_header">
                                            <div class="font-x-x-large boxSizingBorderBox" style="font-size: 14px;"><a href="index.php?module=EMAILMaker&view=Edit&return_module=EMAILMaker&return_view=List">Blank</a>
                                            </div>
                                        </div>
                                        <div style="padding-left:3px;">
                                            <div class="extension_contents padding10" style="border:none;">
                                                <a href="index.php?module=EMAILMaker&view=Edit&return_module=EMAILMaker&return_view=List"><img src="modules/EMAILMaker/templates/blank.png" border="0"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {foreach item=templatename key=templatenameid from=$EMAILTEMPLATES}
                                    <div class="col-lg-2 col-md-2 col-sm-2 " style="margin-bottom:10px;">
                                        <div class="extension_container extensionWidgetContainer">
                                            <div class="extension_header">
                                                <div class="font-x-x-large boxSizingBorderBox" style="font-size: 14px;"><a href="index.php?module=EMAILMaker&view=Edit&theme={$templatename}&return_module=EMAILMaker&return_view=List">{$templatename}</a>
                                                </div>
                                            </div>
                                            <div style="padding-left:3px;">
                                                <div class="extension_contents" style="border:none;">
                                                    <a href="index.php?module=EMAILMaker&view=Edit&theme={$templatename}&return_module=EMAILMaker&return_view=List"><img src="modules/EMAILMaker/templates/{$templatename}/image.png" border="0"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                                {foreach item=theme name=themes from=$EMAILTHEMES}
                                    <div class="col-lg-4 col-md-4 col-sm-4 " style="margin-bottom:10px;">
                                        <div class="extension_container extensionWidgetContainer">
                                            <div class="extension_header row">
                                                <div class="col-lg-10 col-md-10 col-sm-10">
                                                    <div class="font-x-x-large boxSizingBorderBox" style="font-size: 14px;">
                                                        <a href="index.php?module=EMAILMaker&view=Edit&themeid={$theme.themeid}&return_module=EMAILMaker&return_view=List" title="{$theme.themename}">{$theme.themename}
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-md-2 col-sm-2">
                                                    {if $theme.edit neq ""}
                                                        <div class="pull-right">
                                                            {$theme.edit}
                                                        </div>
                                                    {/if}
                                                </div>
                                            </div>
                                            <div style="padding-left:3px;">
                                                <div class="extension_contents" style="border:none;">
                                                    {$theme.description}
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                {/foreach}

                                <div class="col-lg-2 col-md-2 col-sm-2 " style="margin-bottom:10px;">
                                    <div class="extension_container extensionWidgetContainer">
                                        <div class="extension_header">
                                            <div class="font-x-x-large boxSizingBorderBox" style="font-size: 14px;"><a href="index.php?module=EMAILMaker&view=Edit&theme=new&mode=EditTheme&return_module=EMAILMaker&return_view=List">{vtranslate('LBL_ADD_THEME','EMAILMaker')}</a>
                                            </div>
                                        </div>
                                        <div style="padding-left:3px;">
                                            <div class="extension_contents" style="border:none;">
                                                {vtranslate('LBL_ADD_THEME_INFO','EMAILMaker')}
                                            </div>
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