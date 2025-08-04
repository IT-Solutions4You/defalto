{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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