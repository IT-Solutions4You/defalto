{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="left-block col-xl-5">
        {* Module Summary View*}
        <div class="summaryView bg-body rounded mb-3">
            <div class="summaryViewHeader p-3 border-1 border-bottom">
                <h4 class="display-inline-block">{vtranslate('LBL_KEY_FIELDS', $MODULE_NAME)}</h4>
            </div>
            <div class="summaryViewFields p-3">
                {$MODULE_SUMMARY}
            </div>
        </div>
        {* Module Summary View Ends Here*}
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
            {if 'Placeholder' eq $DETAIL_VIEW_WIDGET->getLabel()}
                {continue}
            {/if}
            {if $smarty.foreach.count.index % 2 == 0}
                {include file='SummaryViewWidget.tpl'|vtemplate_path:$MODULE_NAME}
            {/if}
        {/foreach}
    </div>

    <div class="right-block col-xl-7">
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
            {if 'Placeholder' eq $DETAIL_VIEW_WIDGET->getLabel()}
                {continue}
            {/if}
            {if $smarty.foreach.count.index % 2 != 0}
                {include file='SummaryViewWidget.tpl'|vtemplate_path:$MODULE_NAME}
            {/if}
        {/foreach}
    </div>
{/strip}