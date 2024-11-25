{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded p-3">
        <div class="container-fluid">
            <div class="row py-2 fw-bold">
                <div class="col">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</div>
                <div class="col">{vtranslate('LBL_ACTION', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=$RELATED_BLOCK_MODELS item=RELATED_BLOCK_MODEL}
                <div class="row py-2 border-top">
                    <div class="col">{$RELATED_BLOCK_MODEL->getName()}</div>
                    <div class="col">
                        <a class="btn btn-outline-secondary" href="{$RELATED_BLOCK_MODEL->getEditViewUrl()}">
                            <i class="fa-solid fa-pencil"></i>
                            <span class="ms-2">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</span>
                        </a>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>