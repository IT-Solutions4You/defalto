{**
 * This file is part of Defalto - a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="list-group-item py-1 px-2 small fw-semibold text-secondary bg-body-tertiary">
        {vtranslate('LBL_ACCOUNTS_SIMILAR_ORGANIZATIONS', $MODULE)}
    </div>
    {foreach item=RECORD from=$RECORDS}
        <div class="list-group-item py-1 px-2 text-body" data-record-id="{$RECORD.id}">{$RECORD.name|escape:html}</div>
    {/foreach}
{/strip}
