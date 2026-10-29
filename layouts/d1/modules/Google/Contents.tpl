{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{foreach key=INDEX item=JS_MODEL from=$SCRIPTS}
    <script type="{$JS_MODEL->getType()}" src="{$JS_MODEL->getSrc()}"></script>
{/foreach}

<div id="googleSync" class="p-3">
    <input type="hidden" id="googleSyncFirstTime" value="{if $FIRSTTIME}no{else}yes{/if}">
    <div id="googleSyncDetails"></div>

    {if $STATE eq 'home'}
        <p id="googleSyncTime" class="text-secondary small">
            {if $SYNCTIME}
                <span title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($SYNCTIME)}">
                    {vtranslate('LBL_SYNCRONIZED', $MODULE_NAME)}: {Vtiger_Util_Helper::formatDateDiffInStrings($SYNCTIME)}
                </span>
            {else}
                {vtranslate('LBL_NOT_SYNCRONIZED', $MODULE_NAME)}
            {/if}
        </p>
    {/if}

    <div class="d-grid gap-2">
        <button id="googleSyncButton" class="btn btn-primary" data-url="index.php?module=Google&view=List&operation=sync&sourcemodule={$SOURCEMODULE}">
            {vtranslate('LBL_SYNC_BUTTON', $MODULE_NAME)}
        </button>
        <button id="googleRemoveSyncButton" class="btn btn-outline-danger {if !$FIRSTTIME}d-none{/if}" data-url="index.php?module=Google&view=List&operation=removeSync&sourcemodule={$SOURCEMODULE}">
            {vtranslate('LBL_REMOVE_SYNC', $MODULE_NAME)}
        </button>
    </div>
</div>

{if $STATE eq 'CLOSEWINDOW'}
    <script>
        {if !$DENY}
            if (window.opener && typeof window.opener.sync === 'function') {
                window.opener.sync();
            }
        {/if}
        window.close();
    </script>
{/if}
