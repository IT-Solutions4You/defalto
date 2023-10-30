{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var="MASS_EDITION_MODE" value=true}
    <div id="massEditContainer">
        <form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="MassSave"/>
            <input type="hidden" name="viewname" value="{$CVID}"/>
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" name="tag_params" value={ZEND_JSON::encode($TAG_PARAMS)}>
            <input type="hidden" name="search_params" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SEARCH_PARAMS))}'/>
            <div class="fc-overlay-modal modal-content overlayEdit border-0">
                {assign var=TITLE value=vtranslate('LBL_MASS_EDITING',$MODULE)}
                {include file=vtemplate_path('ModalHeader.tpl', $MODULE) TITLE=$TITLE}
                <div class="modal-body datacontent editViewContents overflow-auto">
                    {include file=vtemplate_path('partials/EditViewContents.tpl', $MODULE)}
                </div>
                <div class="modal-footer overlayFooter d-flex justify-content-between">
                    <a class='cancelLink btn btn-primary' data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    <button type='submit' class='btn btn-primary active saveButton'>{vtranslate('LBL_SAVE', $MODULE)}</button>
                </div>
            </div>
        </form>
    </div>
{/strip}