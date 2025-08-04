{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Vtiger/resources/ExtensionCommon.js')}"></script>
<div class='fc-overlay-modal modal-content'>
    <div class="overlayHeader">
        {assign var=TITLE value={vtranslate('LBL_IMPORT_RESULTS_GOOGLE',$MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    </div>
    <div class="modal-body" style = "margin-bottom:450px">
        {include file="ExtensionListLog.tpl"|vtemplate_path:$MODULE}
    </div>
</div>