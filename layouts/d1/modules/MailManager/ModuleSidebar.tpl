{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{strip}
    <div class="col-lg-2 p-0 bg-body rounded-end rounded-bottom-0 mmFoldersContainer">
        <div id="modules-menu" class="modules-menu mmModulesMenu p-3 h-100">
            <div class="d-flex justify-content-between pb-3">
                <div class="fw-bold text-truncate">{$MAILBOX->username()}</div>
                <div class="d-flex">
                    <button class="btn btn-outline-secondary mailbox_refresh me-2" title="{vtranslate('LBL_Refresh', $MODULE)}">
                        <i class="fa fa-refresh"></i>
                    </button>
                    <button class="btn btn-outline-secondary mailbox_setting" title="{vtranslate('JSLBL_Settings', $MODULE)}">
                        <i class="fa fa-cog"></i>
                    </button>
                </div>
            </div>
            <div id="folders_list"></div>
        </div>
    </div>
{/strip}