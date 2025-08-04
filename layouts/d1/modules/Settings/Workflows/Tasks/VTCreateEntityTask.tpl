{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="row py-2">
        <div class="col-lg-2">
            <span>{vtranslate('LBL_MODULES_TO_CREATE_RECORD',$QUALIFIED_MODULE)}</span>
            <span class="text-danger ms-2">*</span>
        </div>
        <div class="col-lg-6">
            {assign var=RELATED_MODULES_INFO value=$WORKFLOW_MODEL->getDependentModules()}
            {assign var=RELATED_MODULES value=$RELATED_MODULES_INFO|array_keys}
            {assign var=RELATED_MODULE_MODEL_NAME value=$TASK_OBJECT->entity_type}
            <select class="select2" id="createEntityModule" name="entity_type" data-rule-required="true" style="min-width: 150px;">
                <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                {foreach from=$RELATED_MODULES item=MODULE}
                    <option {if $TASK_OBJECT->entity_type eq $MODULE} selected="" {/if} value="{$MODULE}">{vtranslate($MODULE,$MODULE)}</option>
                {/foreach}
            </select>
        </div>
    </div>
	<div id="addCreateEntityContainer">
		{include file="CreateEntity.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}
