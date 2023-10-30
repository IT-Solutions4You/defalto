{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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