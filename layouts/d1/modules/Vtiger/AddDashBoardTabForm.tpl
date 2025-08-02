{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modelContainer">
        <form class="modal-content" id="AddDashBoardTab" name="AddDashBoardTab" method="post" action="index.php">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_ADD_DASHBOARD', $MODULE)}
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="DashBoardTab"/>
            <input type="hidden" name="mode" value="addTab"/>
            <div class="modal-body clearfix">
                <div class="row py-2">
                    <div class="col-lg-5 text-end">
                        <label class="control-label">
                            {vtranslate('LBL_TAB_NAME',$MODULE)}&nbsp;<span class="redColor">*</span>
                        </label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" name="tabName" data-rule-required="true" size="25" class="inputElement form-control" maxlength='30'/>
                    </div>
                </div>
                <div class="row py-2">
                    <div class="col-lg-12">
                        <div class="alert-info text-center">
                            <i class="fa fa-info-circle"></i>
                            <span class="ms-2">{vtranslate('LBL_MAX_CHARACTERS_ALLOWED_DASHBOARD', $MODULE)}</span>
                        </div>
                    </div>
                </div>
            </div>
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </form>
    </div>
{/strip}
