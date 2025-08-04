{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
     <div class="showAllTagContainer">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="detailShowAllModal">
                    {assign var=TITLE value=vtranslate('LBL_ADD_OR_SELECT_TAG',$MODULE,$RECORD_NAME)}
                    {include file='ModalHeader.tpl'|vtemplate_path:$MODULE}
                    <div class="modal-body">
                        <input type="hidden" name="deleteOldTags" value="{$DELETE_OLD_TAGS}" />
                        <div class="form-group">
                            <div class="text-secondary">
                                {vtranslate('LBL_CURRENT_TAGS',$MODULE)}
                            </div>
                            <div class="py-2 tagListContainer">
                                <select name="tagList" class="tagListSelect" multiple="multiple" id="tagList">
                                    {foreach item=TAG_MODEL from=$TAGS_LIST}
                                        <option value="{$TAG_MODEL->getId()}" {if $DELETE_OLD_TAGS}selected="selected"{/if}>{$TAG_MODEL->getName()}</option>
                                    {/foreach}
                                    {if isset($ALL_USER_TAGS)}
                                        {foreach item=TAG_MODEL from=$ALL_USER_TAGS}
                                            {if array_key_exists($TAG_MODEL->getId(), $TAGS_LIST)}
                                                {continue}
                                            {/if}
                                            <option value="{$TAG_MODEL->getId()}">{$TAG_MODEL->getName()}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="text-secondary">{vtranslate('LBL_CREATE_NEW_TAG',$QUALIFIED_MODULE)}</div>
                            <div class="py-2">
                                <input name="createNewTag" value="" class="form-control" placeholder="{vtranslate('LBL_ENTER_TAG_NAME',$QUALIFIED_MODULE)}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="d-flex align-items-center py-2">
                                <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                                <input type="checkbox" class="form-check-input m-0 h-13rem w-13rem" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}"/>
                                <span class="ms-2">{vtranslate('LBL_SHARE_TAGS',$QUALIFIED_MODULE)}</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <div class="p-3 vt-default-callout vt-info-callout tagInfoblock">
                                <h5 class="vt-callout-header">
                                    <span class="fa fa-info-circle"></span>
                                    <span class="ms-2">{vtranslate('Info', $QUALIFIED_MODULE)}</span>
                                </h5>
                                <div>{vtranslate('LBL_TAG_SEPARATOR_DESC', $QUALIFIED_MODULE)}</div>
                                <div>{vtranslate('LBL_SHARED_TAGS_ACCESS',$QUALIFIED_MODULE)}</div>
                                <div>{vtranslate('LBL_GOTO_TAGS', $QUALIFIED_MODULE)}</div>
                            </div>
                        </div>
                    </div>
                    {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
                </form>
            </div>
        </div>
    </div>
{/strip}