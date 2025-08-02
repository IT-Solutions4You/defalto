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
                    <div class="row py-2 selectModulesContainer hide">
                        <div class="col-lg-3">
                            {vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <select class="selectModules form-control" disabled="">
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
                </div>
            </div>
            {include file='ModalFooter.tpl'|vtemplate_path:$QUALIFIED_MODULE BUTTON_ID='selectFieldsButton' BUTTON_NAME=vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}
        </div>
    </div>
</div>
{/strip}