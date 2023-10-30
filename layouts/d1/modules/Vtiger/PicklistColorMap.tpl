{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<style type="text/css">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$LISTVIEW_HEADERS}
        {if $FIELD_MODEL->getFieldDataType() eq 'picklist' or $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
            {assign var=FIELD_NAME value=$FIELD_MODEL->get('_name')}
            {if $FIELD_NAME eq ''}
                {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
            {/if}
            {assign var=PICKLIST_COLOR_MAP value=Settings_Picklist_Module_Model::getPicklistColorMap($FIELD_NAME, true)}
            {foreach item=PICKLIST_COLOR key=PICKLIST_VALUE from=$PICKLIST_COLOR_MAP}
                {assign var=PICKLIST_TEXT_COLOR value= decode_html(Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR))}
                {assign var=CONVERTED_PICKLIST_VALUE value=Vtiger_Util_Helper::convertSpaceToHyphen($PICKLIST_VALUE)}
                    .picklist-{$FIELD_MODEL->getId()}-{Vtiger_Util_Helper::escapeCssSpecialCharacters($CONVERTED_PICKLIST_VALUE)} {
                        background-color: {$PICKLIST_COLOR};
                        color: {$PICKLIST_TEXT_COLOR}; 
                    }
            {/foreach}
        {/if}
    {/foreach}
    {if ($MODULE_NAME eq 'Calendar' && $MODULE_MODEL) OR ($RELATED_MODULE_NAME  eq 'Calendar' && $RELATED_MODULE) OR ($SOURCE_MODULE eq 'Calendar')}
		{assign var=STATUS_FIELD value=Vtiger_Field_Model::getInstance('eventstatus', Vtiger_Module_Model::getInstance('Events'))}

        {if $STATUS_FIELD}
            {assign var=PICKLIST_COLOR_MAP value=Settings_Picklist_Module_Model::getPicklistColorMap('eventstatus', true)}
            {foreach item=PICKLIST_COLOR key=PICKLIST_VALUE from=$PICKLIST_COLOR_MAP}
                {assign var=PICKLIST_TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)}
                {assign var=CONVERTED_PICKLIST_VALUE value=Vtiger_Util_Helper::convertSpaceToHyphen($PICKLIST_VALUE)}
                    .picklist-{$STATUS_FIELD->getId()}-{Vtiger_Util_Helper::escapeCssSpecialCharacters($CONVERTED_PICKLIST_VALUE)} {
                        background-color: {$PICKLIST_COLOR};color: {$PICKLIST_TEXT_COLOR};
                    }
            {/foreach}
        {/if}
    {/if}
</style>
