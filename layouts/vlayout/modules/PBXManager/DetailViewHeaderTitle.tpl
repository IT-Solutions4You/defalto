{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}

    <span class="span2">
     <div style="position:relative;display:inline;">
        {if $RECORD->get('customer') and $RECORD->get('customertype') eq 'Contacts'}
            {assign var=MODULE_INSTANCE value=Vtiger_Record_Model::getInstanceById($RECORD->get('customer'),$RECORD->get('customertype'))}
            {assign var=IMAGE_DETAILS value=$MODULE_INSTANCE->getImageDetails()}
	    {if $IMAGE_DETAILS}
		    {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
		        {if !empty($IMAGE_INFO.url)}
		                <img src="{$IMAGE_INFO.url}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" >
		        {else}
		                <img src="{vimage_path('summary_Contact.png')}" class="summaryImg"/>
		        {/if}
		    {/foreach}
	    {else}
		    <img src="{vimage_path('summary_Contact.png')}" class="summaryImg"/>
	    {/if}
        {else}
            <img src="{vimage_path('summary_Contact.png')}" class="summaryImg"/>
        {/if}
        {if $RECORD->get('direction') eq 'inbound'}
         <img src="modules/PBXManager/resources/images/Incoming.png" style="position:absolute;bottom:4px;right:0;">
         </div>
        {else if $RECORD->get('direction') eq 'outbound'}
         <img src="modules/PBXManager/resources/images/Outgoing.png" style="position:absolute;bottom:4px;right:0;">
         </div>
        {else}
         </div>
       {/if}    
    </span> 

    <span class="span10 margin0px">
        <span class="row-fluid">
            <span class="recordLabel pushDown" title="{$RECORD->getName()}">
                {assign var=NAME_FIELD value=$MODULE_MODEL->getNameFields()}
                {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                {if $FIELD_MODEL->getPermissions()}
                    {assign var=RECORDID value=$RECORD->get("customer")}

                    {if $RECORDID}
                        {assign var=MODULE value=$RECORD->get('customertype')}
                        {assign var=ENTITY_NAMES value=getEntityName($MODULE, array($RECORDID))}
                        {assign var=CALLERNAME value=$ENTITY_NAMES[$RECORDID]}
                    {else}
                        {assign var=CALLERNAME value=$RECORD->get("customernumber")}
                    {/if}

                    {assign var=CALLER_INFO value=PBXManager_Record_Model::lookUpRelatedWithNumber($RECORD->get('customernumber'))}
                        {if $CALLER_INFO.id}
                            {assign var=MODULEMODEL value=Vtiger_Module_Model::getInstance($RECORD->get('customertype'))}
                            {assign var=FIELDMODEL value=Vtiger_Field_Model::getInstance($CALLER_INFO.fieldname,$MODULEMODEL)}
                            {assign var=FIELD_NAME value=$FIELDMODEL->get('label')}
                        {/if}

                    {if $RECORD->get('direction') eq 'inbound'}
                        &nbsp;<strong><span class="{$NAME_FIELD}">
                                {vtranslate('LBL_CALL_FROM', $MODULE_MODEL->get('name'))}&nbsp;{$CALLERNAME}
                            </span><br/></strong>
                    {else}
                          &nbsp;<strong><span class="{$NAME_FIELD}">
                                     {vtranslate('LBL_CALL_TO', $MODULE_MODEL->get('name'))}&nbsp;{$CALLERNAME}
                              </span><br/></strong>
                    {/if}    
                    
                    {if $FIELD_NAME}           
                         &nbsp;{$FIELD_NAME}:&nbsp;<span class="title_label muted">{$RECORD->get('customernumber')}
                               </span>
                    {/if}          
                {/if}
            </span>
        </span>
    </span>

{/strip}
