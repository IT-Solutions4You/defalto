{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* Combined "PSČ / Mesto" edit row: ONE label column naming both fields and the two
   inputs side by side in the value column. This keeps the standard label|value table
   look of the edit view (rendering them as two separate labelled fields produced
   cramped, ugly half-width label columns) while still having zip next to city.
   PSČ gets the narrower column — it is only ever a few characters.

   Expects: ZIP_FIELD, CITY_FIELD (field models, either may be null), MODULE,
   MASS_EDIT (bool). *}

{assign var=PAIR_LABEL value=''}
{if $ZIP_FIELD}
    {assign var=PAIR_LABEL value=vtranslate($ZIP_FIELD->get('label'), $MODULE)}
{/if}
{if $CITY_FIELD}
    {assign var=PAIR_CITY_LABEL value=vtranslate($CITY_FIELD->get('label'), $MODULE)}
    {if $PAIR_LABEL ne ''}
        {assign var=PAIR_LABEL value="`$PAIR_LABEL` / `$PAIR_CITY_LABEL`"}
    {else}
        {assign var=PAIR_LABEL value=$PAIR_CITY_LABEL}
    {/if}
{/if}

<div class="py-2 col-12">
    <div class="row align-items-center">
        <div class="fieldLabel text-secondary col-sm-4">
            <div class="d-flex">
                {$PAIR_LABEL}
                {if ($ZIP_FIELD && $ZIP_FIELD->isMandatory()) || ($CITY_FIELD && $CITY_FIELD->isMandatory())}<span class="text-danger ms-2">*</span>{/if}
            </div>
        </div>
        <div class="fieldValue col-sm-8">
            <div class="row g-2">
                {if $ZIP_FIELD}
                    <div class="col-4">
                        {if $MASS_EDIT}<input class="inputElement me-2 form-check-input" id="include_in_mass_edit_{$ZIP_FIELD->getFieldName()}" data-update-field="{$ZIP_FIELD->getFieldName()}" type="checkbox">{/if}
                        {include file=vtemplate_path($ZIP_FIELD->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$ZIP_FIELD FIELD_NAME=$ZIP_FIELD->getName() MODULE=$MODULE QUALIFIED_MODULE=$MODULE}
                    </div>
                {/if}
                {if $CITY_FIELD}
                    <div class="col-8">
                        {if $MASS_EDIT}<input class="inputElement me-2 form-check-input" id="include_in_mass_edit_{$CITY_FIELD->getFieldName()}" data-update-field="{$CITY_FIELD->getFieldName()}" type="checkbox">{/if}
                        {include file=vtemplate_path($CITY_FIELD->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$CITY_FIELD FIELD_NAME=$CITY_FIELD->getName() MODULE=$MODULE QUALIFIED_MODULE=$MODULE}
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
