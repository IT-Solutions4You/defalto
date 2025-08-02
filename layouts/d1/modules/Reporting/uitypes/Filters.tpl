{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    {assign var=UITYPE_BLOCK value=$FIELD_MODEL->getUITypeModel()}
    <div class="containerFilters">
        <div class="py-2 text-secondary">{vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}</div>
        <div class="py-2">
            <textarea name="filter" class="hide">{$FIELD_VALUE}</textarea>
            {if $PRIMARY_MODULE}
                {include file='AdvanceFilter.tpl'|vtemplate_path:$QUALIFIED_MODULE
                SOURCE_MODULE=$PRIMARY_MODULE
                SOURCE_MODULE_MODEL=$UITYPE_BLOCK->getModuleModel($PRIMARY_MODULE)
                RECORD_STRUCTURE=$UITYPE_BLOCK->getRecordStructure($PRIMARY_MODULE)
                ADVANCE_CRITERIA=$UITYPE_BLOCK->getAdvanceCriteria($FIELD_VALUE)
                ADVANCED_FILTER_OPTIONS=Vtiger_Field_Model::getAdvancedFilterOptions()
                ADVANCED_FILTER_OPTIONS_BY_TYPE=Vtiger_Field_Model::getAdvancedFilterOpsByFieldType()
                DATE_FILTERS=Vtiger_Field_Model::getDateFilterTypes()}
            {/if}
        </div>
    </div>
{/strip}