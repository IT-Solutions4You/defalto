{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="containerDetailViewTagList d-inline align-top">
    <div class="tagContainer d-inline">
        <span class="tag-contents {if empty($TAGS_LIST)}hide{/if}">
            <span class="detailTagList" data-num-of-tags-to-show="{Vtiger_Tag_Model::NUM_OF_TAGS_DETAIL}">
                {foreach from=$TAGS_LIST item=TAG_MODEL name=tagCounter}
                    {assign var=TAG_LABEL value=$TAG_MODEL->getName()}
                    {include file='Tag.tpl'|vtemplate_path:$MODULE}
                {/foreach}
            </span>
        </span>
        <div id="dummyTagElement" class="hide">
            {include file=vtemplate_path('Tag.tpl',$MODULE) TAG_MODEL=Vtiger_Tag_Model::getCleanInstance()}
        </div>
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