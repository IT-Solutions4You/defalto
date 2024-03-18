{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {if $LINKEDTO}
        <div class="row">
            <div class="col-lg-7 recordScroll" >
                <div class="row">
                    <div class="col-lg-1">
                        <input type="radio" name="_mlinkto" value="{$LINKEDTO.record}" class="form-check-input">
                    </div>
                    <div class="col-lg-11 mmRelatedRecordDesc text-truncate" title="{$LINKEDTO.detailviewlink}">
                        <span class="me-2">{$LINKEDTO.detailviewlink}</span>
                        <span>({vtranslate($LINKEDTO.module, $moduleName)})</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-end">
                {if $LINK_TO_AVAILABLE_ACTIONS|count neq 0}
                    <select name="_mlinktotype"  id="_mlinktotype" data-action="associate" class="form-select">
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
        {assign var="LOOKRECATLEASTONE" value=false}
        {foreach item=RECORDS key=MODULE from=$LOOKUPS}
            {foreach item=RECORD from=$RECORDS}
                {assign var="LOOKRECATLEASTONE" value=true}
            {/foreach}
        {/foreach}
        <div class="row">
            <div class="col-lg-7 recordScroll" >
                {foreach item=RECORDS key=MODULE from=$LOOKUPS}
                    {foreach item=RECORD from=$RECORDS}
                        <div class="row">
                            <div class="col-lg-1">
                                <input type="radio" name="_mlinkto" value="{$RECORD.id}" class="form-check-input">
                            </div>
                            <div class="col-lg-11 text-truncate mmRelatedRecordDesc">
                                <a target="_blank" href="index.php?module={$MODULE}&view=Detail&record={$RECORD.id}" title="{$RECORD.label}">
                                    <span>{$RECORD.label|textlength_check}</span>
                                    {assign var="SINGLE_MODLABEL" value="SINGLE_$MODULE"}
                                    <span class="ms-2">({vtranslate($SINGLE_MODLABEL, $MODULE)})</span>
                                </a>
                            </div>
                        </div>
                        <br>
                    {/foreach}
                {/foreach}
            </div>
            <div class="pull-left col-lg-5 ">
                {if $LOOKRECATLEASTONE}
                    {if $LINK_TO_AVAILABLE_ACTIONS|count neq 0}
                        <select name="_mlinktotype"  id="_mlinktotype" data-action="associate" class="form-select">
                            <option value="">{vtranslate('LBL_ACTIONS',$MODULE)}</option>
                            {foreach item=moduleName from=$LINK_TO_AVAILABLE_ACTIONS}
                                <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                            {/foreach}
                        </select>
                    {/if}
                {else}
                    {if $ALLOWED_MODULES|count neq 0}
                        <select name="_mlinktotype"  id="_mlinktotype" data-action="create" class="form-select">
                            <option value="">{vtranslate('LBL_ACTIONS','MailManager')}</option>
                            {foreach item=moduleName from=$ALLOWED_MODULES}
                                <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                            {/foreach}
                        </select>
                    {/if}
                {/if}
            </div>
        </div>
    {else}
        {if $LINKEDTO eq ""}
            <div class="row">
                <div class="col-lg-7 recordScroll">

                </div>
                <div class="col-lg-5">
                    {if $ALLOWED_MODULES|count neq 0}
                        <select name="_mlinktotype"  id="_mlinktotype" data-action="create" class="form-select" >
                            <option value="">{vtranslate('LBL_ACTIONS','MailManager')}</option>
                            {foreach item=moduleName from=$ALLOWED_MODULES}
                                <option value="{$moduleName}">{vtranslate("LBL_MAILMANAGER_ADD_$moduleName", 'MailManager')}</option>
                            {/foreach}
                        </select>
                    {/if}
                </div>
            </div>
        {/if}
    {/if}
{/strip}