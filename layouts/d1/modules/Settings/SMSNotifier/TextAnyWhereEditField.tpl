{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{include file="ProviderEditFields.tpl"|@vtemplate_path:$QUALIFIED_MODULE_NAME}
	<div class="alert-info alert mt-3">
		<div>
			In the Originator field, enter one of the following:<br /><br />
			1. The 11 characters to be sent with each SMS<br />
			2. The mobile number to be sent with each SMS<br />
			3. The email address to which any SMS replies will be sent<br />
		</div>
		<br>
		<div>
			<div>
				<a href="http://www.textanywhere.net/static/Products/VTiger_Capabilities.aspx" target="_blank">Help</a>
			</div>
			<div>
				<a href="https://www.textapp.net/web/textanywhere/" target="_blank">Account Login</a>
			</div>
			<div>
				<a href="https://www.textapp.net/web/textanywhere/Register/Register.aspx" target="_blank">Create Account</a>
			</div>
		</div>
	</div>	
{/strip}