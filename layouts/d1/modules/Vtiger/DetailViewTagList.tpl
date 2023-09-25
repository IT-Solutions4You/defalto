{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
<div class="containerDetailViewTagList d-inline">
    <div class="tagContainer d-inline">
        <span class="tag-contents {if empty($TAGS_LIST)}hide{/if}">
            <span class="detailTagList" data-num-of-tags-to-show="{Vtiger_Tag_Model::NUM_OF_TAGS_DETAIL}">
                {foreach from=$TAGS_LIST item=TAG_MODEL name=tagCounter}
                    {assign var=TAG_LABEL value=$TAG_MODEL->getName()}
                    {include file="Tag.tpl"|vtemplate_path:$MODULE}
                {/foreach}
            </span>
        </span>
        <span id="addTagContainer">
            <a id="addTagTriggerer" class="btn btn-outline-secondary mb-1 me-1">
                <i class="fa fa-plus"></i>
                {vtranslate('LBL_ADD_NEW_TAG',$MODULE)}
            </a>
        </span>
        <div class="viewAllTagsContainer hide">
            <div class="modal-dialog">
                <div class="modal-content">
                    {assign var="TITLE" value="{vtranslate('LBL_TAG_FOR',$MODULE,$RECORD->getName())}"}
                    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE}
                    <div class="modal-body detailShowAllModal">
                        <div class="form-group">
                            <label class="col-lg-3 col-sm-12 col-md-4 control-label">
                                {vtranslate('LBL_CURRENT_TAGS',$MODULE)}
                            </label>
                            <div class="col-lg-9 col-sm-12 col-md-8 ">
                                <div class="currentTag multiLevelTagList form-control">
                                    {foreach item=TAG_MODEL from=$TAGS_LIST}
                                        {include file=vtemplate_path('Tag.tpl',$MODULE) NO_EDIT=true}
                                    {/foreach}
                                </div>
                           </div>
                       </div>
                   </div>
                </div>
            </div>
        </div>
       {include file="AddTagUI.tpl"|vtemplate_path:$MODULE RECORD_NAME=$RECORD->getName()}
    </div>
    <div id="dummyTagElement" class="hide">
    {assign var=TAG_MODEL value=Vtiger_Tag_Model::getCleanInstance()}
    {include file=vtemplate_path('Tag.tpl',$MODULE)}
    </div>
    <div>
        <div class="editTagContainer hide" >
            <input type="hidden" name="id" value="" />
            <div class="editTagContents">
                <div>
                    <input type="text" class="form-control" name="tagName" value="" style="width:100%" />
                </div>
                <div class="my-2">
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                            <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" />
                            <span class="ms-2">{vtranslate('LBL_SHARE_TAG',$MODULE)}</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="btn-group w-100">
                <button class="btn btn-mini btn-success saveTag" type="button" style="width:50%;float:left">
                    <div class="text-center"> <i class="fa fa-check"></i> </div>
                </button>
                <button class="btn btn-mini btn-danger cancelSaveTag" type="button" style="width:50%">
                    <div class="text-center"> <i class="fa fa-close"></i> </div>
                </button>
            </div>
        </div>
    </div>
</div>