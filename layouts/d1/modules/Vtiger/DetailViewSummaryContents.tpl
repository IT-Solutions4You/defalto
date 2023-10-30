{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<form id="detailView" class="clearfix mt-3" method="POST">
		<div class="resizable-summary-view">
			<div class="container-fluid">
				<div class="row">
					{include file='SummaryViewWidgets.tpl'|vtemplate_path:$MODULE_NAME}
				</div>
			</div>
		</div>
	</form>
{/strip}