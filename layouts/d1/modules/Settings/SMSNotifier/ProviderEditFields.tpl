{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="form-group row py-2">
		<div class="col-lg-4">
			<label for="username">{vtranslate('username', $QUALIFIED_MODULE_NAME)}</label>
		</div>
		<div class="col-lg-6">
			<input type="text" class="form-control" name="username" data-rule-required="true" id="username" value="{$RECORD_MODEL->get('username')}"/>
		</div>
	</div>
	<div class="form-group row py-2">
		<div class="col-lg-4">
			<label for="password">{vtranslate('password', $QUALIFIED_MODULE_NAME)}</label>
		</div>
		<div class="col-lg-6">
			<input type="password" class="form-control" data-rule-required="true" name="password" id="password" value="{$RECORD_MODEL->get('password')}"/>
		</div>
	</div>
    {include file='BaseProviderEditFields.tpl'|@vtemplate_path:$QUALIFIED_MODULE_NAME RECORD_MODEL=$RECORD_MODEL}
{/strip}