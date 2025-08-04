{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modelContainer mailSentSuccessfully">
    <div class="modal-content" style="width:800px;">
        {assign var=HEADER_TITLE value=$MESSAGE.title}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="padding15px">
            <div class="widgetContainer_1">
                <div class="widget_contents" id="popup_notifi_content">{$MESSAGE.content}</div>
            </div>
        </div>
    </div>
</div>