{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Import/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($TYPE, $MODULE)}}
		<div class="modal-body">
			<div id="popupPageContainer" class="contentsDiv import-details-container">
				<div id="popupContents" class="paddingLeftRight10px">
					<table class="table table-borderless listViewEntriesTable">
						<thead>
							<tr class="listViewHeaders">
								{assign var=LISTVIEW_HEADERS value=$IMPORT_RECORDS['headers']}
								{assign var=IMPORT_RESULT_DATA value=$IMPORT_RECORDS[$TYPE]}
								{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}<th>{vtranslate($LISTVIEW_HEADER->get('label'), $LISTVIEW_HEADER->getModule()->getName())}</th>{/foreach}
							</tr>
						</thead>
						{foreach item=RECORD from=$IMPORT_RESULT_DATA}
							<tr class="listViewEntries">
								{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
									<td>{$RECORD->get($LISTVIEW_HEADER->getName())}</td>
								{/foreach}
							</tr>
						{/foreach}
					</table>
				</div>
				<input type="hidden" class="triggerEventName" value="{$REQUEST_INSTANCE.triggerEventName}"/>
			</div>
		</div>
	</div>
</div>
