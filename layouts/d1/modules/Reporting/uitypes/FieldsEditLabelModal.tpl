{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modal-lg modelContainer fieldsEditLabelModal">
    <div class="modal-content">
        <div class="modal-content form-horizontal">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_EDIT_LABEL', $QUALIFIED_MODULE)}
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row py-2 selectFieldLabelContainer">
                        <div class="col-lg-3">
                            {vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}
                        </div>
                        <div class="col-lg">
                            <input type="hidden" class="selectedFieldName">
                            <input type="text" class="selectedFieldLabel form-control">
                        </div>
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|vtemplate_path:$QUALIFIED_MODULE BUTTON_ID='changeLabelsButton' BUTTON_NAME=vtranslate('LBL_UPDATE', $QUALIFIED_MODULE)}
        </div>
    </div>
</div>