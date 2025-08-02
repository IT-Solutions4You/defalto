{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="p-2">
        {foreach item=KEYMETRIC from=$KEYMETRICS}
            <div class="pb-2">
                <span class="pull-right">{$KEYMETRIC.count}</span>
                <a href="?module={$KEYMETRIC.module}&view=List&viewname={$KEYMETRIC.id}">{$KEYMETRIC.name}</a>
            </div>
        {/foreach}
    </div>
{/strip}
