{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Users/actions/ForgotPassword.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{*<DIV>TEMPLATE: layout/modules/Users/FPLogin.tpl</DIV>*}
{if $ERROR}
	{$MESSAGE}
{else}
	<h4>Loading .... </h4>
	<form class="form-horizontal" name="login" id="login" method="post" action="../../../index.php?module=Users&action=Login">
		<input type=hidden name="username" value="{$USERNAME}" >
		<input type=hidden name="password" value="{$PASSWORD}" >
	</form>
	<script type="text/javascript">
		function autoLogin () {
			var form = document.getElementById("login");
			form.submit();
		}
		window.onload = autoLogin;
	</script>
{/if}