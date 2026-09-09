{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if $WIDGET_CONFIGURATION.linkId}
        {assign var=MODAL_TITLE value='LBL_EDIT_SUMMARY_LIST_WIDGET'}
    {else}
        {assign var=MODAL_TITLE value='LBL_CREATE_SUMMARY_LIST_WIDGET'}
    {/if}
    <div class="modal-dialog modal-lg summaryListWidgetModal">
        <div class="modal-content">
            {include file='ModalHeader.tpl'|vtemplate_path:'Vtiger' TITLE={vtranslate($MODAL_TITLE, $QUALIFIED_MODULE)}}
            <form id="summaryListWidgetForm" method="POST">
                <input type="hidden" name="sourceModule" value="{Vtiger_Util_Helper::toSafeHTML($SOURCE_MODULE)}">
                <input type="hidden" name="linkId" value="{$WIDGET_CONFIGURATION.linkId}">
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetTitle">
                                {vtranslate('LBL_WIDGET_TITLE', $QUALIFIED_MODULE)} <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control" id="summaryWidgetTitle" name="widgetTitle"
                                       value="{Vtiger_Util_Helper::toSafeHTML($WIDGET_CONFIGURATION.title)}" maxlength="50" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetTargetModule">
                                {vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)} <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select class="select2 form-select" id="summaryWidgetTargetModule" name="targetModule" required>
                                    {foreach item=MODULE_MODEL from=$TARGET_MODULES}
                                        {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                                        <option value="{Vtiger_Util_Helper::toSafeHTML($MODULE_NAME)}"{if $MODULE_NAME eq $WIDGET_CONFIGURATION.targetModule} selected{/if}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetFilter">
                                {vtranslate('LBL_LIST_FILTER', $QUALIFIED_MODULE)} <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select class="select2 form-select" id="summaryWidgetFilter" name="filterId"
                                        data-initial-value="{$WIDGET_CONFIGURATION.filterId}" required></select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetFields">
                                {vtranslate('LBL_DISPLAY_FIELDS', $QUALIFIED_MODULE)} <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select class="select2 form-select" id="summaryWidgetFields" name="fields[]"
                                        data-initial-value="{Vtiger_Util_Helper::toSafeHTML($WIDGET_FIELDS)}"
                                        data-maximum-selection-size="3" multiple required></select>
                                <input type="hidden" id="summaryWidgetFieldsOrder"
                                       value="{Vtiger_Util_Helper::toSafeHTML($WIDGET_FIELDS_ORDER)}">
                                <div class="form-text">{vtranslate('LBL_SORT_SELECTED_FIELDS', $QUALIFIED_MODULE)}</div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetRelationType">
                                {vtranslate('LBL_RELATION_TYPE', $QUALIFIED_MODULE)} <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-select" id="summaryWidgetRelationType" name="relationType" required>
                                    <option value="reference"{if $WIDGET_CONFIGURATION.relationType eq 'reference'} selected{/if}>{vtranslate('LBL_REFERENCE_FIELD_RELATION', $QUALIFIED_MODULE)}</option>
                                    <option value="related_list"{if $WIDGET_CONFIGURATION.relationType eq 'related_list'} selected{/if}>{vtranslate('LBL_RELATED_LIST_RELATION', $QUALIFIED_MODULE)}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3 summaryWidgetReferenceRow">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetReferenceField">{vtranslate('LBL_REFERENCE_FIELD', $QUALIFIED_MODULE)}</label>
                            <div class="col-sm-8">
                                <select class="select2 form-select" id="summaryWidgetReferenceField" name="referenceField"
                                        data-initial-value="{Vtiger_Util_Helper::toSafeHTML($WIDGET_CONFIGURATION.referenceField)}"></select>
                            </div>
                        </div>
                        <div class="row mb-3 summaryWidgetRelatedListRow d-none">
                            <label class="col-sm-4 col-form-label" for="summaryWidgetRelation">{vtranslate('LBL_RELATED_LIST', $QUALIFIED_MODULE)}</label>
                            <div class="col-sm-8">
                                <select class="select2 form-select" id="summaryWidgetRelation" name="relationId"
                                        data-initial-value="{$WIDGET_CONFIGURATION.relationId}"></select>
                            </div>
                        </div>
                        <div class="alert alert-warning d-none summaryWidgetNoRelation">{vtranslate('LBL_NO_AVAILABLE_RELATION', $QUALIFIED_MODULE)}</div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}
