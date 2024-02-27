{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Picklist/views/IndexAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {if !empty($NO_PICKLIST_FIELDS)}
        <label>
            <b>
                {vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)} {vtranslate('NO_PICKLIST_FIELDS',$QUALIFIED_NAME)}.
                {if !empty($CREATE_PICKLIST_URL)}
                    <a class="ms-2" href="{$CREATE_PICKLIST_URL}">{vtranslate('LBL_CREATE_NEW',$QUALIFIED_NAME)}</a>
                {/if}
            </b>
        </label>
    {else}
        <div class="container-fluid px-3">
            <div class="row form-group">
                <div class="col-sm-3 control-label fieldLabel pb-3">
                    <label>
                        <strong>{vtranslate('LBL_SELECT_PICKLIST_IN',$QUALIFIED_MODULE)}&nbsp;{vtranslate($SELECTED_MODULE_NAME,$QUALIFIED_MODULE)}</strong>
                    </label>
                </div>
                <div class="fieldValue col-sm-6 pb-3">
                    <select class="select2 inputElement form-select" id="modulePickList" name="modulePickList">
                        {foreach key=PICKLIST_FIELD item=FIELD_MODEL from=$PICKLIST_FIELDS}
                            <option value="{$FIELD_MODEL->getId()}" {if $DEFAULT_FIELD eq $FIELD_MODEL->getName()}selected{/if}>{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    {/if}
{/strip}
