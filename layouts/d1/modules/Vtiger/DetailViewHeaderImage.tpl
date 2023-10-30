{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="containerDetailViewHeaderImage">
        {if $RECORD}
            {assign var=IMAGE_FIELDS value=$RECORD->getModule()->getFieldsByType('image')}
            {if !empty($IMAGE_FIELDS)}
                {assign var=IMAGE_DETAILS value=$RECORD->getImageDetails()}
                {if !empty($IMAGE_DETAILS)}
                    {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
                        {if !empty($IMAGE_INFO['url'])}
                            <div class="me-3">
                                <div class="ratio ratio-1x1 rounded" style="background: url('{$IMAGE_INFO['url']}') center center / cover; width: 7rem;"></div>
                            </div>
                            {break}
                        {/if}
                    {/foreach}
                {else}
                    <div class="me-3">
                        <div class="ratio ratio-1x1 rounded bg-primary text-white" style="width: 7rem;">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                {$RECORD->getModule()->getModuleIcon('3rem')}
                            </div>
                        </div>
                    </div>
                {/if}
            {/if}
        {/if}
    </div>
{/strip}