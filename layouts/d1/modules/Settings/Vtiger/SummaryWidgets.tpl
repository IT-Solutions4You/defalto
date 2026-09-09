{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="px-4 pb-4"
         id="summaryWidgetsSettings"
         data-source-module="{Vtiger_Util_Helper::toSafeHTML($SOURCE_MODULE)}"
         data-remove-confirmation="{vtranslate('LBL_SUMMARY_WIDGET_REMOVE_CONFIRMATION', $QUALIFIED_MODULE)}"
         data-move-label="{vtranslate('LBL_MOVE_SUMMARY_WIDGET', $QUALIFIED_MODULE)}"
         data-edit-label="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"
         data-remove-label="{vtranslate('LBL_REMOVE', $QUALIFIED_MODULE)}">
        <div class="detailViewContainer bg-body rounded">
            <div class="container-fluid px-3 pt-3 border-bottom">
                <div class="row align-items-center">
                    <div class="col-lg pb-3">
                        <h4 class="m-0">{vtranslate('LBL_SUMMARY_WIDGET_EDITOR', $QUALIFIED_MODULE)}</h4>
                    </div>
                    <div class="col-lg-4 pb-3">
                        <label class="visually-hidden" for="summaryWidgetsSourceModule">{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
                        <select class="select2 inputElement form-select" id="summaryWidgetsSourceModule" name="sourceModule">
                            {foreach item=MODULE_MODEL from=$SUPPORTED_MODULES}
                                {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                                <option value="{Vtiger_Util_Helper::toSafeHTML($MODULE_NAME)}"{if $MODULE_NAME eq $SOURCE_MODULE} selected{/if}>
                                    {vtranslate($MODULE_NAME, $MODULE_NAME)}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg-auto pb-3">
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle"
                                    type="button"
                                    id="summaryWidgetAddDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fa fa-plus me-2" aria-hidden="true"></i>
                                {vtranslate('LBL_ADD_SUMMARY_WIDGET', $QUALIFIED_MODULE)}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end summaryWidgetAddMenu"
                                aria-labelledby="summaryWidgetAddDropdown">
                                <li><h6 class="dropdown-header">{vtranslate('LBL_AVAILABLE_SUMMARY_WIDGETS', $QUALIFIED_MODULE)}</h6></li>
                                {foreach key=WIDGET_LABEL item=DEFINITION from=$AVAILABLE_WIDGETS}
                                    <li class="summaryWidgetAvailableItem">
                                        <button type="button"
                                                class="dropdown-item addSummaryWidget"
                                                data-widget-label="{Vtiger_Util_Helper::toSafeHTML($WIDGET_LABEL)}">
                                            <i class="fa-solid fa-puzzle-piece me-2 text-secondary" aria-hidden="true"></i>
                                            <span>{vtranslate($WIDGET_LABEL, $SOURCE_MODULE)}</span>
                                        </button>
                                    </li>
                                {/foreach}
                                <li class="summaryWidgetNoAvailableItem{if !empty($AVAILABLE_WIDGETS)} d-none{/if}">
                                    <span class="dropdown-item-text text-secondary">{vtranslate('LBL_NO_AVAILABLE_SUMMARY_WIDGETS', $QUALIFIED_MODULE)}</span>
                                </li>
                                <li class="summaryWidgetAvailableDivider"><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item createSummaryListWidget">
                                        <i class="fa-solid fa-table-list me-2 text-primary" aria-hidden="true"></i>
                                        <span>{vtranslate('LBL_CREATE_SUMMARY_LIST_WIDGET', $QUALIFIED_MODULE)}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid p-3">
                <p class="text-secondary mb-3">{vtranslate('LBL_SUMMARY_WIDGETS_DESCRIPTION', $QUALIFIED_MODULE)}</p>
                <div class="row">
                    <div class="col-xl-5 mb-3 mb-xl-0">
                        <h5>{vtranslate('LBL_LEFT_COLUMN', $QUALIFIED_MODULE)}</h5>
                        <div class="summaryWidgetsColumn border rounded p-2" data-column="left">
                            {foreach item=WIDGET from=$LEFT_WIDGETS}
                                {include file='SummaryWidgetSettingItem.tpl'|vtemplate_path:$QUALIFIED_MODULE WIDGET=$WIDGET}
                            {/foreach}
                            <div class="summaryWidgetsEmpty text-secondary text-center p-4{if !empty($LEFT_WIDGETS)} d-none{/if}">
                                {vtranslate('LBL_DROP_SUMMARY_WIDGET_HERE', $QUALIFIED_MODULE)}
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-7">
                        <h5>{vtranslate('LBL_RIGHT_COLUMN', $QUALIFIED_MODULE)}</h5>
                        <div class="summaryWidgetsColumn border rounded p-2" data-column="right">
                            {foreach item=WIDGET from=$RIGHT_WIDGETS}
                                {include file='SummaryWidgetSettingItem.tpl'|vtemplate_path:$QUALIFIED_MODULE WIDGET=$WIDGET}
                            {/foreach}
                            <div class="summaryWidgetsEmpty text-secondary text-center p-4{if !empty($RIGHT_WIDGETS)} d-none{/if}">
                                {vtranslate('LBL_DROP_SUMMARY_WIDGET_HERE', $QUALIFIED_MODULE)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}
