{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="container-fluid px-0">
        {foreach item=SUMMARY_CATEGORY from=$SUMMARY_INFORMATION}
            <div class="row textAlignCenter roundedCorners">
                {foreach key=FIELD_NAME item=FIELD_VALUE from=$SUMMARY_CATEGORY}
                    <div class="col-lg-3 pb-3">
                        <div class="ratio ratio-1x1">
                            <div class="h-100 w-100 d-flex align-items-center justify-content-center bg-body-secondary rounded">
                                <div>
                                    <div class="fw-bold">{vtranslate($FIELD_NAME, $MODULE_NAME)}</div>
                                    <div>{if !empty($FIELD_VALUE)}{$FIELD_VALUE}{else}0{/if}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {/foreach}
    </div>
{/strip}
