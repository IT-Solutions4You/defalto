{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

{if $BLOCK_FIELDS|php7_count gt 0}
    <div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom" data-block="{$BLOCK_LABEL}">
        <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
        {include file=vtemplate_path('blocks/Fields.tpl',$MODULE)}
    </div>
{/if}
