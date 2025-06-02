{* ********************************************************************************
 * The content of this file is subject to the ITS4YouInstaller license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** *}
{strip}
    <div class="contents" id="requirementsContents">
        {include file="SourceModules.tpl"|vtemplate_path:$MODULE}
        <div class="requirements-container">
            {if $REQUIREMENTS}
                <h5><b>{vtranslate('LBL_REQUIREMENTS_FOR', $QUALIFIED_MODULE)} {$REQUIREMENTS->getModuleLabel()}</b></h5>
                <hr>
                {foreach from=$REQUIREMENT_VALIDATIONS item=VALIDATION}
                    {assign var=REQUIREMENTS_INFO value=$REQUIREMENTS->getDataFromFunction($VALIDATION['function'])}
                    {if !$REQUIREMENTS_INFO}{continue}{/if}
                    <div>
                        {assign var=HEADERS value=$REQUIREMENTS->getHeaders($VALIDATION['type'])}
                        <h5 class="my-3">{vtranslate($VALIDATION['label'], $QUALIFIED_MODULE)}</h5>
                        <div class="container-fluid border">
                            <div class="row py-2 border-bottom">
                                <div class="col"></div>
                                {foreach from=$HEADERS key=HEADER_LABEL item=HEADER_NAME}
                                    <div class="col fw-bold header-{$HEADER_NAME}">{vtranslate($HEADER_LABEL, $QUALIFIED_MODULE)}</div>
                                {/foreach}
                                <div class="col"></div>
                            </div>
                            {foreach from=$REQUIREMENTS_INFO item=REQUIREMENTS_DATA}
                                <div class="row py-2 border-top {if $REQUIREMENTS_DATA['validate']}text-success noError {else}text-danger yesError{/if}">
                                    <div class="col">
                                        {if $REQUIREMENTS_DATA['validate']}
                                            <i class="fa fa-check"></i>
                                        {else}
                                            <i class="fa fa-times"></i>
                                        {/if}
                                    </div>
                                    {foreach from=$HEADERS key=HEADER_LABEL item=HEADER_NAME}
                                        <div class="col text-break-all custom-link-{$HEADER_NAME}">{$REQUIREMENTS_DATA[$HEADER_NAME]}</div>
                                    {/foreach}
                                    <div class="col text-break-all">
                                        {vtranslate($REQUIREMENTS_DATA['validate_message'], $QUALIFIED_MODULE)}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
{/strip}
