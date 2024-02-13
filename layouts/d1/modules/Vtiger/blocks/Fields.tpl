{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="container-fluid py-3 px-4">
        <div class="row">
            {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                {assign var=isReferenceField value=$FIELD_MODEL->getFieldDataType()}
                {assign var=refrenceList value=$FIELD_MODEL->getReferenceList()}
                {assign var=refrenceListCount value=php7_count($refrenceList)}
                {if $FIELD_MODEL->isEditable() eq true}
                    {if $FIELD_MODEL->isTableCustomWidth()}
                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                    {else}
                        <div class="py-2 {if $FIELD_MODEL->isTableFullWidth()}col-lg-12{else}col-lg-6{/if}">
                            <div class="row">
                                <div class="fieldLabel {if $FIELD_MODEL->isTableFullWidth()}col-sm-2{else}col-sm-4{/if}">
                                    {if $MASS_EDITION_MODE}
                                        <input class="inputElement me-2 form-check-input" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">
                                    {/if}
                                    {if $isReferenceField eq "reference"}
                                        {if $refrenceListCount > 1}
                                            {assign var=REFERENCED_MODULE_ID value=$FIELD_MODEL->get('fieldvalue')}
                                            {assign var=REFERENCED_MODULE_STRUCTURE value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($REFERENCED_MODULE_ID)}
                                            {if !empty($REFERENCED_MODULE_STRUCTURE)}
                                                {assign var=REFERENCED_MODULE_NAME value=$REFERENCED_MODULE_STRUCTURE->get('name')}
                                            {/if}
                                            <select class="select2 referenceModulesList">
                                                {foreach key=index item=value from=$refrenceList}
                                                    <option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
                                                {/foreach}
                                            </select>
                                        {else}
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {/if}
                                    {else}
                                        {if $MODULE eq 'Documents' && $FIELD_MODEL->get('label') eq 'File Name'}
                                            {assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
                                            {if $FILE_LOCATION_TYPE_FIELD}
                                                {if $FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}
                                                    <span class="me-2">{vtranslate("LBL_FILE_URL", $MODULE)}</span>
                                                    <span class="text-danger">*</span>
                                                {else}
                                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                                {/if}
                                            {else}
                                                {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                            {/if}
                                        {else}
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                        {/if}
                                    {/if}
                                    {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                                </div>
                                <div class="fieldValue {if $FIELD_MODEL->isTableFullWidth()}col-sm-10{else}col-sm-8{/if} {if $FIELD_MODEL->get('uitype') eq '56'}checkBoxType{/if}">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </div>
                            </div>
                        </div>
                    {/if}
                {/if}
            {/foreach}
        </div>
    </div>
{/strip}