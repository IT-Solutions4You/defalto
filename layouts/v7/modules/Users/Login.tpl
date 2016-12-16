{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Users/views/Login.php *}

{strip}
<!DOCTYPE html>
<html>
	<head>
		<title>Vtiger login page</title>

		<style>
			.login-logo{
				width: 233px;
				margin: 0 auto;
			}
			.header h1, .header h4{
				text-align: center;
			}
			.login-form{
				width: 350px;
				margin: 0 auto;
				background-color: #F1F1F1;
			}
			.user-logo{
				width: 100px;
				margin: 0 auto;
				padding-top: 40px;
				padding-bottom: 30px;
			}
			.form-control{
				background-color:white !important;
				width: 200px;
				margin: 0 auto;
				margin-top: -15px;
			}
			.form-group .login-submit-btn{
				width: 200px;
				margin: 0 auto;
				display: block;
				margin-top: 10px;
			}
			.forgot-password-link {
				width: 200px;
				margin: 0 auto;
				display: block;
			}
			#forgot-password #username{
				background-color:white !important;
				width: 200px;
				height:35px;
				margin: 0 auto;
				display: block;
				margin-top: 30px;
			}
			#forgot-password #email{
				background-color:white !important;
				width: 200px;
				height:35px;
				margin: 0 auto;
				display: block;
				margin-top: -15px;
				margin-bottom: 20px;
			}

			.form-group .forgot-submit-btn{
				width: 200px;
				margin: 0 auto;
				display: block;
				margin-top: 20px;
			}
			#failure-message {
				width: 200px;
				margin: 0 auto;
				display: block;
				color: red;
			}
			.app-footer p {
				margin-top: 0px;
			}
			#page {
				padding-top: 55px;
			}
		</style>
	</head>

	<body>
		<div class="header">
			<div class="login-logo"><img src='layouts/v7/resources/Images/vtiger.jpg'/></div>
			<div><h1>One account. All of Vtiger.</h1></div>
			<div><h4>Sign in to continue to Vtiger</h4></div>
		</div>
		<div class="body">
			<div class="container login-form" id="login-form-div">

				<img class="img-circle img-responsive user-logo" src='layouts/v7/resources/Images/user.png'>

				<form class="form-horizontal" method="POST" action="index.php">
					<input type="hidden" name="module" value="Users"/>
					<input type="hidden" name="action" value="Login"/>
					<div class="form-group">
						<input class="form-control" id="username" type="text" name="username" placeholder="Username" value="admin">
					</div>
					<div class="form-group">
						<input class="form-control" id="password" type="password" name="password" placeholder="Password" value="admin">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary login-submit-btn">Sign in</button>
					</div>
					<div class="form-group">
						<label class="{if !$smarty.request.error}hide{/if}" id='failure-message'>Invalid credentials</label>
					</div>
					<div class="form-group">
						<a class="forgot-password-link">forgot password?</a>
					</div>
				</form>
			</div>
			<div class ="container login-form hide" id="forgot-password">
				<form class="form-horizontal" action="forgotPassword.php" method="POST">
					<div class="form-group">
						<input class="forgot-password" type="text" id="username" name="username" placeholder="Username">
					</div>
					<div class="form-group">
						<input class="forgot-password" type="email" id="email" name="email" placeholder="email">
					</div>
					<div class="form-group">
						<button type="submit" class="btn btn-primary forgot-submit-btn" name="retrievePassword">Submit</button>
					</div>
					<div class="form-group">
						<label class="hide" id='failure-message'></label>
					</div>
				</form>
			</div>
		</div>
		<div class="footer">
			{include file="Footer.tpl"|vtemplate_path:$MODULE}
		</div> 
	</body>
	<script>
		jQuery(document).ready(function () {
			jQuery('#login-form-div #username').focus();
			jQuery("#login-form-div a").click(function () {
				jQuery("#login-form-div").toggleClass('hide');
				jQuery("#forgot-password").toggleClass('hide');
			});

			jQuery(".forgot-password input").click(function () {
				jQuery("#forgot-password label").hide();
			});

			jQuery("#login-form-div button").on("click", function () {
				var username = jQuery('#login-form-div #username').val();
				var password = jQuery('#password').val();
				if (username === '') {
					jQuery("#login-form-div label").text('Please enter valid username');
					jQuery("#login-form-div label").removeClass('hide');
					return false;
				} else if (password === '') {
					jQuery("#login-form-div label").text('Pleeease enter valid password');
					jQuery("#login-form-div label").removeClass('hide');
					return false;
				} else {
					return true;
				}
			});

			jQuery("#forgot-password button").on("click", function () {
				var username = jQuery('#forgot-password #username').val();
				var email = jQuery('#email').val();

				var email1 = email.replace(/^\s+/, '').replace(/\s+$/, '');
				var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/;
				var illegalChars = /[\(\)\<\>\,\;\:\\\"\[\]]/;

				if (username === '') {
					jQuery("#forgot-password label").text('Please enter valid username');
					jQuery("#forgot-password label").removeClass('hide');
					return false;
				} else if (!emailFilter.test(email1) || email == '') {
					jQuery("#forgot-password label").text('Please enter valid email address');
					jQuery("#forgot-password label").removeClass('hide');
					return false;
				} else if (email.match(illegalChars)) {
					jQuery("#forgot-password label").text('The email address contains illegal characters.');
					jQuery("#forgot-password label").removeClass('hide');
					return false;
				} else {
					return true;
				}
			});
		});
	</script>
</html>
{/strip}