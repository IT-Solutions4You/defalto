{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="summaryView bg-body mb-3">
    <div class="summaryViewHeader border-1 border-bottom p-3">
        <h4 class="display-inline-block">{vtranslate('LBL_KEY_METRICS', $MODULE_NAME)}</h4>
    </div>
    <div class="summaryViewFields p-3 pb-0 container-fluid">
        {foreach item=SUMMARY_CATEGORY from=$SUMMARY_INFORMATION}
            <div class="row textAlignCenter roundedCorners">
                {foreach key=FIELD_NAME item=FIELD_VALUE from=$SUMMARY_CATEGORY}
                    <div class="col-lg-3 pb-3">
                        <div class="ratio ratio-1x1">
                            <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-body-secondary rounded">
                                <div>
                                    <div class="fw-bold">{vtranslate($FIELD_NAME,$MODULE_NAME)}</div>
                                    <div>
                                        {if !empty($FIELD_VALUE)}{$FIELD_VALUE}{else}0{/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {/foreach}
    </div>
</div>