{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<form class="form-horizontal" name="step6" method="post" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step7" />
	<input type=hidden name="auth_key" value="{$AUTH_KEY}" />

	<div class="row main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4>{vtranslate('LBL_ONE_LAST_THING','Install')}</h4>
				</div>
				<div class="col-sm-2">
					<a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="pull-right">
						<img src="{'help.png'|vimage_path}" alt="Help-Icon"/>
					</a>
				</div>
			</div>
			<hr>
			<div class="offset2 row">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<table class="config-table input-table">
						<tbody>
							<tr>
								<td><strong>Your Name</strong> <span class="no">*</span></td>
								<td><input name="myname" class="text" required="true" style="width: 250px;"></td>
							</tr>
							<tr>
								<td><strong>Your Email</strong> 
								<td><input name="myemail" class="email" required="true" style="width: 250px;"></td>
							<tr>
								<td><strong>Your Industry</strong> <span class="no">*</span></td>
								<td>
									<select name="industry" class="select2" required="true" style="width:250px;" placeholder="Choose one...">
										<option>Accounting</option>
										<option>Advertising</option>
										<option>Agriculture</option>
										<option>Apparel &amp; Accessories</option>
										<option>Automotive</option>
										<option>Banking &amp; Financial Services</option>
										<option>Biotechnology</option>
										<option>Call Centers</option>
										<option>Careers/Employment</option>
										<option>Chemical</option>
										<option>Computer Hardware</option>
										<option>Computer Software</option>
										<option>Consulting</option>
										<option>Construction</option>
										<option>Education</option>
										<option>Energy Services</option>
										<option>Engineering</option>
										<option>Entertainment</option>
										<option>Financial</option>
										<option>Food &amp; Food Service</option>
										<option>Government</option>
										<option>Health care</option>
										<option>Insurance</option>
										<option>Legal</option>
										<option>Logistics</option>
										<option>Manufacturing</option>
										<option>Media &amp; Production</option>
										<option>Non-profit</option>
										<option>Pharmaceutical</option>
										<option>Real Estate</option>
										<option>Rental</option>
										<option>Retail &amp; Wholesale</option>
										<option>Security</option>
										<option>Service</option>
										<option>Sports</option>
										<option>Telecommunications</option>
										<option>Transportation</option>
										<option>Travel &amp; Tourism</option>
										<option>Utilities</option>
										<option>Other</option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2">
								This information will not be shared. Vtiger will use the email address for sending a monthly newsletter and any product updates.
								Industry will be used to understand use cases and further improve the product.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row offset2">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<div class="button-container">
						<input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step7"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<div id="progressIndicator" class="row main-container hide">
	<div class="inner-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-12 welcome-div alignCenter">
					<h3>{vtranslate('LBL_INSTALLATION_IN_PROGRESS','Install')}...</h3><br>
					<img src="{'install_loading.gif'|vimage_path}"/>
					<h6>{vtranslate('LBL_PLEASE_WAIT','Install')}.... </h6>
				</div>
			</div>
		</div>
	</div>
</div>