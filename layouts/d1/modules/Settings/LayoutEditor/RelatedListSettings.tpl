{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="relatedListSettingsDiv py-3">
        <form id="relatedListSettingsForm" method="POST">
            <input type="hidden" name="module" value="LayoutEditor" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" name="action" value="SaveRelatedListSettings" />
            <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />

            <div class="detailViewInfo container-fluid px-3">
                <div class="row form-group align-items-center mb-3">
                    <div class="col-sm-3 control-label fieldLabel">
                        <label class="fieldLabel">{vtranslate('LBL_RELATED_LIST_VISIBLE_COLUMNS', $QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="fieldValue col-sm-6">
                        <select class="select2 form-control" id="relatedListColumns" multiple name="relatedListColumns[]">
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
                                <option value="{$FIELD_NAME}" {if in_array($FIELD_NAME, $SELECTED_SETTINGS.columnslist)}selected{/if}>
                                    {vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
                                </option>
                            {/foreach}
                        </select>
                        <input type="hidden" name="columnslist" value='{Vtiger_Functions::jsonEncode($SELECTED_SETTINGS.columnslist)}' />
                    </div>
                </div>

                <div class="row form-group align-items-center mb-3">
                    <div class="col-sm-3 control-label fieldLabel">
                        <label class="fieldLabel">{vtranslate('LBL_RELATED_LIST_SORT_FIELD', $QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="fieldValue col-sm-6">
                        <select class="select2 form-control" id="relatedListSortField" name="sortfield">
                            <option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
                                <option value="{$FIELD_NAME}" {if $SELECTED_SETTINGS.sortfield eq $FIELD_NAME}selected{/if}>
                                    {vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="row form-group align-items-center mb-3">
                    <div class="col-sm-3 control-label fieldLabel">
                        <label class="fieldLabel">{vtranslate('LBL_RELATED_LIST_SORT_ORDER', $QUALIFIED_MODULE)}</label>
                    </div>
                    <div class="fieldValue col-sm-6">
                        <select class="select2 form-control" id="relatedListSortOrder" name="sortorder">
                            <option value="ASC" {if $SELECTED_SETTINGS.sortorder eq 'ASC'}selected{/if}>{vtranslate('LBL_ASCENDING', $QUALIFIED_MODULE)}</option>
                            <option value="DESC" {if $SELECTED_SETTINGS.sortorder eq 'DESC'}selected{/if}>{vtranslate('LBL_DESCENDING', $QUALIFIED_MODULE)}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="container-fluid py-3">
                <div class="row">
                    <div class="col">
                        <button type="button" class="btn btn-primary active saveRelatedListSettingsBtn">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
{/strip}
