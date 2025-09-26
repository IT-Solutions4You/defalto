{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-footer">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-auto col-lg-6">
                    <a href="#" class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
                <div class="col-auto col-lg-6 text-end ms-auto">
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