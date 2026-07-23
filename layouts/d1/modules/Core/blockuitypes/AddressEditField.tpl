{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* One address-block edit field. Two modes:
   - default: label column + value column, mirroring the standard block rendering
     (blocks/Fields.tpl) so the address block matches every other block in the
     edit view;
   - PLACEHOLDER_MODE: no label at all — the label is carried in
     data-address-placeholder and the JS puts it into the widget's placeholder,
     which keeps the inline ajax editor compact.

   Expects: FIELD_MODEL, COL (bootstrap column class), MODULE, MASS_EDIT (bool),
   PLACEHOLDER_MODE (bool, optional). *}

{if $FIELD_MODEL && $FIELD_MODEL->isEditable()}
    {assign var=ADDR_PH value=($PLACEHOLDER_MODE|default:false)}
    {assign var=ADDR_LABEL value=vtranslate($FIELD_MODEL->get('label'), $MODULE)}
    {if $ADDR_PH}
        {* Mandatory is marked in the placeholder itself, there is no label to carry it. *}
        {if $FIELD_MODEL->isMandatory()}{assign var=ADDR_LABEL value="`$ADDR_LABEL` *"}{/if}
        <div class="py-1 {$COL}" data-address-placeholder="{$ADDR_LABEL|escape}">
            <div class="fieldValue {if $FIELD_MODEL->get('uitype') eq '56'}checkBoxType{/if}">
                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$FIELD_MODEL FIELD_NAME=$FIELD_MODEL->getName() MODULE=$MODULE QUALIFIED_MODULE=$MODULE}
            </div>
        </div>
    {else}
        <div class="py-2 {$COL}">
            <div class="row align-items-center">
                <div class="fieldLabel text-secondary col-sm-4">
                    <div class="d-flex">
                        {if $MASS_EDIT}<input class="inputElement me-2 form-check-input" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">{/if}
                        {$ADDR_LABEL}
                        {if $FIELD_MODEL->isMandatory()}<span class="text-danger ms-2">*</span>{/if}
                    </div>
                </div>
                <div class="fieldValue col-sm-8 {if $FIELD_MODEL->get('uitype') eq '56'}checkBoxType{/if}">
                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$FIELD_MODEL FIELD_NAME=$FIELD_MODEL->getName() MODULE=$MODULE QUALIFIED_MODULE=$MODULE}
                </div>
            </div>
        </div>
    {/if}
{/if}
