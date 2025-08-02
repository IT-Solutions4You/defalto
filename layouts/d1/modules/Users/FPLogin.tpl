{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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