{*<!--
/*********************************************************************************
* The content of this file is subject to the EMAIL Maker license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
<div id="listview-actions" class="container-fluid listview-actions-container">
    <div class="row">
        <div class="col px-3">
            <div class="btn-group listViewMassActions" role="group">
                {if count($LISTVIEW_MASSACTIONS) gt 0 || $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">{vtranslate('LBL_ACTIONS', $MODULE)}&nbsp;&nbsp;<i class="caret"></i></button>
                        <ul class="dropdown-menu">
                            {if count($LISTVIEW_MASSACTIONS) gt 0}
                                {foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_MASSACTIONS}
                                    <li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}">
                                        <a class="dropdown-item" href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")}'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{vtranslate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a>
                                    </li>
                                {/foreach}
                                {if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                {/if}
                            {/if}
                            {if $LISTVIEW_LINKS['LISTVIEW']|@count gt 0}
                                {foreach item=LISTVIEW_ADVANCEDACTIONS from=$LISTVIEW_LINKS['LISTVIEW']}
                                    <li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}">
                                        <a class="dropdown-item" {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")}'{else} href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}' {/if}>{vtranslate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a>
                                    </li>
                                {/foreach}
                            {/if}
                        </ul>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>