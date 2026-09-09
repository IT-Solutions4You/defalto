{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="summaryWidgetContainer bg-body rounded mb-3">
    <div class="widgetContainer_{$DETAIL_VIEW_WIDGET->getId()}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}" data-sequence="{$DETAIL_VIEW_WIDGET->get('sequence')}">
        <div class="widget_contents p-3">
        </div>
    </div>
</div>
{/strip}
