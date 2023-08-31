{strip}
<div class="listViewTagsTemplate">
    <div id="listViewTagContainer" class="tagContainer multiLevelTagList" {if $ALL_CUSTOMVIEW_MODEL} data-view-id="{$ALL_CUSTOMVIEW_MODEL->getId()}" {/if} data-list-tag-count="{Vtiger_Tag_Model::NUM_OF_TAGS_LIST}">
        {assign var=ALL_CUSTOM_VIEW_ID value=CustomView_Record_Model::getAllFilterByModule($MODULE)->get('cvid')}
        <div class="tag btn mb-2 me-1 {if $VIEWNAME eq $ALL_CUSTOM_VIEW_ID}btn-primary active{else}text-primary bg-primary bg-opacity-10{/if}" data-cv-id="{$ALL_CUSTOM_VIEW_ID}" data-id="">
            <i class="fa fa-list"></i>
            <span class="mx-2">{vtranslate('LBL_ALL', $MODULE)}</span>
        </div>
        {assign var=DEFAULT_CUSTOM_VIEW value=CustomView_Record_Model::getDefaultFilterByModule($MODULE)}
        {assign var=DEFAULT_CUSTOM_VIEW_ID value=$DEFAULT_CUSTOM_VIEW->get('cvid')}
        {if $ALL_CUSTOM_VIEW_ID neq $DEFAULT_CUSTOM_VIEW_ID}
            <div class="tag btn mb-2 me-1 {if $VIEWNAME eq $DEFAULT_CUSTOM_VIEW_ID}btn-primary active{else}text-primary bg-primary bg-opacity-10{/if}" data-cv-id="{$DEFAULT_CUSTOM_VIEW_ID}" data-id="">
                <i class="fa fa-list"></i>
                <span class="mx-2">{vtranslate($DEFAULT_CUSTOM_VIEW->get('viewname'), $MODULE)}</span>
            </div>
        {/if}
        {if $VIEWNAME and $VIEWNAME neq $ALL_CUSTOM_VIEW_ID and $VIEWNAME neq $DEFAULT_CUSTOM_VIEW_ID}
            {assign var=CURRENT_CUSTOM_VIEW value=CustomView_Record_Model::getInstanceById($VIEWNAME)}
            {assign var=CURRENT_CUSTOM_VIEW_ID value=$CURRENT_CUSTOM_VIEW->get('cvid')}
            <div class="tag btn mb-2 me-1 {if $VIEWNAME eq $CURRENT_CUSTOM_VIEW_ID}btn-primary active{else}text-primary bg-primary bg-opacity-10{/if}" data-cv-id="{$CURRENT_CUSTOM_VIEW_ID}" data-id="">
                <i class="fa fa-list"></i>
                <span class="mx-2">{vtranslate($CURRENT_CUSTOM_VIEW->get('viewname'), $MODULE)}</span>
            </div>
        {/if}
        {foreach item=TAG_MODEL from=$TAGS name=tagCounter}
            {assign var=TAG_ID value=$TAG_MODEL->getId()}
            {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true ACTIVE=$CURRENT_TAG eq $TAG_ID}
        {/foreach}
    </div>
    {include file="AddTagUI.tpl"|vtemplate_path:$MODULE RECORD_NAME="" TAGS_LIST=array()}
    <div id="dummyTagElement" class="hide">
        {assign var=TAG_MODEL value=Vtiger_Tag_Model::getCleanInstance()}
        {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true}
    </div>
    <div>
        <div class="editTagContainer hide">
            <input type="hidden" name="id" value=""/>
            <div class="editTagContents">
                <div>
                    <input type="text" name="tagName" value="" class="form-control"/>
                </div>
                <div>
                    <div class="checkbox my-2">
                        <label>
                            <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                            <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}"/>
                            &nbsp; {vtranslate('LBL_SHARE_TAG',$MODULE)}
                        </label>
                    </div>
                </div>
            </div>
            <div class="input-group">
                <button class="btn btn-success saveTag w-50" type="button">
                    <i class="fa fa-check"></i>
                </button>
                <button class="btn btn-danger cancelSaveTag w-50" type="button">
                    <i class="fa fa-close"></i>
                </button>
            </div>
        </div>
    </div>
</div>
{/strip}