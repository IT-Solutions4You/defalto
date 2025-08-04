{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{if $BLOCK_FIELDS|php7_count gt 0}
    <div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom" data-block="{$BLOCK_LABEL}">
        <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
        {include file=vtemplate_path('blocks/Fields.tpl',$MODULE)}
    </div>
{/if}
