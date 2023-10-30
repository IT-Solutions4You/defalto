{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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
