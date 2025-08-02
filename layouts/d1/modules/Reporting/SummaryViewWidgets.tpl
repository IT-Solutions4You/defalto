{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="col-lg-12">
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
            {include file=$DETAIL_VIEW_WIDGET->getWidgetTemplate()|vtemplate_path:$MODULE_NAME}
        {/foreach}
    </div>
{/strip}