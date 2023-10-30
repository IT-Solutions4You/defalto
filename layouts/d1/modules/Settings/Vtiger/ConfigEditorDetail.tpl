{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/ConfigEditorDetail.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="detailViewContainer px-4 pb-4" id="ConfigEditorDetails">
        <div class="bg-body rounded">
            {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
            <div class="contents ">
                <div class="container-fluid py-3 border-bottom">
                    <div class="row">
                        <h4 class="col-lg">{vtranslate('LBL_CONFIG_EDITOR', $QUALIFIED_MODULE)}</h4>
                        <div class="col-auto btn-group">
                            <button class="btn btn-outline-secondary editButton" data-url='{$MODEL->getEditViewUrl()}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</button>
                        </div>
                    </div>
                </div>
                <div class="detailViewInfo container-fluid py-3">
                    {assign var=FIELD_DATA value=$MODEL->getViewableData()}
                    {foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
                        <div class="row my-3 form-group">
                            <div class="col-sm-4 fieldLabel">
                                <label>{if $FIELD_NAME == 'upload_maxsize'}{if $FIELD_DATA[$FIELD_NAME] gt 5}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,$FIELD_DATA[$FIELD_NAME])}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,5)}{/if}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}{/if}</label>
                            </div>
                            <div  class="col-sm-8 fieldValue break-word">
                                <div>
                                    {if $FIELD_DETAILS['fieldType'] == 'checkbox'}
                                        {vtranslate($FIELD_DATA[$FIELD_NAME], $QUALIFIED_MODULE)}
                                        {if $FIELD_NAME == 'email_tracking'}
                                            <div class="input-info-addon">
                                                <a class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_PERSONAL_EMAIL_TRACKING_INFO',$QUALIFIED_MODULE)}"></a>
                                            </div>
                                        {/if}
                                    {elseif $FIELD_NAME == 'default_reply_to'}
                                        {vtranslate($FIELD_DATA[$FIELD_NAME])}
                                        <div class="input-info-addon">
                                            <a class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_DEFAULT_REPLY_TO_INFO',$QUALIFIED_MODULE)}"></a>
                                        </div>
                                    {else}
                                        {$FIELD_DATA[$FIELD_NAME]}
                                    {/if}
                                    {if $FIELD_NAME == 'upload_maxsize'}
                                        <span class="ms-2">{vtranslate('LBL_MB', $QUALIFIED_MODULE)}</span>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
{/strip}
