{*/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/*}
{strip}
    {if $ENABLE_EMAILMAKER eq 'true'}
        <div class="pull-right" id="EMAILMakerContentDiv" style="padding-left: 5px;">
            <div class="clearfix">
                <div class="btn-group pull-right">
                    <button class="btn btn-default selectEMAILTemplates">
                        <i title="{vtranslate('LBL_SEND_EMAILMAKER_EMAIL','EMAILMaker')}" class="fa fa-envelope-o" aria-hidden="true"></i>&nbsp;{vtranslate('LBL_SEND_EMAILMAKER_EMAIL','EMAILMaker')}
                    </button>
                </div>
            </div>
        </div>
    {/if}
{/strip}