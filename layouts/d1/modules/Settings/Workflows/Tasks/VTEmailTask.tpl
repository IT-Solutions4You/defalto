{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
	<div id="VtEmailTaskContainer">
		<div class="contents tabbable ui-sortable">
			<div class="layoutContent">
				<div id="detailViewLayout">
					<div class="row">
						<h4 class="m-0 fw-bold py-3 border-bottom">{vtranslate('LBL_EMAIL_DETAILS',$QUALIFIED_MODULE)}</h4>
					</div>
					{assign var=SMTP_SERVERS value=$TASK_OBJECT->getSMTPServers()}
					{if !empty($SMTP_SERVERS)}
						<div class="row form-group py-2">
							<div class="col-lg-2">{vtranslate('SMTP', $QUALIFIED_MODULE)}</div>
							<div class="col-lg-6">
								<select name="smtp" id="smtp" class="select2 inputElement form-select">
									<option>{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
									{foreach from=$TASK_OBJECT->getSMTPServers() key=SMTP_SERVER_ID item=SMTP_SERVER}
										<option value="{$SMTP_SERVER_ID}" {if $SMTP_SERVER_ID eq $TASK_OBJECT->smtp}selected="selected"{/if}>{$SMTP_SERVER->get('server')} &lt;{$SMTP_SERVER->get('server_username')}&gt;</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
					<div class="row form-group py-2">
						<div class="col-lg-2">{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}</div>
						<div class="col-lg-6">
							<select id="fromEmail" name="fromEmail" class="inputElement select2 form-select" multiple="multiple" data-tags="1" data-maximum-selection-length="1" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								<option></option>
								{foreach from=$TASK_OBJECT->getFromEmailFields($FROM_EMAIL_FIELDS) key=FROM_EMAIL_FIELD_KEY item=FROM_EMAIL_FIELD}
									<option value="{$FROM_EMAIL_FIELD_KEY}" {if $TASK_OBJECT->isSelectedFromEmailField($FROM_EMAIL_FIELD_KEY)}selected="selected"{/if}>{$FROM_EMAIL_FIELD}</option>
								{/foreach}
							</select>
						</div>
						<div class="col-lg-auto">
							<a class="btn btn-outline-secondary {if (!empty($TASK_OBJECT->replyTo))}hide{/if}" id="bccLink" data-show-container="#replyToContainer">{vtranslate('LBL_ADD_REPLY_TO',$QUALIFIED_MODULE)}</a>
						</div>
					</div>
					<div class="row form-group py-2 {if empty($TASK_OBJECT->replyTo)}hide{/if}" id="replyToContainer">
						<div class="col-lg-2">{vtranslate('Reply To',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-6">
							<select id="replyTo" name="replyTo" class="inputElement select2 form-select" multiple="multiple" data-tags="1" data-maximum-selection-length="1" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								{foreach from=$TASK_OBJECT->getReplyToEmailFields($EMAIL_FIELDS) key=EMAIL_FIELD_KEY item=EMAIL_FIELD}
									<option value="{$EMAIL_FIELD_KEY}" {if $TASK_OBJECT->isSelectedReplyToEmailField($EMAIL_FIELD_KEY)}selected="selected"{/if}>{$EMAIL_FIELD}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="row form-group py-2">
						<div class="col-lg-2">
							<span>{vtranslate('LBL_TO',$QUALIFIED_MODULE)}</span>
							<span class="text-danger ms-2">*</span>
						</div>
						<div class="col-lg-6">
							<select id="recepient" data-rule-required="true" name="recepient" class="inputElement select2 form-select" multiple="multiple" data-tags="1" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								{foreach from=$TASK_OBJECT->getToEmailFields($EMAIL_FIELDS) key=EMAIL_FIELD_KEY item=EMAIL_FIELD}
									<option value="{$EMAIL_FIELD_KEY}" {if $TASK_OBJECT->isSelectedToEmailField($EMAIL_FIELD_KEY)}selected="selected"{/if}>{$EMAIL_FIELD}</option>
								{/foreach}
							</select>
						</div>
						<div class="col-lg-auto">
							<a class="btn btn-outline-secondary me-2 {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink" data-show-container="#ccContainer">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>
							<a class="btn btn-outline-secondary {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink" data-show-container="#bccContainer">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
						</div>
					</div>
					<div class="row form-group py-2 {if empty($TASK_OBJECT->emailcc)}hide{/if}" id="ccContainer">
						<div class="col-lg-2">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-6">
							<select id="emailcc" name="emailcc" class="inputElement select2 form-select" multiple="multiple" data-tags="1" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								{foreach from=$TASK_OBJECT->getCCEmailFields($EMAIL_FIELDS) key=EMAIL_FIELD_KEY item=EMAIL_FIELD}
									<option value="{$EMAIL_FIELD_KEY}" {if $TASK_OBJECT->isSelectedCCEmailField($EMAIL_FIELD_KEY)}selected="selected"{/if}>{$EMAIL_FIELD}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="row form-group py-2 {if empty($TASK_OBJECT->emailbcc)}hide{/if}" id="bccContainer">
						<div class="col-lg-2">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-6">
							<select id="emailbcc" name="emailbcc" class="inputElement select2 form-select" multiple="multiple" data-tags="1" data-placeholder="{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}">
								{foreach from=$TASK_OBJECT->getBCCEmailFields($EMAIL_FIELDS) key=EMAIL_FIELD_KEY item=EMAIL_FIELD}
									<option value="{$EMAIL_FIELD_KEY}" {if $TASK_OBJECT->isSelectedBCCEmailField($EMAIL_FIELD_KEY)}selected="selected"{/if}>{$EMAIL_FIELD}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				<div id="relatedTabTemplate">
					<div class="row">
						<h4 class="m-0 fw-bold py-3 border-bottom">{vtranslate('LBL_EMAIL_CONTENT',$QUALIFIED_MODULE)}</h4>
					</div>
					<div class="row form-group py-2">
						<div class="col-lg-2">{vtranslate('LBL_EMAIL_TEMPLATE',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-6">
							<select id="task_template" name="template" class="inputElement select2 form-select">
								<option value="custom_template">{vtranslate('LBL_CUSTOM_TEMPLATE', $QUALIFIED_MODULE)}</option>
								{html_options  options=$TASK_OBJECT->getTemplates($SOURCE_MODULE) selected=$TASK_OBJECT->template}
							</select>
							<input type="hidden" id="task_folder_value" value="{$TASK_OBJECT->template}">
						</div>
					</div>
					<div class="templateContainer">
						<div class="row form-group py-2">
							<div class="col-lg-2">{vtranslate('LBL_EMAIL_LANGUAGE',$QUALIFIED_MODULE)}</div>
							<div class="col-lg-6">
								{assign var=LANGUAGES_ARRAY value=$TASK_OBJECT->getLanguages()}
								<select id="task_template_language" name="template_language" class="inputElement select2 form-select">
									{html_options  options=$LANGUAGES_ARRAY selected=$TASK_OBJECT->template_language}
								</select>
								<input type="hidden" id="template_language_value" value="{$TASK_OBJECT->template_language}">
							</div>
						</div>
						{if $TASK_OBJECT->isDynamicTemplateByField()}
							<div class="row form-group py-2" id="templateFieldsContainer">
								<div class="col-lg-2">{vtranslate('LBL_EMAIL_TEMPLATE_BY_FIELD',$QUALIFIED_MODULE)}</div>
								<div class="col-lg-6">
									{assign var=MODULE_FIELDS value=$TASK_OBJECT->getDynamicTemplateFields($SOURCE_MODULE)}
									<select id="template_field" name="template_field" class="inputElement select2 form-select">
										{html_options options=$MODULE_FIELDS selected=$TASK_OBJECT->template_field}
									</select>
								</div>
							</div>
						{/if}
					</div>
					<div class="customTemplateContainer">
						<div class="row form-group py-2">
							<div class="col-lg-2">
								{vtranslate('LBL_VARIABLES',$QUALIFIED_MODULE)}
							</div>
							<div class="col-lg-6">
								<select id="task_variables" class="select2 form-select" data-width="100%" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
									<option></option>
									<optgroup label="{vtranslate('LBL_GENERAL_FIELDS',$QUALIFIED_MODULE)}">
										{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
											<option value="{if strpos(strtolower($META_VARIABLE_VALUE), 'url') === false}${/if}{$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
										{/foreach}
									</optgroup>
									<optgroup label="{vtranslate('LBL_MODULE_FIELDS',$QUALIFIED_MODULE)}">
										{$ALL_FIELD_OPTIONS}
									</optgroup>
								</select>
							</div>
							<div class="col-auto">
								<button type="button" class="btn btn-outline-secondary task_variables_subject">
									<i class="fa-solid fa-dollar"></i>
									<span class="ms-2">{vtranslate('LBL_ADD_TO_SUBJECT',$QUALIFIED_MODULE)}</span>
								</button>
								<button type="button" class="ms-2 btn btn-outline-secondary task_variables_body">
									<i class="fa-solid fa-dollar"></i>
									<span class="ms-2">{vtranslate('LBL_ADD_TO_BODY',$QUALIFIED_MODULE)}</span>
								</button>
							</div>
						</div>
						<div class="row form-group py-2">
							<div class="col-lg-2">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
							<div class="col-lg-6">
								<input data-rule-required="true" class="fields inputElement form-control" type="text" name="subject" value="{$TASK_OBJECT->subject}" id="subject" spellcheck="true"/>
							</div>
						</div>
						<div class="row form-group py-2">
							<div class="col-lg-2">
								{vtranslate('LBL_BODY',$QUALIFIED_MODULE)}
							</div>
							<div class="col-lg-6">
								<textarea id="content" name="content">{$TASK_OBJECT->content}</textarea>
							</div>
						</div>
					</div>
					<div class="row form-group py-2">
						<div class="col-lg-2">{vtranslate('LBL_SIGNATURE',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-8">
							<label class="form-check form-switch">
								<input type="checkbox" name="signature" id="signature" class="form-check-input" {if $TASK_OBJECT->signature}checked="checked"{/if}">
							</label>
						</div>
					</div>
					<div class="row form-group py-2">
						<div class="col-lg-2">{vtranslate('LBL_EXECUTE_IMMEDIATELY',$QUALIFIED_MODULE)}</div>
						<div class="col-lg-8">
							<label class="form-check form-switch">
								<input type="hidden" name="executeImmediately" id="executeImmediately" value="">
								<input type="checkbox" name="executeImmediately" id="executeImmediately" class="form-check-input" value="1" {if !empty($TASK_OBJECT->executeImmediately)}checked="checked"{/if}>
							</label>
						</div>
					</div>
				</div>
				<div id="infoTabTemplate">
					<div class="row">
						<h4 class="m-0 fw-bold py-3 border-bottom">{vtranslate('LBL_CONFIG_DETAILS',$QUALIFIED_MODULE)}</h4>
					</div>
					<div class="row py-2">
						<div class="col-lg-2">
							{vtranslate('LBL_EMAIL_TEMPLATE_BY_FIELD',$QUALIFIED_MODULE)}
						</div>
						<div class="col-lg-6">
							<div class="mb-2">{vtranslate('LBL_EMAIL_TEMPLATE_BY_FIELD_DESCRIPTION',$QUALIFIED_MODULE)}</div>
							<pre class="bg-body-secondary p-2 border rounded">$email_maker_dynamic_template_wf = true;</pre>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="layouts/{Vtiger_Viewer::getLayoutName()}/modules/Settings/Workflows/resources/VTEmailTask.js" type="text/javascript" charset="utf-8"></script>
	</div>
{/strip}