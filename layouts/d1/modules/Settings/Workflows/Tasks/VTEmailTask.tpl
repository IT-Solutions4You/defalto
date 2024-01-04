{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div id="VtEmailTaskContainer">
		<div class="row form-group py-2">
			<div class="col-sm-2 col-xs-2">
				{vtranslate('LBL_FROM', $QUALIFIED_MODULE)}
			</div>
			<div class="col-sm-3 col-xs-3">
				<input name="fromEmail" class="fields inputElement form-control" type="text" value="{$TASK_OBJECT->fromEmail}"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select id="fromEmailOption" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{$FROM_EMAIL_FIELD_OPTION}
				</select>
			</div>
		</div>

		<div class="row form-group py-2">
			<div class="col-sm-2 col-xs-2">
				{vtranslate('Reply To',$QUALIFIED_MODULE)}
			</div>
			<div class="col-sm-3 col-xs-3">
				<input name="replyTo" class="fields inputElement form-control" type="text" value="{$TASK_OBJECT->replyTo}"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select class="task-fields select2 overwriteSelection form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{$EMAIL_FIELD_OPTION}
				</select>
			</div>
		</div>
		<div class="row form-group py-2">
			<span class="col-sm-2 col-xs-2">
				<span>{vtranslate('LBL_TO',$QUALIFIED_MODULE)}</span>
				<span class="text-danger ms-2">*</span>
			</span>
			<div class="col-sm-3 col-xs-3">
				<input data-rule-required="true" name="recepient" class="fields inputElement form-control" type="text" value="{$TASK_OBJECT->recepient}"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select class="task-fields select2 form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{$EMAIL_FIELD_OPTION}
				</select>
			</div>
		</div>
		<div class="row form-group py-2 {if empty($TASK_OBJECT->emailcc)}hide{/if}" id="ccContainer">
			<div class="col-sm-2 col-xs-2">{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</div>
			<div class="col-sm-3 col-xs-3">
				<input class="fields inputElement form-control" type="text" name="emailcc" value="{$TASK_OBJECT->emailcc}"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select class="task-fields select2 form-select" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}'>
					<option></option>
					{$EMAIL_FIELD_OPTION}
				</select>
			</div>
		</div>
		<div class="row form-group py-2 {if empty($TASK_OBJECT->emailbcc)}hide{/if}" id="bccContainer">
			<div class="col-sm-2 col-xs-2">{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</div>
			<div class="col-sm-3 col-xs-3">
				<input class="fields inputElement form-control" type="text" name="emailbcc" value="{$TASK_OBJECT->emailbcc}"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select class="task-fields select2 form-select" data-placeholder='{vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}'>
					<option></option>
					{$EMAIL_FIELD_OPTION}
				</select>
			</div>
		</div>
		<div class="row form-group py-2 {if (!empty($TASK_OBJECT->emailcc)) and (!empty($TASK_OBJECT->emailbcc))}hide{/if}">
			<div class="col-sm-2 col-xs-2"></div>
			<div class="col-sm-3 col-xs-3">
				<a class="btn btn-outline-secondary me-2 {if (!empty($TASK_OBJECT->emailcc))}hide{/if}" id="ccLink">{vtranslate('LBL_ADD_CC',$QUALIFIED_MODULE)}</a>
				<a class="btn btn-outline-secondary me-2 {if (!empty($TASK_OBJECT->emailbcc))}hide{/if}" id="bccLink">{vtranslate('LBL_ADD_BCC',$QUALIFIED_MODULE)}</a>
			</div>
		</div>
		<div class="row form-group py-2">
			<div class="col-sm-2 col-xs-2">{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
			<div class="col-sm-3 col-xs-3">
				<input data-rule-required="true" class="fields inputElement form-control" type="text" name="subject" value="{$TASK_OBJECT->subject}" id="subject" spellcheck="true"/>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select class="task-fields select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{$ALL_FIELD_OPTIONS}
				</select>
			</div>
		</div>
		<div class="row form-group py-2">
			<div class="col-sm-2 col-xs-2">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</div>
			<div class="col-sm-6 col-xs-6">
				<select id="task-fieldnames" class="select2 form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{$ALL_FIELD_OPTIONS}
				</select>
			</div>
		</div>
		<div class="row form-group py-2">
			<div class="col-sm-2 col-xs-2">{vtranslate('LBL_GENERAL_FIELDS',$QUALIFIED_MODULE)}</div>
			<div class="col-sm-6 col-xs-6">
				<select id="task_timefields" class="select2 form-select" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
					<option></option>
					{foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
						<option value="{if strpos(strtolower($META_VARIABLE_VALUE), 'url') === false}${/if}{$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row form-group py-2">
			<div class="col-sm-12 col-xs-12">
				<textarea id="content" name="content">{$TASK_OBJECT->content}</textarea>
			</div>
		</div>
	</div>
{/strip}