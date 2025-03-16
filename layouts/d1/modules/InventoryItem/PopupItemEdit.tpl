<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE,$MODULE)}}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv col-sm-12">
                hoy!
                <form id="InventoryItemPopupForm">
                    <input type="hidden" name="module" value="{$MODULE}" />
                </form>
            </div>
        </div>
    </div>
</div>
