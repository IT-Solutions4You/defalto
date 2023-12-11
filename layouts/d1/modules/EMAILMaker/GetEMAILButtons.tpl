{*/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/*}
{strip}
    {if $ENABLE_EMAILMAKER eq 'true'}
        <div class="btn-group ms-2" id="EMAILMakerContentDiv" style="padding-left: 5px;">
            <div class="btn-group">
                <button class="btn btn-outline-secondary selectEMAILTemplates">
                    <i title="{vtranslate('LBL_SEND_EMAILMAKER_EMAIL','EMAILMaker')}" class="fa fa-envelope-o" aria-hidden="true"></i>
                    <span class="ms-2">{vtranslate('LBL_SEND_EMAILMAKER_EMAIL','EMAILMaker')}</span>
                </button>
            </div>
        </div>
    {/if}
{/strip}