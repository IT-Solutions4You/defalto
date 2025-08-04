{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="detailViewContainer px-4 pb-4">
        <div class="col-sm-12">
            {include file='DetailViewHeader.tpl'|vtemplate_path:$QUALIFIED_MODULE MODULE_NAME=$MODULE_NAME}
            {include file='DetailViewBlockView.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
            {include file='FieldsDetailView.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
        </div>
    </div>
    </div>
    </div>
{/strip}