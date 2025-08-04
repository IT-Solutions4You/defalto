{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div id="addNotePadWidgetContainer" class='modal-dialog'>
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD', $MODULE)}|cat:" "|cat:{vtranslate('LBL_NOTEPAD', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" method="POST">
                <div class="row py-2">
                    <label class="fieldLabel col-lg-4 text-end">
                        <label>
                            <span>{vtranslate('LBL_NOTEPAD_NAME', $MODULE)}</span>
                            <span class="ms-2 text-danger">*</span>
                        </label>
                    </label>
                    <div class="fieldValue col-lg-6">
                        <input type="text" name="notePadName" class="inputElement form-control" data-rule-required="true"/>
                    </div>
                </div>
                <div class="row py-2">
                    <label class="fieldLabel col-lg-4">
                        <label class="pull-right">{vtranslate('LBL_NOTEPAD_CONTENT', $MODULE)}</label>
                    </label>
                    <div class="fieldValue col-lg-6">
                        <textarea class="form-control" type="text" name="notePadContent"></textarea>
                    </div>
                </div>

                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}