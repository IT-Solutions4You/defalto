{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {foreach item=EMAIL_FIELD_LIST key=EMAIL_FIELD_NAME from=$EMAIL_FIELDS_LIST name= email_fields_foreach}
        <optgroup label="{$EMAIL_FIELD_NAME}">
            {foreach item=EMAIL_FIELD_DATA  from=$EMAIL_FIELD_LIST name=emailFieldIterator}
                {if $IS_INPUT_SELECTED_ALLOWED && 'yes' eq $SINGLE_RECORD && '1' neq $IS_INPUT_SELECTED_DEFINED}
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


