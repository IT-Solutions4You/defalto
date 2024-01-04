{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
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