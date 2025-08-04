{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="viewAllTagsContainer">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_TAG_FOR',$MODULE,$RECORD_NAME)}
            <div class="modal-body detailShowAllModal">
                <div class="form-group">
                    <label class="col-12 control-label">
                        {vtranslate('LBL_CURRENT_TAGS',$MODULE)}
                    </label>
                    <div class="col-12">
                        <div class="currentTag multiLevelTagList form-control">
                            {foreach item=TAG_MODEL from=$TAGS_LIST}
                                {include file=vtemplate_path('Tag.tpl',$MODULE) NO_EDIT=true}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>