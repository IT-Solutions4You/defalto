{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getAllSkins()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
<select class="inputElement select2" name="{$FIELD_MODEL->getFieldName()}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
        {foreach item=VALIDATOR from=$FIELD_INFO["validator"]}
            {assign var=VALIDATOR_NAME value=$VALIDATOR["name"]}
            data-rule-{$VALIDATOR_NAME} = "true" 
        {/foreach}
        >
    {foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
	<option value="{$PICKLIST_NAME}" style='background-color:{$PICKLIST_VALUE}; margin:5px; color:white;'
		{if $FIELD_MODEL->get('fieldvalue') eq $PICKLIST_NAME} selected {/if}>{ucfirst($PICKLIST_NAME)}</option>
{/foreach}
</select>
{/strip}