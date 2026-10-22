{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-lg conversionMappingModal">
        <div class="modal-content">
            <form id="conversionMappingForm" method="post" action="javascript:void(0);" data-compatible-targets="{Vtiger_Util_Helper::toSafeHTML($COMPATIBLE_TARGETS)}">
                <input type="hidden" name="module" value="InventoryItem">
                <input type="hidden" name="parent" value="Settings">
                <input type="hidden" name="action" value="ConversionMappingSave">
                <input type="hidden" name="conversion" value="{$CONVERSION_ID}">
                {include file="ModalHeader.tpl"|vtemplate_path:$QUALIFIED_MODULE TITLE=vtranslate('LBL_CONVERSION_MAPPING_TITLE', $QUALIFIED_MODULE, $SOURCE_LABEL, $TARGET_LABEL)}
                <div class="modal-body">
                    <p class="text-secondary">{vtranslate('LBL_CONVERSION_MAPPING_DESCRIPTION', $QUALIFIED_MODULE)}</p>
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th style="width: 45%">{vtranslate('LBL_CONVERSION_MAPPING_SOURCE', $QUALIFIED_MODULE, $SOURCE_LABEL)}</th>
                                <th></th>
                                <th style="width: 45%">{vtranslate('LBL_CONVERSION_MAPPING_TARGET', $QUALIFIED_MODULE, $TARGET_LABEL)}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="conversionMappingRows">
                            {foreach key=TARGET_FIELD item=SOURCE_FIELD from=$MAPPING}
                                {include file="ConversionMappingRow.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                            {/foreach}
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-outline-secondary btn-sm addConversionMappingRow"><i class="fa fa-plus"></i> {vtranslate('LBL_CONVERSION_MAPPING_ADD', $QUALIFIED_MODULE)}</button>
                    <table class="d-none">
                        <tbody class="conversionMappingRowTemplate">
                            {assign var=TARGET_FIELD value=''}
                            {assign var=SOURCE_FIELD value=''}
                            {include file="ConversionMappingRow.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6"><a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a></div>
                            <div class="col-6 text-end"><button type="submit" class="btn btn-primary active saveButton"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}
