{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="modal-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6 text-end">
                    <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                <div class="col-6">
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