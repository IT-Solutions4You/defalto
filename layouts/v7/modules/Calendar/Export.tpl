{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}

<div class="fc-overlay-modal modal-content">
    <form id="exportForm" class="form-horizontal" method="post" action="index.php">
        <input type="hidden" name="module" value="{$SOURCE_MODULE}" />
        <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
        <input type="hidden" name="action" value="ExportData" />
        <input type="hidden" name="viewname" value="{$VIEWID}" />
        <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
        <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" id="page" name="page" value="{$PAGE}" />
        <input type="hidden" value="export" name="view">

        <div class="overlayHeader">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_EXPORT_RECORDS', $MODULE)}}
        </div>
        <div class="modal-body" style="margin-bottom:250px">
            <div class="well exportContents">
                <div>
                    <label style ="font-weight:normal">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_FILE_NAME',$MODULE)}</label>
                    <input type='text' name='filename' id='filename' size='25' value='vtiger.calendar'/>
                </div>
                <hr>
                <div>
                    <input type="radio" name="mode" value="ExportSelectedRecords" id ="group1" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if} />
                    <label style ="font-weight:normal" for="group1">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}</label>
                    {if empty($SELECTED_IDS)}&nbsp; <span style ="color:red">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
                </div>
                <br>
                <div>
                    <input type="radio" name="mode" value="ExportCurrentPage" id ="group2"/>
                    <label style ="font-weight:normal" for="group2">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}</label>
                </div>
                <br>
                <div>
                    <input type="radio" name="mode" value="ExportAllData" id ="group3" {if empty($SELECTED_IDS)} checked="checked" {/if}/>
                    <label style ="font-weight:normal" for="group3">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}</label>
                </div>
            </div>
        </div>
        <div class='modal-overlay-footer clearfix'>
            <div class="row clearfix">
                    <div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '><button type="submit" class="btn btn-success btn-lg">{vtranslate($MODULE, $MODULE)}&nbsp;{vtranslate($SOURCE_MODULE, $MODULE)}</button>
                    &nbsp;&nbsp;<a class='cancelLink' data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </div>
            </div>
        </div>
    </form>
</div>

