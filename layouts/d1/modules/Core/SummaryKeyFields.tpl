{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="summaryView bg-body rounded mb-3">
    <div class="summaryViewHeader p-3 border-1 border-bottom">
        <h4 class="display-inline-block">{vtranslate('LBL_KEY_FIELDS', $MODULE_NAME)}</h4>
        <div class="float-end">
            <button type="button" class="btn btn-sm text-secondary fw-bold" onclick="Vtiger_Detail_Js.openDetail();">
                <i class="fa-solid fa-circle-info"></i>
                <span class="ms-2">{vtranslate('LBL_DETAILS', $QUALIFIED_MODULE)}</span>
            </button>
        </div>
    </div>
    <div class="summaryViewFields p-3">
        {$MODULE_SUMMARY}
    </div>
    <div class="p-3 border-top">
        {if $RECORD}
            {assign var=FIELD_MODEL value=$RECORD->getField('createdtime')}
            {if $FIELD_MODEL}
                <div class="text-end text-secondary small">
                    <span>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}:</span>
                    <span class="ms-2">{$RECORD->getDisplayValue($FIELD_MODEL->get('name'))}</span>
                </div>
            {/if}
            {assign var=FIELD_MODEL value=$RECORD->getField('modifiedtime')}
            {if $FIELD_MODEL}
                <div class="text-end text-secondary small">
                    <span>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}:</span>
                    <span class="ms-2">{$RECORD->getDisplayValue($FIELD_MODEL->get('name'))}</span>
                </div>
            {/if}
        {/if}
    </div>
</div>