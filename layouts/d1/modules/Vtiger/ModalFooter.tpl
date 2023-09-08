{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
    <div class="modal-footer align-content-between">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6 text-start">
                    <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                <div class="col-6 text-end">
                    {if $BUTTON_NAME neq null}
                        {assign var=BUTTON_LABEL value=$BUTTON_NAME}
                    {else}
                        {assign var=BUTTON_LABEL value={vtranslate('LBL_SAVE', $MODULE)}}
                    {/if}
                    <button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-primary active" type="submit" name="saveButton">
                        <strong>{$BUTTON_LABEL}</strong>
                    </button>
                </div>
            </div>
        </div>
    </div>
{/strip}