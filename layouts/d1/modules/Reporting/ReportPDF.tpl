{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    <style>
        .renderedTable, .renderedTable tr, .renderedTable td, .renderedTable th {
            border: 1px solid #ddd;
        }
    </style>
    <h3>{$RECORD->getName()}</h3>
    <p>{$RECORD->get('description')}</p>
    <br>
    {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getTableData() TABLE_STYLE=$RECORD->getTableStyle()}
    {if $HAS_CALCULATIONS}
        <br>
        {include file='ReportTable.tpl'|vtemplate_path:$MODULE_NAME TABLE_DATA=$RECORD->getTableCalculations()}
    {/if}
{/strip}