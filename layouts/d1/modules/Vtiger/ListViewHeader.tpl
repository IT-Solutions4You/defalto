{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="containerListViewHeader module-action-bar clearfix">
        <div class="module-action-content clearfix row">
        <span class="col-lg-4 col-md-4 col-sm-4">
            <div id="appnav" class="navbar-right">
                <ul class="nav navbar-nav">
                    {foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
                        <li>
                            <button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" type="button" class="btn addButton btn-default module-buttons" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}  onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}>
                                <i class="fa fa-plus"></i>
                                <span class="ms-2">{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</span>
                            </button>
                        </li>
                    {/foreach}
                    <li>
                    {if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
                        <div class="settingsIcon">
                            <button type="button" class="btn btn-default module-buttons dropdown-toggle" data-bs-toggle="dropdown">
                                <span class="fa fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>
                            </button>
                            <ul class="listViewSetting dropdown-menu">
                                {foreach item=LISTVIEW_SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
                                    {if $LISTVIEW_SETTING->get('isActionLink')}
                                        <li>
                                            <a href="javascript:void(0)" id="{$LISTVIEW_SETTING->getLabel()}" class="{$LISTVIEW_SETTING->get('linkclass')}" data-url="{$LISTVIEW_SETTING->getUrl()}">{vtranslate($LISTVIEW_SETTING->getLabel(),$MODULE)}</a>
                                        </li>
                                    {else}
                                        <li>
                                            <a href={$LISTVIEW_SETTING->getUrl()}>{vtranslate($LISTVIEW_SETTING->getLabel(), $MODULE)}</a>
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    </li>
                </ul>
            </div>
        </span>
        </div>
    </div>
{/strip}