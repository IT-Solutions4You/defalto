{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="summaryWidgetContainer bg-body rounded mb-3">
    <div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
        <div class="widget_header border-1 border-bottom p-3 clearfix">
            <input type="hidden" name="relatedModule" value="{$DETAIL_VIEW_WIDGET->get('linkName')}"/>
            <h4 class="display-inline-block pull-left">{vtranslate('InventoryItem', 'InventoryItem')}</h4>
            <div class="pull-right">
                <button type="button" class="btn btn-sm text-secondary fw-bold" onclick="Vtiger_Detail_Js.openDetail(this);" data-detail-url="{$RECORD->getFullDetailViewUrl()}">
                    <i class="fa-solid fa-circle-info"></i>
                    <span class="ms-2">{vtranslate('LBL_DETAILS', $QUALIFIED_MODULE)}</span>
                </button>
            </div>
        </div>
        <div class="widget_filter">
        </div>
        <div class="widget_contents p-3">
        </div>
    </div>
</div>