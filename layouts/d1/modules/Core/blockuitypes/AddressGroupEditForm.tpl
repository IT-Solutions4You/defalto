{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* Hidden inline edit form for one address group. Shared by the detail block and the
   key-fields summary so both edit with the same arrangement: PSČ + Mesto side by
   side, the rest stacked. Its fields are the standard edit widgets so a normal
   SaveAjax persists them; marked .addressEditBlock + data-address-map so the
   autocomplete JS wires up on reveal, and .addressGroup* hooks drive the
   document-level open/save/cancel handlers.

   Expects: MODULE_NAME, GROUP_INDEX, EDIT_GROUP ({map, fields}), FIELD_MODELS
   (field name -> field model carrying the record value). *}

<div class="addressGroupEditForm addressEditBlock hide mt-2"
     data-address-module="{$MODULE_NAME}"
     data-address-group-index="{$GROUP_INDEX}"
     data-address-map='{[$EDIT_GROUP.map]|@json_encode|escape:'html'}'
     data-address-fields='{$EDIT_GROUP.fields|@json_encode|escape:'html'}'>
    <div class="row">
        {foreach item=GF from=$EDIT_GROUP.fields}
            {* PSČ + Mesto go side by side; city is rendered right after zip and skipped here. *}
            {if $GF.role eq 'city'}{continue}{/if}
            {assign var=GF_COL value='col-12'}
            {if $GF.role eq 'zip'}{assign var=GF_COL value='col-3'}{/if}
            {include file=vtemplate_path('blockuitypes/AddressEditField.tpl',$MODULE_NAME) FIELD_MODEL=$FIELD_MODELS[$GF.name] COL=$GF_COL MODULE=$MODULE_NAME MASS_EDIT=false PLACEHOLDER_MODE=true}
            {if $GF.role eq 'zip'}
                {foreach item=CF from=$EDIT_GROUP.fields}
                    {if $CF.role eq 'city'}{include file=vtemplate_path('blockuitypes/AddressEditField.tpl',$MODULE_NAME) FIELD_MODEL=$FIELD_MODELS[$CF.name] COL='col-9' MODULE=$MODULE_NAME MASS_EDIT=false PLACEHOLDER_MODE=true}{/if}
                {/foreach}
            {/if}
        {/foreach}
    </div>
    <div class="mt-2">
        <button type="button" class="btn btn-outline-primary px-4 me-2 cancelAddressGroup">{vtranslate('LBL_CANCEL',$MODULE_NAME)}</button>
        <button type="button" class="btn btn-primary active px-5 saveAddressGroup">{vtranslate('LBL_SAVE',$MODULE_NAME)}</button>
    </div>
</div>
