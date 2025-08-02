{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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