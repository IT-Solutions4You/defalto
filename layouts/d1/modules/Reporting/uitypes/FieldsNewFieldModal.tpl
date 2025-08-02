{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="modal-dialog modal-lg modelContainer fieldsNewFieldModal">
    <div class="modal-content">
        <div class="modal-content form-horizontal">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}
            <div class="modal-body">

                <input type="hidden" class="selectedFieldName">
                <div class="container-fluid">
                    <div class="row py-2 selectModulesContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <select class="selectModules form-control">
                                {foreach from=$MODULE_OPTIONS item=OPTION_LABEL key=OPTION_VALUE}
                                    <option value="{$OPTION_VALUE}" >{$OPTION_LABEL}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row py-2 selectFieldsContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <select class="selectFields form-control"></select>
                        </div>
                    </div>
                    <div class="row py-2 selectFieldNameContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_FIELD_NAME', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <input class="selectedFieldName form-control" disabled readonly>
                        </div>
                    </div>
                    <div class="row py-2 selectFieldLabelContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <input type="text" class="selectedFieldLabel form-control">
                        </div>
                    </div>
                    <div class="row py-2 selectSortByContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_SORT_BY', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <select class="selectedSortBy form-control">
                                <option value="">{vtranslate('LBL_SELECT_SORTING', $QUALIFIED_MODULE)}</option>
                                <option value="ASC">{vtranslate('LBL_ASC', $QUALIFIED_MODULE)}</option>
                                <option value="DESC">{vtranslate('LBL_DESC', $QUALIFIED_MODULE)}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row py-2 selectWidthContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <input class="selectedWidth form-control" type="text">
                        </div>
                    </div>
                    <div class="row py-2 selectWidthContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_ALIGN', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary selectedAlign" data-align="left">
                                    <i class="bi bi-justify-left"></i>
                                </button>
                                <button class="btn btn-outline-secondary selectedAlign" data-align="center">
                                    <i class="bi bi-text-center"></i>
                                </button>
                                <button class="btn btn-outline-secondary selectedAlign" data-align="right">
                                    <i class="bi bi-justify-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|vtemplate_path:$QUALIFIED_MODULE BUTTON_ID='selectFieldsButton' BUTTON_NAME=vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}
        </div>
    </div>
</div>
{/strip}