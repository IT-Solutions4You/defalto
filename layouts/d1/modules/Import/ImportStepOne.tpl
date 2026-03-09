{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="importBlockContainer show" id="uploadFileContainer">
    <div class="container-fluid">
        <div class="row py-2">
            <div class="col">
                {if $FORMAT eq 'vcf'}
                    <h4>{'LBL_IMPORT_FROM_VCF_FILE'|@vtranslate:$MODULE}</h4>
                {elseif $FORMAT eq 'ics'}
                    <h4>{'LBL_IMPORT_FROM_ICS_FILE'|@vtranslate:$MODULE}</h4>
                {else}
                    <h4>{'LBL_IMPORT_FROM_CSV_FILE'|@vtranslate:$MODULE}</h4>
                {/if}
                <hr>
            </div>
        </div>
        <div class="row py-2" id="file_type_container">
            <div class="col-lg-3 text-secondary">
                {if $FORMAT eq 'vcf'}
                    {'LBL_SELECT_VCF_FILE'|@vtranslate:$MODULE}
                {elseif $FORMAT eq 'ics'}
                    {'LBL_SELECT_ICS_FILE'|@vtranslate:$MODULE}
                {else}
                    {'LBL_SELECT_CSV_FILE'|@vtranslate:$MODULE}
                {/if}
            </div>
            <div class="col-lg" data-import-upload-size="{$IMPORT_UPLOAD_SIZE}" data-import-upload-size-mb="{$IMPORT_UPLOAD_SIZE_MB}">
                <div>
                    <input type="hidden" id="type" name="type" value="csv"/>
                    <input type="hidden" name="is_scheduled" value="1"/>
                    <div class="fileUploadBtn btn btn-primary input-file-full-size">
                        <span><i class="fa fa-laptop"></i> {vtranslate('Select from My Computer', $MODULE)}</span>
                        <input type="file" name="import_file" id="import_file" onchange="Vtiger_Import_Js.checkFileType(event)" data-file-formats="{if $FORMAT eq ''}csv{else}{$FORMAT}{/if}"/>
                    </div>
                    <div id="importFileDetails" class="d-inline-block px-3"></div>
                </div>
            </div>
        </div>
        {if $FORMAT eq 'csv'}
            <div class="row py-2" id="has_header_container">
                <div class="col-lg-3 text-secondary">{'LBL_HAS_HEADER'|@vtranslate:$MODULE}</div>
                <div class="col-lg">
                    <div class="form-check">
                        <input type="checkbox" id="has_header" name="has_header" checked class="form-check-input"/>
                    </div>
                </div>
            </div>
        {/if}
        {if $FORMAT neq 'ics'}
            <div class="row py-2" id="file_encoding_container">
                <div class="col-lg-3 text-secondary">{'LBL_CHARACTER_ENCODING'|@vtranslate:$MODULE}</div>
                <div class="col-lg">
                    <select name="file_encoding" id="file_encoding" class="select2">
                        {foreach key=_FILE_ENCODING item=_FILE_ENCODING_LABEL from=$SUPPORTED_FILE_ENCODING}
                            <option value="{$_FILE_ENCODING}">{$_FILE_ENCODING_LABEL|@vtranslate:$MODULE}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/if}
        {if $FORMAT eq 'csv'}
            <div class="row py-2" id="delimiter_container">
                <div class="col-lg-3 text-secondary">{'LBL_DELIMITER'|@vtranslate:$MODULE}</div>
                <div class="col-lg">
                    {foreach key=_DELIMITER item=_DELIMITER_LABEL from=$SUPPORTED_DELIMITERS name=delimiters}
                        <label class="form-check py-2">
                            <input class="form-check-input" type="radio" name="delimiter" value="{$_DELIMITER}" {if $smarty.foreach.delimiters.index eq 0} checked="true" {/if}>
                            <span class="form-check-label">{$_DELIMITER_LABEL|@vtranslate:$MODULE}</span>
                        </label>
                    {/foreach}
                </div>
            </div>
            {if isset($MULTI_CURRENCY) && $MULTI_CURRENCY}
                <div class="row py-2" id="lineitem_currency_container">
                    <div class="col-lg-3 text-secondary">{vtranslate('LBL_IMPORT_LINEITEMS_CURRENCY',$MODULE)}</div>
                    <div class="col-lg">
                        <select name="lineitem_currency" id="lineitem_currency" class="select2">
                            {$i = 0}
                            {foreach key=id item=CURRENCY from=$CURRENCIES}
                                <option value="{$CURRENCY['currency_id']}">{$CURRENCY['currencycode']}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/if}
        {/if}
    </div>
</div>
