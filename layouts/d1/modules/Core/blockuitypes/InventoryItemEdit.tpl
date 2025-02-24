<div {*style="display: none;"*}>
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/foreach}
</div>