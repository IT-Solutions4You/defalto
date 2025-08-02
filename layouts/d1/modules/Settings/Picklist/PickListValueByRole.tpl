{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Picklist/views/IndexAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div>
        <div class="form-group row my-3">
            <div class="control-label col-lg-3">&nbsp;</div>
            <div class="controls col-lg-6">
                <select class="select2 form-control" id="role2picklist" multiple name="role2picklist[]">
                    {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$ALL_PICKLIST_VALUES}
                        <option value="{$PICKLIST_VALUE}" data-id="{$PICKLIST_KEY}" {if in_array($PICKLIST_VALUE,$ROLE_PICKLIST_VALUES)} selected {/if}>
                            {vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group row my-3">
            <div class="control-label col-lg-3">&nbsp;</div>
            <div class="controls col-lg-6 text-end">
                <button id="saveOrder" class="btn btn-primary active">
                    {vtranslate('LBL_SAVE',$QUALIFIED_MODULE)}
                </button>
            </div>
        </div>
    </div>
{/strip}