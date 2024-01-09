{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="modal-dialog modelContainer">
        <div class="modal-content" style="width:675px;">
            {assign var=HEADER_TITLE value=vtranslate('LBL_PRODUCT_IMAGE', $MODULE_NAME)}
            {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="modal-body">
                <form id="SaveProductImagesForm" class="form-horizontal" name="upload" method="post" action="index.php">
                    <input type="hidden" name="module" value="{$MODULE_NAME}"/>
                    <input type="hidden" name="action" value="SaveAjax"/>
                    <input type="hidden" name="mode" value="SavePDFImages"/>
                    <input type="hidden" name="return_id" value="{$RECORD}"/>
                    {$IMG_HTML}
                </form>
            </div>
            {assign var=BUTTON_NAME value={vtranslate('LBL_SAVE', $MODULE)}}
            {assign var=BUTTON_ID value='js-save-button'}
            {include file='ModalFooter.tpl'|vtemplate_path:$MODULE}
        </div>
    </div>
{/strip}
