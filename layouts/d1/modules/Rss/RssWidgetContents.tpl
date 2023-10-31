{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Rss/views/ViewTypes.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="sidebar-menu quickWidgetContainer">
    {assign var=val value=1}
    <div class="module-filters">
        {foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
            <div class="sidebar-container lists-menu-container">
                <div class="sidebar-header d-flex">
                    <h5>{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
                    <button class="btn btn-primary ms-auto rssAddButton" title="{vtranslate('LBL_FEED_SOURCE',$MODULE)}">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        <span class="ms-2">{vtranslate('LBL_FEED_SOURCE',$MODULE)}</span>
                    </button>
                </div>
                <hr>
                <div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
                    <div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
                        <div class="mCSB_container" style="position:relative; top:0; left:0;">
                            <div class="list-menu-content">
                                <ul class="widgetContainer nav nav-pills flex-column" data-url="{$SIDEBARWIDGET->getUrl()}">
                                    {assign var="RSS_MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
                                    {assign  var="RSS_SOURCES" value=$RSS_MODULE_MODEL->getRssSources()}
                                    {foreach item=recordsModel from=$RSS_SOURCES}
                                        <li class="tab-item nav-link fs-6">
                                            <a href="#" class="rssLink " data-id={$recordsModel->getId()} data-url="{$recordsModel->get('rssurl')}" title="{decode_html($recordsModel->getName())}">{decode_html($recordsModel->getName())}</a>
                                        </li>
                                    {foreachelse}
                                        <li class="text-center noRssFeeds">{vtranslate('LBL_NO_RECORDS', $MODULE)}</li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
        <div class="rssAddFormContainer hide"></div>
    </div>
</div>

