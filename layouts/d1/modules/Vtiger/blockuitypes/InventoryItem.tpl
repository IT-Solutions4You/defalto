{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<div class="mt-3 bg-body rounded block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
    {assign var=IS_HIDDEN value=$BLOCK->isHidden()}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    <input type=hidden name="timeFormatOptions" data-value='{if isset($DAY_STARTS)}{$DAY_STARTS}{else}""{/if}' />
    <div class="p-3">
        <div class="text-truncate d-flex align-items-center">
            <span class="btn btn-outline-secondary blockToggle {if !$IS_HIDDEN}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                <i class="fa fa-plus"></i>
            </span>
            <span class="btn btn-outline-secondary blockToggle {if $IS_HIDDEN}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                <i class="fa fa-minus"></i>
            </span>
            <span class="ms-3 fs-4 fw-bold">{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</span>
        </div>
    </div>
    <div class="blockData p-3 border-top border-light-subtle {if $IS_HIDDEN}hide{/if}">
        asdfk
    </div>
</div>