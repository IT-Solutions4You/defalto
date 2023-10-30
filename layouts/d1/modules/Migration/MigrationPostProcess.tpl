{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<center>
		<footer class="noprint">
			<div class="vtFooter">
				<p>
					{vtranslate('POWEREDBY')} {$VTIGER_VERSION}&nbsp;
					&copy; 2004 - {date('Y')}&nbsp;
					<a href="//www.vtiger.com" target="_blank">vtiger.com</a>
					&nbsp;|&nbsp;
					<a href="#" onclick="window.open('copyright.html', 'copyright', 'height=115,width=575').moveTo(210, 620)">{vtranslate('LBL_READ_LICENSE')}</a>
					&nbsp;|&nbsp;
					<a href="https://www.vtiger.com/privacy-policy" target="_blank">{vtranslate('LBL_PRIVACY_POLICY')}</a>
				</p>
			</div>
		</footer>
	</center>
	{include file='JSResources.tpl'|@vtemplate_path}
	</div>
{/strip}