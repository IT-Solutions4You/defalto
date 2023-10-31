{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="col-lg-2 p-0 bg-body rounded-end rounded-bottom-0">
        <div id="modules-menu" class="modules-menu mmModulesMenu p-3">
            <div class="d-flex justify-content-between">
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
            <div class="my-3">
                <div id="mail_compose" class="btn btn-primary">
                    <i class="fa fa-pencil-square-o"></i>
                    <span class="ms-2">{vtranslate('LBL_Compose', $MODULE)}</span>
                </div>
            </div>
            <div id='folders_list'></div>
        </div>
    </div>
{/strip}