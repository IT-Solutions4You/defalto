{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{foreach key=FIELD_NAME item=FIELD_MODEL from=$PROVIDER_MODEL}
		<div class="form-group row py-2">
			{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
			<div class="col-lg-4">
				<label for="{$FIELD_NAME}">{vtranslate($FIELD_MODEL->get('label') , $QUALIFIED_MODULE_NAME)}</label>
			</div>
			<div class="col-lg-6">
				{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
				{assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}
				{if $FIELD_TYPE == 'picklist'}
					<select class="select2 form-control" id="{$FIELD_NAME}" data-rule-required="true" name="{$FIELD_NAME}" placeholder="{vtranslate('LBL_SELECT_ONE', $QUALIFIED_MODULE_NAME)}">
						<option></option>
						{assign var=PICKLIST_VALUES value=$FIELD_MODEL->get('picklistvalues')}
						{foreach item=PICKLIST_VALUE key=PICKLIST_KEY from=$PICKLIST_VALUES}
							<option value="{$PICKLIST_KEY}" {if $FIELD_VALUE eq $PICKLIST_KEY} selected {/if}>
								{vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE_NAME)}
							</option>
						{/foreach}
					</select>
				{elseif $FIELD_TYPE == 'radio'}
					<label>
						<input type="radio" name="{$FIELD_NAME}" value="1" id="{$FIELD_NAME}" {if $FIELD_VALUE} checked="checked" {/if} />
						<span class="ms-2">{vtranslate('LBL_YES', $QUALIFIED_MODULE_NAME)}</span>
					</label>
					<label class="ms-4">
						<input type="radio" name="{$FIELD_NAME}" value="0" id="{$FIELD_NAME}" {if !$FIELD_VALUE} checked="checked" {/if}/>
						<span class="ms-2">{vtranslate('LBL_NO', $QUALIFIED_MODULE_NAME)}</span>
					</label>
				{elseif $FIELD_TYPE == 'password'}
					<input type="password" id="{$FIELD_NAME}" class="form-control" data-rule-required="true" name="{$FIELD_NAME}" value="{$FIELD_VALUE}" />
				{else}
					<input type="text" name="{$FIELD_NAME}" id="{$FIELD_NAME}" class="form-control" data-rule-required="true" {if $FIELD_NAME == 'username'} {/if} value="{$FIELD_VALUE}" />
				{/if}
			</div>
		</div>
	{/foreach}
{/strip}