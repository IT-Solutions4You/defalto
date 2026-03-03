{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="row py-2 text-success">
    <div class="col">{'LBL_TOTAL_RECORDS_IMPORTED'|@vtranslate:$MODULE}</div>
    <div class="col">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</div>
</div>
<div class="row py-2 text-success">
    <div class="col">{'LBL_NUMBER_OF_RECORDS_CREATED'|@vtranslate:$MODULE}</div>
    <div class="col">
        <span>{$IMPORT_RESULT.CREATED}</span>
        {if $IMPORT_RESULT['CREATED'] neq '0' and $FOR_MODULE neq 'Users'}
            <a class="cursorPointer ms-2" onclick="return Vtiger_Import_Js.showLastImportedRecords('index.php?module={$MODULE}&for_module={$FOR_MODULE}&view=List&start=1&foruser={$OWNER_ID}&_showContents=0')">{'LBL_DETAILS'|@vtranslate:$MODULE}</a>
        {/if}
    </div>
</div>
{if in_array($FOR_MODULE, $INVENTORY_MODULES) eq FALSE}
    <div class="row py-2">
        <div class="col">{'LBL_NUMBER_OF_RECORDS_UPDATED'|@vtranslate:$MODULE}</div>
        <div class="col">{$IMPORT_RESULT.UPDATED}</div>
    </div>
    <div class="row py-2">
        <div class="col">{'LBL_NUMBER_OF_RECORDS_SKIPPED'|@vtranslate:$MODULE}</div>
        <div class="col">
            <span>{$IMPORT_RESULT.SKIPPED}</span>
            {if $IMPORT_RESULT['SKIPPED'] neq '0'}
                <a class="cursorPointer ms-2" onclick="return Vtiger_Import_Js.showSkippedRecords('index.php?module={$MODULE}&view=List&mode=getImportDetails&type=skipped&start=1&foruser={$OWNER_ID}&_showContents=0&for_module={$FOR_MODULE}')">{'LBL_DETAILS'|@vtranslate:$MODULE}</a>
            {/if}
        </div>
    </div>
    <div class="row py-2">
        <div class="col">{'LBL_NUMBER_OF_RECORDS_MERGED'|@vtranslate:$MODULE}</div>
        <div class="col">{$IMPORT_RESULT.MERGED}</div>
    </div>
{/if}
{if $IMPORT_RESULT['FAILED'] neq '0'}
    <div class="row py-2 text-danger">
        <div class="col">{'LBL_TOTAL_RECORDS_FAILED'|@vtranslate:$MODULE}</div>
        <div>
            <span>{$IMPORT_RESULT.FAILED} / {$IMPORT_RESULT.TOTAL}</span>
            <a class="cursorPointer ms-2" onclick="return Vtiger_Import_Js.showFailedImportRecords('index.php?module={$MODULE}&view=List&mode=getImportDetails&type=failed&start=1&foruser={$OWNER_ID}&_showContents=0&for_module={$FOR_MODULE}')">{'LBL_DETAILS'|@vtranslate:$MODULE}</a>
        </div>
    </div>
{/if}