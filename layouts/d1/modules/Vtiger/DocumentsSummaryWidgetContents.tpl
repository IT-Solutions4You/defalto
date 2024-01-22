{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="containerDocumentsSummaryWidgetContents">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-5">
                    <strong>{vtranslate('Title','Documents')}</strong>
                </div>
                <div class="col-sm-7">
                    <strong>{vtranslate('File Name', 'Documents')}</strong>
                </div>
            </div>
        </div>
        {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
            {assign var=DOWNLOAD_FILE_URL value=$RELATED_RECORD->getDownloadFileURL()}
            {assign var=DOWNLOAD_STATUS value=$RELATED_RECORD->get('filestatus')}
            {assign var=DOWNLOAD_LOCATION_TYPE value=$RELATED_RECORD->get('filelocationtype')}
            <div class="container-fluid recentActivitiesContainer my-3">
                <div class="row" id="documentRelatedRecord">
                    <span class="col-sm-5 text-truncate">
                        <a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('notes_title')}">
                            {$RELATED_RECORD->getDisplayValue('notes_title')}
                        </a>
                    </span>
                    <span class="col-sm-5 text-truncate" id="DownloadableLink">
                        {if $DOWNLOAD_STATUS eq 1}
                            {$RELATED_RECORD->getDisplayValue('filename', $RELATED_RECORD->getId(), $RELATED_RECORD)}
                        {else}
                            {$RELATED_RECORD->get('filename')}
                        {/if}
                    </span>
                    <span class="col-sm-2 text-secondary d-flex justify-content-end">
                        {* Documents list view special actions "view file" and "download file" *}
                        {assign var=RECORD_ID value=$RELATED_RECORD->getId()}
                        {if isPermitted('Documents', 'DetailView', $RECORD_ID) eq 'yes'}
                            {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
                            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                                <a name="viewfile" class="ms-3" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event,{$RECORD_ID})">
                                    <i title="{vtranslate('LBL_VIEW_FILE', 'Documents')}" class="fa fa-picture-o alignMiddle"></i>
                                </a>
                            {/if}
                            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                                <a name="downloadfile" class="ms-3" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}">
                                    <i title="{vtranslate('LBL_DOWNLOAD_FILE', 'Documents')}" class="fa fa-download alignMiddle"></i>
                                </a>
                            {/if}
                        {/if}
                    </span>
                </div>
            </div>
        {/foreach}
    </div>
    {assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
    {if $NUMBER_OF_RECORDS eq 5}
        <div class="container-fluid">
            <div class="row py-2">
                <div class="col text-center">
                    <a target="_blank" href="index.php?{$RELATION_LIST_URL}&tab_label={$ACTIVITIES_MODULE_NAME}" class="moreRecentDocuments btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
                </div>
            </div>
        </div>
    {/if}
{/strip}