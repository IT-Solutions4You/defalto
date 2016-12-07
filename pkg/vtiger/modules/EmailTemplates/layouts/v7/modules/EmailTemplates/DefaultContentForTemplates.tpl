{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{assign var="VIEW_IN_BROWSER_TAG" value=EmailTemplates_Module_Model::$BROWSER_MERGE_TAG}
{assign var="WEBSITE_URL" value=$COMPANY_MODEL->get('website')}
{assign var="FACEBOOK_URL" value=$COMPANY_MODEL->get('facebook')}
{assign var="TWITTER_URL" value=$COMPANY_MODEL->get('twitter')}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title></title>
	</head>
	<body class="scayt-enabled">
		<div>
			<center>
				<table border="0" cellpadding="0" cellspacing="0" class="borderGrey" width="600px">
					<tbody>
						<tr>
							<td colspan="6"><!-- Begin Pre header -->
								<table border="0" cellpadding="5" cellspacing="0" height="52" style="font-family: Helvetica,Verdana,sans-serif; font-size: 10px; color: rgb(102, 102, 102); background-color: rgb(232, 245, 254);" width="597">
									<tbody>
										<tr>
											<td>
												<div>Use this area to offer a short teaser of your email&#39;s content. Text here will show in the preview area<br />
													of some email clients.</div>
											</td>
											<td><a href="{$VIEW_IN_BROWSER_TAG}" target="_blank">View in browser</a></td>
										</tr>
									</tbody>
								</table>
								<!-- // End Pre header \ --></td>
						</tr>
						<tr style="height:50px;">
							<td colspan="6" style="border-top: 1px solid #ddd; font-family: Helvetica,Verdana,sans-serif"></td>
						</tr>
						<tr>
							<td colspan="6" style="font-family: Helvetica,Verdana,sans-serif;font-size: 11px;color: #666666;">
								<table border="0" cellpadding="4" cellspacing="0" width="100%">
									<tbody>
										<tr>
											<td colspan="2" id="social" valign="middle">
												<center>
													<div>&nbsp;<a href="{$TWITTER_URL}" target="_blank"> follow on Twitter</a> | <a href="{$FACEBOOK_URL}" target="_blank">follow on Facebook</a></div>
												</center>
											</td>
										</tr>
										<!--copy right data-->
										<tr>
											<td valign="top" width="350px">
												<center>
													<div><em>Copyright &copy; 2014 {if !(empty($WEBSITE_URL))}<a href="{$WEBSITE_URL}" target="_blank">{$WEBSITE_URL}</a>{else}your company.com{/if}, All rights reserved.</em></div>
												</center>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</center>
		</div>
	</body>
</html>
