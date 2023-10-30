{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="px-4 pb-4">
        <div class="editViewPageDiv bg-body rounded">
            <form class="form-horizontal" id="EditView" name="edit" method="post" action="index.php" enctype="multipart/form-data">
                <div class="editViewHeader p-3 border-bottom">
                    {assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
                    {if $RECORD_ID neq ''}
                        <h3 class="editHeader m-0" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}</h3>
                    {else}
                        <h3 class="editHeader m-0">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</h3>
                    {/if}
                </div>
                <div class="editViewBody">
                    <div class="editViewContents">
                        {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                        {assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
                        {assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
                        {if $IS_PARENT_EXISTS}
                            {assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
                            <input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}"/>
                            <input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}"/>
                        {else}
                            <input type="hidden" name="module" value="{$MODULE}"/>
                        {/if}
                        <input type="hidden" name="action" value="Save"/>
                        <input type="hidden" name="record" value="{$RECORD_ID}"/>
                        <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}"/>
                        <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}"/>
                        {if $IS_RELATION_OPERATION }
                            <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
                            <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
                            <input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}"/>
                        {/if}
                        {if $RETURN_VIEW}
                            <input type="hidden" name="returnmodule" value="{$RETURN_MODULE}"/>
                            <input type="hidden" name="returnview" value="{$RETURN_VIEW}"/>
                            <input type="hidden" name="returnrecord" value="{$RETURN_RECORD}"/>
                            <input type="hidden" name="returntab_label" value="{$RETURN_RELATED_TAB}"/>
                            <input type="hidden" name="returnrelatedModule" value="{$RETURN_RELATED_MODULE}"/>
                            <input type="hidden" name="returnpage" value="{$RETURN_PAGE}"/>
                            <input type="hidden" name="returnviewname" value="{$RETURN_VIEW_NAME}"/>
                            <input type="hidden" name="returnsearch_params" value='{Vtiger_Functions::jsonEncode($RETURN_SEARCH_PARAMS)}'/>
                            <input type="hidden" name="returnsearch_key" value={$RETURN_SEARCH_KEY}/>
                            <input type="hidden" name="returnsearch_value" value={$RETURN_SEARCH_VALUE}/>
                            <input type="hidden" name="returnoperator" value={$RETURN_SEARCH_OPERATOR}/>
                            <input type="hidden" name="returnsortorder" value={$RETURN_SORTBY}/>
                            <input type="hidden" name="returnorderby" value="{$RETURN_ORDERBY}"/>
                            <input type="hidden" name="returnmode" value={$RETURN_MODE}/>
                            <input type="hidden" name="returnrelationId" value="{$RETURN_RELATION_ID}"/>
                        {/if}
                        {include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
                        <div class="targetFieldsTableContainer">
                            {include file="FieldsEditView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-3">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col text-end">
                                <a class="btn btn-primary cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col">
                                <button type='submit' class='btn btn-primary active saveButton'>{vtranslate('LBL_SAVE', $MODULE)}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}