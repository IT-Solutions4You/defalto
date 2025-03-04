{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <div class="w-100 overflow-auto">
        <div class="border-bottom mb-3">
            <div class="nav nav-tabs px-4 pt-3">
                {foreach from=$BLOCKS item=BLOCK name=BLOCKS_NAME}
                    {assign var=BLOCK_LABEL value=$BLOCK->getLabel()}
                    {if false eq Reporting_Block_Model::isNavigationTab($BLOCK_LABEL)}{continue}{/if}
                    <div class="nav-item me-2">
                        <a class="nav-link {if 1 eq $smarty.foreach.BLOCKS_NAME.index}active{/if}" href="{$RECORD->getEditViewTabUrl($BLOCK_LABEL)}">
                            {$BLOCK->getIcon()}
                            <span class="ms-2">{vtranslate($BLOCK_LABEL, $MODULE_NAME)}</span>
                        </a>
                    </div>
                {/foreach}
            </div>
        </div>
        {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getTableData() TABLE_STYLE=$RECORD->getTableStyle()}
        {if $RECORD->hasCalculations()}
            {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getTableCalculations() TABLE_STYLE=[]}
        {/if}
    </div>
{/strip}