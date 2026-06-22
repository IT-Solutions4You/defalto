{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="headerFieldsDiv padding20 py-3">
        <div class="row">
            <div class="col-sm-12">
                <div class="containerSelectFields">
                    <div class="containerFields" data-field="" data-label="">
                        {if $PRIMARY_MODULE}
                            {assign var=FIELD_OPTIONS value=$HEADER_FIELDS_MODEL->getFieldOptions($PRIMARY_MODULE)}
                            {assign var=LABEL_OPTIONS value=$HEADER_FIELDS_MODEL->getLabelOptions($PRIMARY_MODULE, [])}
                            <div class="labelFields visually-hidden">{json_encode($LABEL_OPTIONS)}</div>
                            <div class="fieldOptions visually-hidden">{json_encode($FIELD_OPTIONS)}</div>

                            <select class="select2 form-control headerFieldsSelect" id="headerFieldsSelect" multiple name="header_fields[]">
                                {foreach key=GROUP_NAME item=GROUP_FIELDS from=$FIELD_OPTIONS}
                                    <optgroup label="{if $GROUP_NAME neq 'default'}{vtranslate($GROUP_NAME, $SOURCE_MODULE)}{/if}">
                                        {foreach key=FIELD_NAME item=FIELD_LABEL from=$GROUP_FIELDS}
                                            {assign var=FIELD_LABEL_INFO value='##'|explode:$FIELD_LABEL}
                                            <option value="{$FIELD_NAME}" {if in_array($FIELD_NAME, $SELECTED_FIELD_NAMES)}selected{/if}>
                                                {$FIELD_LABEL_INFO[1]}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                            <input type="hidden" name="header_fields_order" value='{Vtiger_Functions::jsonEncode($SELECTED_FIELD_NAMES)}' />

                            <div class="py-3">
                                <button type="button" class="btn btn-primary active saveHeaderFieldsBtn">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                            </div>
                        {/if}

                    </div>
                </div>

            </div>
        </div>
    </div>
{/strip}
