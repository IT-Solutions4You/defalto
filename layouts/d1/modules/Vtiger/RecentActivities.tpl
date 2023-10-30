{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=IMAGE_SIZE value='style="width: 3.5rem; height: 3.5rem;"'}
    <div class="recentActivitiesContainer container-fluid" id="updates">
        <div class="history rounded bg-body mt-3 py-3">
            <div class="history-data">
                <input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}"/>
                {if !empty($RECENT_ACTIVITIES)}
                    <ul class="updates_timeline p-0">
                        {foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
                            {assign var=PROCEED value= TRUE}
                            {if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
                                {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                                {if !($RELATION->getLinkedRecord())}
                                    {assign var=PROCEED value= FALSE}
                                {/if}
                            {/if}
                            {if $PROCEED}
                                {if $RECENT_ACTIVITY->isCreate()}
                                    <li class="row">
                                        <time class="col-3 update_time cursorDefault text-nowrap text-end py-2">
                                            <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getParent()->get('createdtime'))}">
                                                {Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getParent()->get('createdtime'))}
                                            </small>
                                        </time>
                                        {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                        {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                        {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].url eq ''}
                                            <div class="col-auto update_icon bg-info">
                                                <i class="rounded-circle update_image vicon-vtigeruser" {$IMAGE_SIZE}></i>
                                            </div>
                                        {else}
                                            {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                                {if !empty($IMAGE_INFO.url)}
                                                    <div class="col-auto update_icon">
                                                        <img class="rounded-circle update_image" {$IMAGE_SIZE} src="{$IMAGE_INFO.url}" >
                                                    </div>
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <div class="col-7 update_info py-2">
                                            <h5 class="fw-bold">
                                                <span class="field-name text-primary me-2">{$RECENT_ACTIVITY->getModifiedBy()->getName()}</span>
                                                <span>{vtranslate('LBL_CREATED', $MODULE_NAME)}</span>
                                            </h5>
                                        </div>
                                    </li>
                                {elseif $RECENT_ACTIVITY->isUpdate()}
                                    <li class="row">
                                        <time class="col-3 update_time cursorDefault text-nowrap text-end py-2">
                                            <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getActivityTime())}">
                                                {Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}
                                            </small>
                                        </time>
                                        {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                        {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                        {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].url eq ''}
                                            <div class="col-auto update_icon bg-info">
                                                <i class="rounded-circle update_image vicon-vtigeruser" {$IMAGE_SIZE}></i>
                                            </div>
                                        {else}
                                            {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                                {if !empty($IMAGE_INFO.url)}
                                                    <div class="col-auto update_icon">
                                                        <img class="rounded-circle update_image" {$IMAGE_SIZE} src="{$IMAGE_INFO.url}" >
                                                    </div>
                                                {/if}
                                            {/foreach}
                                        {/if}
                                        <div class="col-7 update_info py-2">
                                            <div>
                                                <h5 class="fw-bold">
                                                    <span class="field-name text-primary me-2">{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}</span>
                                                    <span>{vtranslate('LBL_UPDATED', $MODULE_NAME)}</span>
                                                </h5>
                                            </div>
                                            {foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
                                                {if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
                                                    <div class='font-x-small updateInfoContainer text-truncate'>
                                                        {assign var=FIELDMODEL_LABEL value=vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}
                                                        {if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
                                                            <div class='update-name'>
                                                                <span class="field-name text-primary">{$FIELDMODEL_LABEL}</span>
                                                                <span class="ms-2">{vtranslate('LBL_CHANGED')}</span>
                                                            </div>
                                                            <div class='update-from'>
                                                                <span class="field-name text-primary me-2">{vtranslate('LBL_FROM')}</span>
                                                                <em style="white-space:pre-line;" title="{strip_tags({Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))})}">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</em>
                                                            </div>
                                                        {elseif $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                            <div class='update-name'>
                                                                <span class="field-name text-primary">{$FIELDMODEL_LABEL}</span>
                                                                <span class="ms-2">(<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</del> ) {vtranslate('LBL_IS_REMOVED')}</span>
                                                            </div>
                                                        {elseif $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                            <div class='update-name'>
                                                                <span class="field-name text-primary">{$FIELDMODEL_LABEL}</span>
                                                                <span class="ms-2">{vtranslate('LBL_UPDATED')}</span>
                                                            </div>
                                                        {else}
                                                            <div class='update-name'>
                                                                <span class="field-name text-primary">{$FIELDMODEL_LABEL}</span>
                                                                <span class="ms-2">{vtranslate('LBL_CHANGED')}</span>
                                                            </div>
                                                        {/if}
                                                        {if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                            <div class="update-to">
                                                                <span class="field-name text-primary me-2">{vtranslate('LBL_TO')}</span>
                                                                <em style="white-space:pre-line;">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue'))))}</em>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                {/if}
                                            {/foreach}
                                        </div>
                                    </li>
                                {elseif ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
                                    {assign var=RELATED_MODULE value= $RELATION->getLinkedRecord()->getModuleName()}
                                    <li class="row">
                                        <time class="col-3 update_time cursorDefault text-nowrap text-end py-2">
                                            <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATION->get('changedon'))}">
                                                {Vtiger_Util_Helper::formatDateDiffInStrings($RELATION->get('changedon'))} </small>
                                        </time>
                                        <div class="col-auto">
                                            <div class="rounded-circle lh-base update_icon text-white bg-secondary" {$IMAGE_SIZE}>
                                                <div class="rounded-circle h-100 w-100 d-flex justify-content-center align-items-center bg-info-{$RELATED_MODULE|strtolower}">
                                                    {if {$RELATED_MODULE|strtolower eq 'modcomments'}}
                                                        {assign var="VICON_MODULES" value="vicon-chat"}
                                                        <i class="update_image {$VICON_MODULES}"></i>
                                                    {else}
                                                        <span class="update_image">{Vtiger_Module_Model::getModuleIconPath($RELATED_MODULE)}</span>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-7 update_info py-2">
                                            <h5 class="fw-bold">
                                                {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                                                <span class="field-name text-primary me-2">
                                                   {vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}
                                                </span>
                                                <span>
                                                    {if $RECENT_ACTIVITY->isRelationLink()}
                                                        {vtranslate('LBL_LINKED', $MODULE_NAME)}
                                                    {else}
                                                        {vtranslate('LBL_UNLINKED', $MODULE_NAME)}
                                                    {/if}
                                                </span>
                                            </h5>
                                            <div class='font-x-small updateInfoContainer text-truncate'>
                                                <span>
                                                    {if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
                                                        {if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'}
                                                            {assign var=PERMITTED value=1}
                                                        {else}
                                                            {assign var=PERMITTED value=0}
                                                        {/if}
                                                    {else}
                                                        {assign var=PERMITTED value=1}
                                                    {/if}
                                                    {if $PERMITTED}
                                                        {if $RELATED_MODULE eq 'ModComments'}
                                                            {$RELATION->getLinkedRecord()->getName()}
                                                        {else}
                                                            {assign var=DETAILVIEW_URL value=$RELATION->getRecordDetailViewUrl()}
                                                            {if $DETAILVIEW_URL}
                                                                <a {if stripos($DETAILVIEW_URL, 'javascript:') === 0}onclick{else}href{/if}='{$DETAILVIEW_URL}'>
                                                            {/if}
                                                            <span>{$RELATION->getLinkedRecord()->getName()}</span>
                                                            {if $DETAILVIEW_URL}
                                                                </a>
                                                            {/if}
                                                        {/if}
                                                    {/if}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                {elseif $RECENT_ACTIVITY->isRestore()}
                                {/if}
                            {/if}
                        {/foreach}
                        {if $PAGING_MODEL->isNextPageExists()}
                            <li id="more_button" class="row">
                                <div class="col-3"></div>
                                <div class="col-auto py-2">
                                    <div class="update_icon" id="moreLink">
                                        <button type="button" class="btn btn-primary moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
                                    </div>
                                </div>
                            </li>
                        {/if}
                    </ul>
                {else}
                    <div class="summaryWidgetContainer">
                        <p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/strip}
