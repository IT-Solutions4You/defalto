{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modal-lg">
    <form method="post" class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_LICENSE', $QUALIFIED_MODULE)}
        <div class="modal-body">
            <div class="row align-items-center">
                <div class="col-4 text-end text-secondary">{vtranslate('LBL_LICENSE_KEY', $QUALIFIED_MODULE)}</div>
                <div class="col">
                    <input name="module" type="hidden" value="Installer">
                    <input name="view" type="hidden" value="IndexAjax">
                    <input name="mode" type="hidden" value="licenseSave">
                    <input name="license_id" type="hidden" value="{$LICENSE_MODEL->getId()}">
                    <input name="license_name" type="text" class="form-control" value="{$LICENSE_MODEL->getName()}">
                </div>
            </div>
        </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </form>
</div>