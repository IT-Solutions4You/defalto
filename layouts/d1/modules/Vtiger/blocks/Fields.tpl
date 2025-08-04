{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
                            <div class="row align-items-center">
                                <div class="fieldLabel text-secondary {if $FIELD_MODEL->isTableFullWidth()}col-sm-2{else}col-sm-4{/if}">
                                    <div class="d-flex">
                                        {if $MASS_EDITION_MODE}
                                            <input class="inputElement me-2 form-check-input" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">
                                        {/if}
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
                                        {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                                    </div>
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