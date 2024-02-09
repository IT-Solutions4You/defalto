{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="quickWidgetContainer accordion">
        {assign var=val value=1}
        {foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
            <div class="quickWidget">
                <div class="accordion-heading accordion-toggle quickWidgetHeader" data-target="#{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}"
                     data-toggle="collapse" data-parent="#quickWidgets" data-label="{$SIDEBARWIDGET->getLabel()}"
                     data-widget-url="{$SIDEBARWIDGET->getUrl()}" >
                    <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
                    <h5 class="title widgettext-truncate pull-right" title="{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}">{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
                    <div class="loadingImg hide pull-right">
                        <div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARWIDGET->getLabel())}" data-url="{$SIDEBARWIDGET->getUrl()}">
                </div>
            </div>
        {/foreach}
        {if $REQUEST_INSTANCE.view eq 'List' || $REQUEST_INSTANCE.view eq 'Detail'}
        <div class="quickWidget">
            <div class="accordion-heading accordion-toggle quickWidgetHeader" data-target="#{$MODULE}_sideBar_Tools"
                 data-toggle="collapse" data-parent="#quickWidgets" data-label="{vtranslate('Tools', $MODULE)}"
                 data-widget-url="module=PDFMaker&view=TemplateTools&templateid={$REQUEST_INSTANCE.templateid}&from_view={$REQUEST_INSTANCE.view}&from_templateid={$REQUEST_INSTANCE.templateid}" >
                <span class="pull-left"><img class="imageElement" data-rightimage="{vimage_path('rightArrowWhite.png')}" data-downimage="{vimage_path('downArrowWhite.png')}" src="{vimage_path('rightArrowWhite.png')}" /></span>
                <h5 class="title widgettext-truncate pull-right" title="{vtranslate('Tools', $MODULE)}">{vtranslate('Tools', $MODULE)}</h5>
                <div class="loadingImg hide pull-right">
                    <div class="loadingWidgetMsg"><strong>{vtranslate('LBL_LOADING_WIDGET', $MODULE)}</strong></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="widgetContainer accordion-body collapse" id="{$MODULE}_sideBar_Tools" data-url="module=PDFMaker&view=TemplateTools&templateid={$REQUEST_INSTANCE.templateid}&from_view={$REQUEST_INSTANCE.view}&from_templateid={$REQUEST_INSTANCE.templateid}">
            </div>
        </div>
        {/if}
    </div>
{/strip}