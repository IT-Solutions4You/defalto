{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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