{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="left-block col-xl-5">
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
            {if $smarty.foreach.count.index % 2 == 0}
                {include file=$DETAIL_VIEW_WIDGET->getWidgetTemplate()|vtemplate_path:$MODULE_NAME}
            {/if}
        {/foreach}
    </div>

    <div class="right-block col-xl-7">
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
            {if $smarty.foreach.count.index % 2 != 0}
                {include file=$DETAIL_VIEW_WIDGET->getWidgetTemplate()|vtemplate_path:$MODULE_NAME}
            {/if}
        {/foreach}
    </div>
{/strip}