{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg pb-3">
                    <h4 class="m-0">{vtranslate('LBL_RELATED_BLOCKS', $QUALIFIED_MODULE)}</h4>
                </div>
                <div class="col-lg-auto pb-3">
                    <a href="{$RELATED_BLOCK_MODEL->getCreateViewUrl()}" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-lg"></i>
                        <span class="ms-2">{vtranslate('LBL_CREATE', $QUALIFIED_MODULE)}</span>
                    </a>
                </div>
            </div>
        </div>
        <table class="table table-borderless">
            <thead>
                <tr class="listViewContentHeader bg-body-secondary">
                    <th class="text-secondary w-25">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                    <th class="text-secondary">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</th>
                    <th class="text-secondary">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$RELATED_BLOCK_MODELS item=RELATED_BLOCK_MODEL}
                    <tr class="border-bottom">
                        <td>
                            <a class="btn text-secondary" href="{$RELATED_BLOCK_MODEL->getEditViewUrl()}" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                        <td>{$RELATED_BLOCK_MODEL->getName()}</td>
                        <td>{$RELATED_BLOCK_MODEL->getRelatedModuleName()}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>