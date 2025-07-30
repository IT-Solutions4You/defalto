{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div style="display: none;">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/foreach}
</div>