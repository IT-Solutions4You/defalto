{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="col-lg-12">
        {foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
            {include file=$DETAIL_VIEW_WIDGET->getWidgetTemplate()|vtemplate_path:$MODULE_NAME}
        {/foreach}
    </div>
{/strip}