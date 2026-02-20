{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content" role="document" aria-labelledby="dfPremiumTitle">
            {include file='PremiumModalHeader.tpl'|@vtemplate_path:$MODULE}
            <div class="modal-body px-4 py-3">
                <div class="text-secondary">
                    <h3 class="mb-4 fw-bold">{vtranslate('LBL_TEAM_GROWING', $MODULE)}</h3>
                    <h5>{vtranslate('LBL_TEAM_ACTIVE_USER', $MODULE, $REACHED_USER_LIMIT_LICENSE->getUsersCount())}</h5>
                    <h5>{vtranslate('LBL_TEAM_LIMIT_USER', $MODULE, $REACHED_USER_LIMIT_LICENSE->getUsersLimit())}</h5>
                    <h5 class="mt-3">{vtranslate('LBL_TEAM_CONTINUE_ADDING_USER', $MODULE)}</h5>
                </div>
            </div>
            {include file='PremiumModalFooter.tpl'|@vtemplate_path:$MODULE CLOSE_LABEL='LBL_PREMIUM_CONTINUE' SUCCESS_LABEL='LBL_PREMIUM_BUY'}
        </div>
    </div>
{/strip}