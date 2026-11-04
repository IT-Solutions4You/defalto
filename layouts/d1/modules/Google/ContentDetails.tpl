{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="p-2">
    {foreach from=$RECORDS item=RECORD key=TYPE}
        <div class="mb-3">
            <strong>{if $TYPE eq 'vtiger'}{vtranslate('LBL_UPDATES_CRM', $MODULE_NAME)}{else}{vtranslate('LBL_UPDATES_GOOGLE', $MODULE_NAME)}{/if}</strong>
            <div>{vtranslate('LBL_ADDED', $MODULE_NAME)}: {$RECORD['create']}</div>
            <div>{vtranslate('LBL_UPDATED', $MODULE_NAME)}: {$RECORD['update']}</div>
            <div>{vtranslate('LBL_DELETED', $MODULE_NAME)}: {$RECORD['delete']}</div>
            {if $RECORD['more']}
                <div class="alert alert-warning mt-2 mb-0">
                    {if $TYPE eq 'vtiger'}{vtranslate('LBL_MORE_CRM', $MODULE_NAME)}{else}{vtranslate('LBL_MORE_GOOGLE', $MODULE_NAME)}{/if}
                </div>
            {/if}
        </div>
    {/foreach}

    {if $SYNCTIME}
        <p class="text-secondary small mb-0" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($SYNCTIME)}">
            {vtranslate('LBL_SYNCRONIZED', $MODULE_NAME)}: {Vtiger_Util_Helper::formatDateDiffInStrings($SYNCTIME)}
        </p>
    {/if}
    {if $NORECORDS}<input type="hidden" id="googleSyncNoRefresh" value="yes">{/if}
</div>
