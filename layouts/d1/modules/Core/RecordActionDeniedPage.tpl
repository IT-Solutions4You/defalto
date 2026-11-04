{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="recordActionDeniedPage container-fluid p-3">
        <div class="bg-body rounded p-4">
            <h4 class="fw-bold mb-3">{vtranslate('LBL_RECORD_ACTION_DENIED_TITLE', 'Core')}</h4>
            <div class="recordActionDenied alert alert-warning">
                <i class="fa-solid fa-lock me-2"></i>{$DENY_REASON|escape}
            </div>
            <a class="btn btn-outline-primary" href="javascript:history.back()">{vtranslate('LBL_GO_BACK', 'Core')}</a>
        </div>
    </div>
{/strip}
