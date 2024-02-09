{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="contents tabbable ui-sortable h-main">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
            <input type="hidden" name="module" value="EMAILMaker">
            <input type="hidden" name="parenttab" value="{$PARENTTAB}">
            <input type="hidden" name="templateid" id="templateid" value="{$SAVETEMPLATEID}">
            <input type="hidden" name="action" value="SaveEMAILTemplate">
            <input type="hidden" name="redirect" value="true">
            <input type="hidden" name="return_module" value="{$REQUEST_INSTANCE.return_module}">
            <input type="hidden" name="return_view" value="{$REQUEST_INSTANCE.return_view}">
            <input type="hidden" name="is_theme" value="{if $THEME_MODE eq "true"}1{else}0{/if}">
            <input type="hidden" name="selectedTab" id="selectedTab" value="properties">
            <input type="hidden" name="selectedTab2" id="selectedTab2" value="body">
            <ul class="nav nav-pills layoutTabs massEditTabs bg-body p-3">
                <li class="detailviewTab nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#pdfContentEdit" aria-expanded="true"><strong>{vtranslate('LBL_PROPERTIES_TAB',$MODULE)}</strong></a>
                </li>
                <li class="detailviewTab nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#pdfContentOther" aria-expanded="false"><strong>{vtranslate('LBL_OTHER_INFO',$MODULE)}</strong></a>
                </li>
                <li class="detailviewTab nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#pdfContentLabels" aria-expanded="false"><strong>{vtranslate('LBL_LABELS',$MODULE)}</strong></a>
                </li>
                {if $THEME_MODE neq "true"}
                    <li class="detailviewTab nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#pdfContentProducts" aria-expanded="false"><strong>{vtranslate('LBL_ARTICLE',$MODULE)}</strong></a>
                    </li>
                {/if}
                <li class="detailviewTab nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#editTabSettings" aria-expanded="false"><strong>{vtranslate('LBL_SETTINGS_TAB',$MODULE)}</strong></a>
                </li>
                {if $THEME_MODE neq "true"}
                    <li class="detailviewTab nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#editTabSharing" aria-expanded="false"><strong>{vtranslate('LBL_SHARING_TAB',$MODULE)}</strong></a>
                    </li>
                {/if}
            </ul>
            <div>
                {********************************************* Settings DIV *************************************************}
                <div class="container-fluid">
                    <div class="row">
                        <div class="left-block col-lg-4">
                            <div class="tab-content layoutContent rounded bg-body p-3 mt-3">
                                {include file='tabs/Properties.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Other.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Labels.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/ProductBlock.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Settings.tpl'|@vtemplate_path:$MODULE}
                                {include file='tabs/Sharing.tpl'|@vtemplate_path:$MODULE}
                            </div>
                        </div>
                        {************************************** END OF TABS BLOCK *************************************}

                        <div class="middle-block col-lg-8">
                            <div class="bg-body rounded p-3 my-3">
                            {if $THEME_MODE neq "true"}
                                {* email subject *}
                                <div id="properties_div">
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <label class="text-muted" for="subject">{vtranslate('LBL_EMAIL_SUBJECT','EMAILMaker')}:</label>
                                        </div>
                                        <div class="col-sm">
                                            <input name="subject" id="subject" type="text" value="{$EMAIL_TEMPLATE_RESULT.subject}" class="inputElement nameField form-control" tabindex="1">
                                        </div>
                                        <div class="col-sm-3">
                                            <select name="subject_fields" id="subject_fields" class="select2 form-control" onchange="EMAILMaker_EditJs.insertFieldIntoSubject(this.value);">
                                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','EMAILMaker')}</option>
                                                <optgroup label="{vtranslate('LBL_COMMON_EMAILINFO','EMAILMaker')}">
                                                    {html_options  options=$SUBJECT_FIELDS}
                                                </optgroup>
                                                {if $TEMPLATEID neq "" || $SELECTMODULE neq ""}
                                                    {html_options  options=$SELECT_MODULE_FIELD_SUBJECT}
                                                {/if}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                            {*********************************************BODY DIV*************************************************}
                            <div class="tab-content">
                                <div class="tab-pane active mb-3" id="body_div2">
                                    <textarea name="body" id="body" style="width: 100%; height:700px" class=small tabindex="5">{$EMAIL_TEMPLATE_RESULT.body}</textarea>
                                </div>
                                {if $ITS4YOUSTYLE_FILES neq ""}
                                    <div class="tab-pane" id="cssstyle_div2">
                                        {foreach item=STYLE_DATA from=$STYLES_CONTENT}
                                            <div class="hide">
                                                <textarea class="CodeMirrorContent" id="CodeMirrorContent{$STYLE_DATA.id}" style="border: 1px solid black; " class="CodeMirrorTextarea " tabindex="5">{$STYLE_DATA.stylecontent}</textarea>
                                            </div>
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr class="listViewHeaders">
                                                    <th>
                                                        <div class="pull-left">
                                                            <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank">{$STYLE_DATA.name}</a>
                                                        </div>
                                                        <div class="pull-right actions">
                                                            <a href="index.php?module=ITS4YouStyles&view=Detail&record={$STYLE_DATA.id}" target="_blank"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
                                                            {if $STYLE_DATA.iseditable eq "yes"}
                                                                <a href="index.php?module=ITS4YouStyles&view=Edit&record={$STYLE_DATA.id}" target="_blank" class="cursorPointer"><i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></i></a>
                                                            {/if}
                                                        </div>
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td id="CodeMirrorContent{$STYLE_DATA.id}Output" class="cm-s-default">

                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                            <br>
                                        {/foreach}
                                    </div>
                                {/if}
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="col text-end">
                                            {if $REQUEST_INSTANCE.return_view neq ''}
                                                <a class="btn btn-primary cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $REQUEST_INSTANCE.return_module neq ''}{$REQUEST_INSTANCE.return_module}{else}EMAILMaker{/if}&view={$REQUEST_INSTANCE.return_view}{if $REQUEST_INSTANCE.templateid neq ""  && $REQUEST_INSTANCE.return_view neq "List"}&templateid={$REQUEST_INSTANCE.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                            {else}
                                                <a class="btn btn-primary cancelLink" type="reset" onclick="window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                            {/if}
                                        </div>
                                        <div class="col-auto">
                                            <button class="btn btn-primary" type="submit" onclick="document.EditView.redirect.value = 'false';"><strong>{vtranslate('LBL_APPLY',$MODULE)}</strong></button>
                                        </div>
                                        <div class="col">
                                            <button class="btn btn-primary active" type="submit">{vtranslate('LBL_SAVE', $MODULE)}</button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <script type="text/javascript">
                                {literal} jQuery(document).ready(function () {{/literal}
                                    {if $ITS4YOUSTYLE_FILES neq ""}
                                    //CKEDITOR.config.contentsCss = [{$ITS4YOUSTYLE_FILES}];
                                    {literal}
                                    jQuery('.CodeMirrorContent').each(function (index, Element) {
                                        var stylecontent = jQuery(Element).val();
                                        CKEDITOR.addCss(stylecontent);
                                    });
                                    {/literal}
                                    {/if}{literal}
                                    let ckeditor = new Vtiger_CkEditor_Js();
                                    ckeditor.loadCkEditor($('#body'), {'height' : '65vh'});
                                }){/literal}
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hide" style="display: none">
                <div id="div_vat_block_table">{$VATBLOCK_TABLE}</div>
                <div id="div_charges_block_table">{$CHARGESBLOCK_TABLE}</div>
                <div id="div_company_header_signature">{$COMPANY_HEADER_SIGNATURE}</div>
                <div id="div_company_stamp_signature">{$COMPANY_STAMP_SIGNATURE}</div>
                <div id="div_company_logo">{$COMPANYLOGO}</div>
            </div>
        </form>
    </div>
    <script type="text/javascript">

        var selectedTab = 'properties';
        var selectedTab2 = 'body';
        var module_blocks = [];

        var selected_module = '{$SELECTMODULE}';

        var constructedOptionValue;
        var constructedOptionName;

        jQuery(document).ready(function () {

            jQuery.fn.scrollBottom = function () {
                return jQuery(document).height() - this.scrollTop() - this.height();
            };

            var $el = jQuery('.edit-template-content');
            var $window = jQuery(window);
            var top = 127;

            $window.bind("scroll resize", function () {

                var gap = $window.height() - $el.height() - 20;
                var scrollTop = $window.scrollTop();

                if (scrollTop < top - 125) {
                    $el.css({
                        top: (top - scrollTop) + "px",
                        bottom: "auto"
                    });
                } else {
                    $el.css({
                        top: top + "px",
                        bottom: "auto"
                    });
                }
            }).scroll();
        });

    </script>
{/strip}
