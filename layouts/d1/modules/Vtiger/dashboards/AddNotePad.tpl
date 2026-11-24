{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div id="addNotePadWidgetContainer" class="modal-dialog modal-lg">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD', $MODULE)}|cat:" "|cat:{vtranslate('LBL_NOTEPAD', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" method="POST">
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="fieldLabel col-lg-3 col-form-label text-lg-end" for="notebookName">
                            <span>{vtranslate('LBL_NOTEPAD_NAME', $MODULE)}</span>
                            <span class="ms-2 text-danger">*</span>
                        </label>
                        <div class="fieldValue col-lg-9">
                            <input id="notebookName" type="text" name="notePadName" class="inputElement form-control" data-rule-required="true"/>
                        </div>
                    </div>
                    <div class="row">
                        <label class="fieldLabel col-lg-3 col-form-label text-lg-end" for="notebookCreateContent">
                            {vtranslate('LBL_NOTEPAD_CONTENT', $MODULE)}
                        </label>
                        <div class="fieldValue col-lg-9">
                            <textarea id="notebookCreateContent" class="form-control" name="notePadContent"></textarea>
                        </div>
                    </div>
                </div>

                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
