{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
<!--LIST VIEW RECORD ACTIONS-->

<div class="table-actions d-flex align-items-center text-secondary">
    {if !$SEARCH_MODE_RESULTS}
    <div class="input form-check" >
        <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input"/>
    </div>
    {/if}
    {if $LISTVIEW_ENTRY->get('starred') eq vtranslate('LBL_YES', $MODULE)}
        {assign var=STARRED value=true}
    {else}
        {assign var=STARRED value=false}
    {/if}
	{if $MODULE_MODEL->isStarredEnabled()}
		<span class="mx-2">
			<a class="text-secondary markStar fa icon action {if $STARRED} fa-star active {else} fa-star-o{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
		</span>
	{/if}
    <div class="more dropdown action mx-2">
        <div data-bs-toggle="dropdown">
            <i class="fa fa-ellipsis-h icon"></i>
        </div>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}">{vtranslate('LBL_DETAILS', $MODULE)}</a>
            </li>
            {if $RECORD_ACTIONS}
                {if $RECORD_ACTIONS['edit']}
                    <li>
                        <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" data-url="{$LISTVIEW_ENTRY->getEditViewUrl()}" name="editlink">{vtranslate('LBL_EDIT', $MODULE)}</a>
                    </li>
                {/if}
				{if $RECORD_ACTIONS['delete']}
                <li>
                    <a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton dropdown-item">{vtranslate('LBL_DELETE', $MODULE)}</a>
                </li>
            {/if}
            {/if}
            {assign var=RECORD_ID value=$LISTVIEW_ENTRY->getId()}
            {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                <li>
                    <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)">File Preview</a>
                </li>
            {/if}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                <li>
                    <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}">Download</a>
                </li>
            {/if}
        </ul>
    </div>
    <div class="btn-group inline-save hide">
        <button class="button btn-success btn-small save" name="save"><i class="fa fa-check"></i></button>
        <button class="button btn-danger btn-small cancel" name="Cancel"><i class="fa fa-close"></i></button>
    </div>
</div>
{/strip}
