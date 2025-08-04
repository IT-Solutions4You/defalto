{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    <div id="{$MODULE}_{$VIEW}_{$FIELD_NAME}" class="Core_MailManagerReference_UIType">
        {$FIELD_VALUE}
    </div>
{/strip}