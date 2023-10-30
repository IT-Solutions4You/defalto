{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="row form-group">
        <div class="col-sm-2 col-xs-2">{vtranslate('LBL_METHOD_NAME',$QUALIFIED_MODULE)} :</div>
        <div class="col-sm-6 col-xs-6">
            {assign var=ENTITY_METHODS value=$WORKFLOW_MODEL->getEntityMethods()}
            {if empty($ENTITY_METHODS)}
                <div class="alert alert-info">{vtranslate('LBL_NO_METHOD_IS_AVAILABLE_FOR_THIS_MODULE',$QUALIFIED_MODULE)}</div>
            {else}
                <select name="methodName" class="select2">
                    {foreach from=$ENTITY_METHODS item=METHOD}
                        <option {if $TASK_OBJECT->methodName eq $METHOD}selected="" {/if} value="{$METHOD}">{vtranslate($METHOD,$QUALIFIED_MODULE)}</option>
                    {/foreach}
                </select>
            {/if}
        </div>
    </div>
{/strip}	
