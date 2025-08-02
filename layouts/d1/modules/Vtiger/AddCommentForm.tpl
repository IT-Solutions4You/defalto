{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Vtiger/views/MassActionAjax.php *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
<div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="massSave" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="action" value="MassSaveAjax" />
            <input type="hidden" name="viewname" value="{$CVID}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <input type="hidden" name="search_params" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SEARCH_PARAMS))}' />
            <input type="hidden" name="tag_params" value={ZEND_JSON::encode($TAG_PARAMS)}>

            {assign var=HEADER_TITLE value={vtranslate('LBL_ADDING_COMMENT', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row commentTextArea" id="mass_action_add_comment">
                        <textarea class="col-lg-12" name="commentcontent" id="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" placeholder="{vtranslate('LBL_WRITE_YOUR_COMMENT_HERE', $MODULE)}..." data-rule-required="true"></textarea>
                    </div>
                </div>
            </div>
			{include file='AddCommentFooter.tpl'|@vtemplate_path:$MODULE}
       </form>
    </div>
</div>

