{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Rss/views/ViewTypes.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class='modal-dialog' id="rssAddFormUi">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_FEED_SOURCE', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="rssAddForm" method="post" action="index.php">
                <div class="modal-body">
                    <div class="fieldLabel mb-3">
                        <label>
                            {vtranslate('LBL_FEED_SOURCE',$MODULE)}&nbsp;<span class="redColor">*</span>
                        </label>
                    </div>
                    <div class="fieldValue">
                        <input class="form-control" type="text" id="feedurl" name="feedurl" data-rule-required="true" data-rule-url="true" value="" placeholder="{vtranslate('LBL_ENTER_FEED_SOURCE',$MODULE)}"/>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
