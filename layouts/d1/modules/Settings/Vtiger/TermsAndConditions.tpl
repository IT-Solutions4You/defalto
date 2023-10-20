{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div class="px-4 pb-4 editViewContainer" id="TermsAndConditionsContainer">
        <div class="rounded bg-body">
            <div class="block">
                <div class="p-3 border-bottom">
                    <h4>{vtranslate('LBL_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}</h4>
                </div>
                <div class="container-fluid">
                    <div class="contents form-group row py-3">
                        <div class="col-lg-4 control-label fieldLabel">
                            <label>{vtranslate('LBL_SELECT_MODULE', 'Vtiger')}</label>
                        </div>
                        <div class="fieldValue col-lg-8">
                            <select class="select2-container select2 inputElement col-sm-6 selectModule">
                                {foreach item=MODULE_NAME from=$INVENTORY_MODULES}
                                    <option value={$MODULE_NAME}>{vtranslate({$MODULE_NAME}, {$MODULE_NAME})}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row py-3">
                        <div class="col-lg-12">
                            <textarea class=" TCContent form-control" rows="10" placeholder="{vtranslate('LBL_SPECIFY_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}" style="width:100%;" >{$CONDITION_TEXT}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-overlay-footer modal-footer py-3">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col"></div>
                        <div class="col">
                            <button type="submit" class="btn btn-primary active saveButton saveTC hide" type="submit" >{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}

