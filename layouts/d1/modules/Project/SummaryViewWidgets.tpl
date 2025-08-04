{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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