{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="px-4 pb-4 editViewContainer" id="TermsAndConditionsContainer">
        <div class="rounded bg-body">
            <div class="p-3 border-bottom">
                <h4>{vtranslate('LBL_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}</h4>
            </div>
            <div class="block">
                <div class="container-fluid py-3">
                    <div class="contents form-group row py-2">
                        <div class="col-lg-3 control-label fieldLabel">
                            <label>{vtranslate('LBL_SELECT_MODULE', 'Vtiger')}</label>
                        </div>
                        <div class="fieldValue col-lg-6">
                            <select class="select2-container select2 inputElement col-sm-6 selectModule">
                                {foreach item=MODULE_NAME from=$INVENTORY_MODULES}
                                    <option value={$MODULE_NAME}>{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-6">
                            <textarea class=" TCContent form-control" rows="10" placeholder="{vtranslate('LBL_SPECIFY_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}" style="width:100%;" >{$CONDITION_TEXT}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-overlay-footer modal-footer py-3 border-top">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary active saveButton saveTC" type="submit" >{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}

