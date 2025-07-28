{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 *}

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="{vtranslate($MODULE,$MODULE)}"}
        <div class="modal-body">
            {if $ITEM_TYPE eq ''}
                {include file="PopupTextEdit.tpl"|vtemplate_path:$MODULE}
            {else}
                {include file="PopupItemEdit.tpl"|vtemplate_path:$MODULE}
            {/if}
        </div>
        <div class="modal-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-6"><a href="#" class="btn btn-primary cancelLink" type="reset">{vtranslate('LBL_CANCEL')}</a></div>
                    <div class="col-6 text-end">
                        <button class="btn btn-primary active saveButton" name="saveButton"><strong>{vtranslate('LBL_SAVE')}</strong></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
