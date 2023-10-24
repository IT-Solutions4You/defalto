{************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
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
