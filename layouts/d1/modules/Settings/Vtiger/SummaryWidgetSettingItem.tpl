{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="summaryWidgetSettingItem border rounded bg-body mb-2"
         data-link-id="{$WIDGET->getId()}"
         data-widget-label="{Vtiger_Util_Helper::toSafeHTML($WIDGET->getLabel())}"
         data-sequence="{$WIDGET->get('sequence')}">
        <div class="row align-items-center g-0">
            <div class="col-auto">
                <button type="button" class="btn text-secondary summaryWidgetDragHandle" title="{vtranslate('LBL_MOVE_SUMMARY_WIDGET', $QUALIFIED_MODULE)}">
                    <i class="fa-solid fa-grip-vertical"></i>
                </button>
            </div>
            <div class="col overflow-hidden">
                <span class="summaryWidgetSettingLabel d-block text-truncate py-2">{vtranslate($WIDGET->getLabel(), $SOURCE_MODULE)}</span>
            </div>
            {if $WIDGET->get('editable')}
                <div class="col-auto">
                    <button type="button" class="btn text-secondary editSummaryWidget" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                        <i class="fa fa-pencil"></i>
                    </button>
                </div>
            {/if}
            <div class="col-auto">
                <button type="button" class="btn text-secondary removeSummaryWidget" title="{vtranslate('LBL_REMOVE', $QUALIFIED_MODULE)}">
                    <i class="fa fa-trash-o"></i>
                </button>
            </div>
        </div>
    </div>
{/strip}
