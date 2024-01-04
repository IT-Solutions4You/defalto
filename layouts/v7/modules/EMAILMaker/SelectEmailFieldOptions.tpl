{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    {foreach item=EMAIL_FIELD_LIST key=EMAIL_FIELD_NAME from=$EMAIL_FIELDS_LIST name= email_fields_foreach}
        <optgroup label="{$EMAIL_FIELD_NAME}">
            {foreach item=EMAIL_FIELD_DATA  from=$EMAIL_FIELD_LIST name=emailFieldIterator}
                {if $IS_INPUT_SELECTED_ALLOWED && '0' eq $EMAIL_FIELD_DATA.emailoptout && 'yes' eq $SINGLE_RECORD && '1' neq $IS_INPUT_SELECTED_DEFINED}
                    {assign var=IS_INPUT_SELECTED value='selected'}
                    {assign var=IS_INPUT_SELECTED_DEFINED value='1'}
                {else}
                    {assign var=IS_INPUT_SELECTED value=''}
                {/if}
                <option value="{$EMAIL_FIELD_DATA.crmid}|{$EMAIL_FIELD_DATA.fieldname}|{$EMAIL_FIELD_DATA.module}" {$IS_INPUT_SELECTED}>
                    {$EMAIL_FIELD_DATA.label} {if $EMAIL_FIELD_DATA.value neq "" && $SINGLE_RECORD eq "yes"}: {$EMAIL_FIELD_DATA.value}{else}{if $EMAIL_FIELD_NAME neq ""}({$EMAIL_FIELD_NAME}){/if}{/if} {if $EMAIL_FIELD_DATA.emailoptout eq "1" && $SINGLE_RECORD eq "yes"}&nbsp;({vtranslate('Email Opt Out', $MODULE)}){/if}
                </option>
            {/foreach}
        </optgroup>
    {/foreach}
{/strip}


