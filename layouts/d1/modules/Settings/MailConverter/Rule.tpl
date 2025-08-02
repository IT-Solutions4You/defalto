{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="mailConverterRuleBlock">
		<div class="details border1px">
			<div class="ruleHead modal-header" style="cursor: move;">
				<div class="container-fluid p-3 border-bottom">
					<div class="row align-items-center">
						<div class="col-auto">
							<div class="btn">
								<img src="{vimage_path('drag.png')}" />
							</div>
						</div>
						<div class="col fw-bold">
							<span class="me-2">{vtranslate('LBL_RULE', $QUALIFIED_MODULE)}</span>
							<span class="sequenceNumber me-2">{$RULE_COUNT}</span>
							<span class="me-2">:</span>
							<span>{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}</span>
						</div>
						<div class="col-auto">
							{foreach from=$RULE_MODEL->getRecordLinks() item=ACTION_LINK}
								<span class="btn btn-outline-secondary ms-2"
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
			<div class="container-fluid">
				<div class="row">
					<fieldset class="col-lg-6 py-3">
						<div class="container-fluid">
							<div class="row py-2">
								<div class="col text-secondary fw-bold">{vtranslate('LBL_CONDITIONS', $QUALIFIED_MODULE)}</div>
							</div>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
								{if $FIELD_NAME eq 'action' or $FIELD_NAME eq 'assigned_to'}
									{continue}
								{/if}
								<div class="row py-2">
									<div class="col-sm-3 fieldLabel text-secondary">
										<label>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}</label>
									</div>
									<div class="col-sm-7 fieldValue">
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
									</div>
								</div>
							{/foreach}
						</div>
					</fieldset>
					<fieldset class="col-lg-6 py-3">
						<div class="container-fluid">
							<div class="row py-2">
								<div class="col text-secondary fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
							</div>
							<div class="row py-2">
								<div class="col-sm-3 fieldLabel text-secondary">
									<label>{vtranslate('action', $QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-lg-7 fieldValue">{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}</small></div>
							</div>
							{assign var=ASSIGNED_TO_RULES_ARRAY value=array('CREATE_HelpDesk_FROM', 'CREATE_Leads_SUBJECT', 'CREATE_Contacts_SUBJECT', 'CREATE_Accounts_SUBJECT')}
							{if in_array($RULE_MODEL->get('action'), $ASSIGNED_TO_RULES_ARRAY)}
								<div class="row py-2">
									<div class="col-sm-3 fieldLabel text-secondary">
										<label>{vtranslate('Assigned To')}</label>
									</div>
									<div class="col-lg-7 fieldValue">{$RULE_MODEL->get('assigned_to')}</div>
								</div>
							{/if}
						</div>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
	<br>
{/strip}
