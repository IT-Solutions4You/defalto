{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=MODULE value='PBXManager'}
    {assign var=MODULEMODEL value=Vtiger_Module_Model::getInstance($MODULE)}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    <span class="js-iti-display" data-iti-number="{$FIELD_VALUE}">
        <span class="iti-display-number">
            {if $MODULEMODEL and $MODULEMODEL->isActive() and $FIELD_VALUE}
                {assign var=PERMISSION value=PBXManager_Server_Model::checkPermissionForOutgoingCall()}
                {if $PERMISSION}
                    {assign var=PHONE_NUMBER value=$FIELD_VALUE|regex_replace:"/[-()\s]/":""}
                    <a class="phoneField" data-value="{$PHONE_NUMBER}" record="{$RECORD->getId()}" onclick="Vtiger_PBXManager_Js.registerPBXOutboundCall('{$PHONE_NUMBER}',{$RECORD->getId()})">{$FIELD_MODEL->get('fieldvalue')}</a>
                {else}
                    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
                {/if}
            {else}
                {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
            {/if}
        </span>
    </span>
{/strip}
