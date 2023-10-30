{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Picklist/views/IndexAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="container-fluid">
        <div class="form-group row my-3">
            <div class="control-label col-lg-2">&nbsp;</div>
            <div class="controls col-lg-8">
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
            <div class="control-label col-lg-2">&nbsp;</div>
            <div class="controls col-lg-8">
                <button id="saveOrder" class="btn btn-success pull-right">
                    {vtranslate('LBL_SAVE',$QUALIFIED_MODULE)}
                </button>
            </div>
        </div>
    </div>
{/strip}