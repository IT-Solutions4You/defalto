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
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getRelationsById($LINKEDTO.record)}
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getOtherRelations()}
                    {/if}
                </div>
            </div>
            <div class="col text-end">
                {if $LINK_TO_AVAILABLE_ACTIONS|count}
                    <select name="_mlinktotype" id="_mlinktotype" data-action="associate" class="form-select">
                        <option value="">{vtranslate('LBL_ACTIONS',$MODULE)}</option>
                        {foreach item=moduleName from=$LINK_TO_AVAILABLE_ACTIONS}
                            <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        </div>
    {/if}
    {if $LOOKUPS}
        {assign var=LOOKRECATLEASTONE value=false}
        {foreach item=RECORDS key=MODULE from=$LOOKUPS}
            {foreach item=RECORD from=$RECORDS}
                {assign var=LOOKRECATLEASTONE value=true}
            {/foreach}
        {/foreach}
        <div class="row mailManagerLookUps">
            <div class="col-8 recordScroll mb-1">
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
                                    </div>
                                </div>
                            </li>
                            {if $EMAIL}
                                {$EMAIL->displayed($RECORD.id)}
                                {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getRelationsById($RECORD.id)}
                            {/if}
                        {/foreach}
                    {/foreach}
                    {if $EMAIL}
                        {include file="RelationshipRecords.tpl"|vtemplate_path:$MODULE RELATION_RECORDS=$EMAIL->getOtherRelations()}
                    {/if}
                </ul>
            </div>
            <div class="col">
                {if $LOOKRECATLEASTONE}
                    {if $LINK_TO_AVAILABLE_ACTIONS|count}
                        <select name="_mlinktotype" id="_mlinktotype" data-action="associate" class="form-select">
                            <option value="">{vtranslate('LBL_ACTIONS',$MODULE)}</option>
                            {foreach item=moduleName from=$LINK_TO_AVAILABLE_ACTIONS}
                                <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                            {/foreach}
                        </select>
                    {/if}
                {elseif $ALLOWED_MODULES|count}
                    <select name="_mlinktotype" id="_mlinktotype" data-action="create" class="form-select">
                        <option value="">{vtranslate('LBL_ACTIONS',$MODULE)}</option>
                        {foreach item=moduleName from=$ALLOWED_MODULES}
                            <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        </div>
    {elseif empty($LINKEDTO)}
        <div class="row mailManagerEmptyLinkedTo">
            <div class="col-8 recordScroll mb-1"></div>
            <div class="col">
                {if $ALLOWED_MODULES|count}
                    <select name="_mlinktotype" id="_mlinktotype" data-action="create" class="form-select">
                        <option value="">{vtranslate('LBL_ACTIONS',$MODULE)}</option>
                        {foreach item=moduleName from=$ALLOWED_MODULES}
                            <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                        {/foreach}
                    </select>
                {/if}
            </div>
        </div>
    {/if}
{/strip}