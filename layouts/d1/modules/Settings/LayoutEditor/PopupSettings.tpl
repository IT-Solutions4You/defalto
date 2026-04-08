{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="popupSettingsDiv padding20">
        <form id="popupSettingsForm" method="POST">
            <input type="hidden" name="module" value="LayoutEditor" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" name="action" value="SavePopupSettings" />
            <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />

            <div class="fieldsBlock">
                <div class="py-3">
                    <b>{vtranslate('LBL_POPUP_VISIBLE_COLUMNS', $QUALIFIED_MODULE)}</b>
                </div>
                <select class="select2 form-control" id="popupColumns" multiple name="popupColumns[]">
                    {foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
                        <option value="{$FIELD_NAME}" {if in_array($FIELD_NAME, $SELECTED_SETTINGS.columnslist)}selected{/if}>
                            {vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
                        </option>
                    {/foreach}
                </select>
                <input type="hidden" name="columnslist" value='{Vtiger_Functions::jsonEncode($SELECTED_SETTINGS.columnslist)}' />
            </div>

            <div class="py-3">
                <button type="button" class="btn btn-primary active savePopupSettingsBtn">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
            </div>
        </form>
    </div>
{/strip}
