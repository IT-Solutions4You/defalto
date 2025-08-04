{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="modal-dialog modal-lg">
		<div id="accountHierarchyContainer" class="modelContainer modal-content" style='min-width:750px'>
			{assign var=HEADER_TITLE value={vtranslate('LBL_SHOW_ACCOUNT_HIERARCHY', $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<div class="modal-body">
				<div id ="hierarchyScroll" style="margin-right: 8px;">
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								{foreach item=HEADERNAME from=$ACCOUNT_HIERARCHY['header']}
									<th>{vtranslate($HEADERNAME, $MODULE)}</th>
									{/foreach}
							</tr>
						</thead>
						{foreach item=ENTRIES from=$ACCOUNT_HIERARCHY['entries']}
							<tbody>
								<tr>
									{foreach item=LISTFIELDS from=$ENTRIES}
										<td>{$LISTFIELDS}</td>
									{/foreach}
								</tr>
							</tbody>
						{/foreach}
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<div class="pull-right cancelLinkContainer">
					<button class="btn btn-primary" type="reset" data-bs-dismiss="modal"><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}