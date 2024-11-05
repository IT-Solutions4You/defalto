{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {if $LINKEDTO}
        <div class="row mailManagerLinkedTo">
            <div class="col-8 recordScroll mb-1">
                <div class="list-group overflow-auto h-25vh-max">
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col-1">
                                <input type="radio" name="_mlinkto" value="{$LINKEDTO.record}" class="form-check-input" checked="checked">
                            </div>
                            <div class="col text-truncate mmRelatedRecordDesc">
                                <a target="_blank" class="link-primary" href="{$LINKEDTO.url}">
                                    {$LINKEDTO.icon}
                                    <span class="ms-2">{$LINKEDTO.label}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {if $EMAIL}
                        {$EMAIL->displayed($LINKEDTO.record)}
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getRelationsById($LINKEDTO.record) RELATION_MARGIN=true}
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getOtherRelations() RELATION_MARGIN=false}
                    {/if}
                </div>
            </div>
            <div class="col text-end">
                {if $LINK_TO_AVAILABLE_ACTIONS|count}
                    {include file="RelationshipActions.tpl"|vtemplate_path:$MODULE ACTION_MODULES=$LINK_TO_AVAILABLE_ACTIONS ACTION_TYPE='associate'}
                {/if}
            </div>
        </div>
    {elseif $LOOKUPS}
        {assign var=LOOKRECATLEASTONE value=false}
        {foreach item=RECORDS key=MODULE from=$LOOKUPS}
            {foreach item=RECORD from=$RECORDS}
                {assign var=LOOKRECATLEASTONE value=true}
            {/foreach}
        {/foreach}
        <div class="row mailManagerLookUps">
            <div class="col recordScroll mb-1">
                <ul class="list-group overflow-auto h-25vh-max">
                    {foreach item=RECORDS key=RECORD_MODULE from=$LOOKUPS}
                        {foreach item=RECORD from=$RECORDS}
                            <li class="list-group-item" title="{vtranslate($RECORD_MODULE, $RECORD_MODULE)}">
                                <div class="row">
                                    <div class="col-1">
                                        <input type="radio" name="_mlinkto" value="{$RECORD.id}" class="form-check-input">
                                    </div>
                                    <div class="col text-truncate mmRelatedRecordDesc">
                                        <a target="_blank" class="link-primary" href="{$RECORD.url}" title="{$RECORD.label}">
                                            {$RECORD.icon}
                                            <span class="ms-2">{$RECORD.label|textlength_check}</span>
                                        </a>
                                        {if !empty($RECORD['account_label'])}
                                            <span class="ms-2 text-secondary">({$RECORD['account_label']})</span>
                                        {/if}
                                    </div>
                                </div>
                            </li>
                            {if $EMAIL}
                                {$EMAIL->displayed($RECORD.id)}
                                {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getRelationsById($RECORD.id) RELATION_MARGIN=true}
                            {/if}
                        {/foreach}
                    {/foreach}
                    {if $EMAIL}
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getOtherRelations() RELATION_MARGIN=false}
                    {/if}
                </ul>
            </div>
            <div class="col-auto">
                {if $LOOKRECATLEASTONE}
                    {if $LINK_TO_AVAILABLE_ACTIONS|count}
                        {include file="RelationshipActions.tpl"|vtemplate_path:$MODULE ACTION_MODULES=$LINK_TO_AVAILABLE_ACTIONS ACTION_TYPE='associate'}
                    {/if}
                {elseif $ALLOWED_MODULES|count}
                    {include file="RelationshipActions.tpl"|vtemplate_path:$MODULE ACTION_MODULES=$ALLOWED_MODULES ACTION_TYPE='create'}
                {/if}
            </div>
        </div>
    {elseif empty($LINKEDTO)}
        <div class="row mailManagerEmptyLinkedTo">
            <div class="col recordScroll mb-1">
                {if $ALLOWED_MODULES|count}
                    {include file="RelationshipActions.tpl"|vtemplate_path:$MODULE ACTION_MODULES=$ALLOWED_MODULES ACTION_TYPE='create' ACTION_BUTTONS=true}
                {/if}
                {if false eq $EMAIL->isAttachmentsAllowed()}
                    <div class="alert alert-warning d-flex align-items-center justify-content-between py-2 my-3">
                        <span>{vtranslate('LBL_BLOCKED_REMOTE_CONTENT', $MODULE)}</span>
                        <button type="button" class="btn btn-warning allowRemoteContent">{vtranslate('LBL_ALLOW', $MODULE)}</button>
                    </div>
                {/if}
            </div>
        </div>
    {/if}
{/strip}