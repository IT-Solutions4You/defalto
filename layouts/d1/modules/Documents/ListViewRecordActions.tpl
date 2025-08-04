{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
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
    <div class="more dropdown action">
        <div class="btn btn-sm text-secondary" data-bs-toggle="dropdown">
            <i class="fa fa-ellipsis-h icon"></i>
        </div>
        <ul class="dropdown-menu">
            {if $MODULE_MODEL->isStarredEnabled()}
                <li>
                    <a class="dropdown-item markStar">
                        <span class="followButton {if $STARRED eq 1}hide{/if}" title="{vtranslate('LBL_NOT_STARRED', $MODULE)}">
                            <i class="bi bi-bookmark text-secondary"></i>
                            <span class="ms-2">{vtranslate('LBL_FOLLOW', $MODULE)}</span>
                        </span>
                        <span class="unfollowButton {if $STARRED eq 0}hide{/if}" title="{vtranslate('LBL_STARRED', $MODULE)}">
                            <i class="bi bi-bookmark-fill text-secondary"></i>
                            <span class="ms-2">{vtranslate('LBL_UNFOLLOW', $MODULE)}</span>
                        </span>
                    </a>
                </li>
            {/if}
            <li>
                <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}">
                    <i class="fa-solid fa-circle-info text-secondary"></i>
                    <span class="ms-2">{vtranslate('LBL_DETAILS', $MODULE)}</span>
                </a>
            </li>
            {if $RECORD_ACTIONS}
                {if $RECORD_ACTIONS['edit']}
                    <li>
                        <a class="dropdown-item editLink" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}">
                            <i class="fa-solid fa-pencil text-secondary"></i>
                            <span class="ms-2">{vtranslate('LBL_EDIT', $MODULE)}</span>
                        </a>
                    </li>
                {/if}
				{if $RECORD_ACTIONS['delete']}
                <li>
                    <a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton dropdown-item">
                        <i class="fa-solid fa-trash text-secondary"></i>
                        <span class="ms-2">{vtranslate('LBL_DELETE', $MODULE)}</span>
                    </a>
                </li>
            {/if}
            {/if}
            {assign var=RECORD_ID value=$LISTVIEW_ENTRY->getId()}
            {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                <li>
                    <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)">
                        <i class="fa-solid fa-file text-secondary"></i>
                        <span class="ms-2">{vtranslate('File Preview', $MODULE)}</span>
                    </a>
                </li>
            {/if}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                <li>
                    <a class="dropdown-item" data-id="{$LISTVIEW_ENTRY->getId()}" name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}">
                        <i class="fa-solid fa-download text-secondary"></i>
                        <span class="ms-2">{vtranslate('Download', $MODULE)}</span>
                    </a>
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
