{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog">
        <div class="modal-content">
            {include file='ModalHeader.tpl'|vtemplate_path:'Vtiger'}
            <div class="modal-body">
                <div class="recordActionDenied alert alert-warning mb-0">
                    <i class="fa-solid fa-lock me-2"></i>{$DENY_REASON|escape}
                </div>
            </div>
        </div>
    </div>
{/strip}
