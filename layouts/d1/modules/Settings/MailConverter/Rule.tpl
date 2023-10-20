{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	<div class="mailConverterRuleBlock">
		<div class="details border1px">
			<div class="ruleHead modal-header" style="cursor: move;">
				<div class="container-fluid p-3 border-bottom">
					<div class="row">
						<div class="col">
							<strong>
								<img src="{vimage_path('drag.png')}" />
								<span class="mx-2">{vtranslate('LBL_RULE', $QUALIFIED_MODULE)}</span>
								<span class="sequenceNumber">{$RULE_COUNT}</span>
								<span class="mx-2">:</span>
								<span>{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}</span>
							</strong>
						</div>
						<div class="col-auto">
							{foreach from=$RULE_MODEL->getRecordLinks() item=ACTION_LINK}
								<span class="btn"
									{if stripos($ACTION_LINK->getUrl(), 'javascript:')===0}
										onclick='{$ACTION_LINK->getUrl()|substr:strlen("javascript:")}'
									{else}
										onclick='window.location.href = "{$ACTION_LINK->getUrl()}"'
									{/if}>
									<i title="{vtranslate($ACTION_LINK->get('linklabel'), $MODULE)}" class="alignMiddle cursorPointer {$ACTION_LINK->get('linkicon')}"></i>
								</span>
							{/foreach}
						</div>
					</div>
				</div>
			</div>
			<fieldset class="p-3">
				<div class="container-fluid">
					<div class="row py-2">
						<div class="col">
							<strong>{vtranslate('LBL_CONDITIONS', $QUALIFIED_MODULE)}</strong>
						</div>
					</div>
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
						<div class="row py-2">
							<div class="col-lg-3 fieldLabel">
								<label>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-lg-7 fieldValue">
								{if $FIELD_NAME neq 'action' && $FIELD_NAME neq 'assigned_to'}
									{assign var=FIELD_VALUE value=$RULE_MODEL->get($FIELD_NAME)}
									{if $FIELD_NAME eq 'matchusing'}
										{assign var=FIELD_VALUE value=vtranslate('LBL_ANY_CONDITIONS', $QUALIFIED_MODULE)}
										{if $RULE_MODEL->get('matchusing') eq 'AND'}
											{assign var=FIELD_VALUE value=vtranslate('LBL_ALL_CONDITIONS', $QUALIFIED_MODULE)}
										{/if}
									{elseif $FIELD_NAME eq 'subject'}
										<span class="me-2">{vtranslate($RULE_MODEL->get('subjectop'))}</span>
									{elseif $FIELD_NAME eq 'body'}
										<span class="me-2">{vtranslate($RULE_MODEL->get('bodyop'))}</span>
									{/if}
									<span>{$FIELD_VALUE}</span>
								{/if}
							</div>
						</div>
					{/foreach}
					{assign var=ASSIGNED_TO_RULES_ARRAY value=array('CREATE_HelpDesk_FROM', 'CREATE_Leads_SUBJECT', 'CREATE_Contacts_SUBJECT', 'CREATE_Accounts_SUBJECT')}
					{if in_array($RULE_MODEL->get('action'), $ASSIGNED_TO_RULES_ARRAY)}
						<div class="row py-2">
							<div class="col-lg-3 fieldLabel"><label>{vtranslate('Assigned To')}</label></div>
							<div class="col-lg-7 fieldValue">{$RULE_MODEL->get('assigned_to')}</div>
						</div>
					{/if}
				</div>
			</fieldset>
			<fieldset class="p-3">
				<div class="container-fluid">
					<div class="row py-2">
						<div class="col">
							<strong>{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>
						</div>
					</div>
					<div class="row py-2">
						<div class="col-lg-3 fieldLabel"><label>{vtranslate('action', $QUALIFIED_MODULE)}</label></div>
						<div class="col-lg-7 fieldValue">{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}</small></div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<br>
{/strip}
