{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* Edit / QuickCreate view of an address block.

   When the module has an address mapping we render group-aware: each group
   (Billing / Shipping / …) is its own column, and inside it the fields are laid
   out in a fixed, readable order — street(s), then PSČ + city side by side, then
   state and country. This mirrors the detail view's column layout and keeps the
   two addresses next to each other. The widgets are the standard edit widgets
   (rendered from each field's uitype template) so a normal Save keeps working, and
   the resolved mapping is exposed in the DOM for the autocomplete JS (Part E).

   Modules without a mapping render as a plain block (blocks/Fields.tpl). *}

{if $BLOCK_FIELDS|php7_count gt 0}
    {assign var=ADDRESS_MAP value=Core_Address_BlockUIType::getAddressGroups($MODULE_NAME)}
    {if empty($ADDRESS_MAP)}
        {* No mapping — keep the standard block rendering untouched. *}
        <div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom" data-block="{$BLOCK_LABEL}">
            <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
            {include file=vtemplate_path('blocks/Fields.tpl',$MODULE)}
        </div>
    {else}
        {assign var=EDIT_GROUPS value=Core_Address_BlockUIType::getEditGroups($MODULE_NAME)}
        {assign var=MASS_EDIT value=($MASS_EDITION_MODE|default:false)}
        {assign var=ADDR_FIELD_TPL value='blockuitypes/AddressEditField.tpl'}
        <div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom addressEditBlock" data-block="{$BLOCK_LABEL}" data-address-module="{$MODULE_NAME}" data-address-map='{$ADDRESS_MAP|@json_encode|escape:'html'}'>
            <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
            <div class="container-fluid py-3 px-4">
                <div class="row">
                    {foreach item=GROUP from=$EDIT_GROUPS}
                        {* Only render the groups whose fields live in THIS block (a module may
                           have more than one address block). *}
                        {if isset($BLOCK_FIELDS[$GROUP.map.zip])}
                            <div class="col-12 col-lg-6 py-2">
                                {if $GROUP.label ne ''}<div class="fw-bold mb-2">{$GROUP.label|escape}</div>{/if}
                                <div class="row">
                                    {* street(s) — full width *}
                                    {foreach item=GF from=$GROUP.fields}
                                        {if $GF.role eq 'street'}{include file=vtemplate_path($ADDR_FIELD_TPL,$MODULE) FIELD_MODEL=$BLOCK_FIELDS[$GF.name] COL='col-12' MODULE=$MODULE MASS_EDIT=$MASS_EDIT}{/if}
                                    {/foreach}
                                    {* PSČ + city — one shared label, both inputs side by side. *}
                                    {assign var=ZIP_FIELD value=null}
                                    {assign var=CITY_FIELD value=null}
                                    {foreach item=GF from=$GROUP.fields}
                                        {if $GF.role eq 'zip'}{assign var=ZIP_FIELD value=$BLOCK_FIELDS[$GF.name]}{/if}
                                        {if $GF.role eq 'city'}{assign var=CITY_FIELD value=$BLOCK_FIELDS[$GF.name]}{/if}
                                    {/foreach}
                                    {if $ZIP_FIELD || $CITY_FIELD}
                                        {include file=vtemplate_path('blockuitypes/AddressEditFieldPair.tpl',$MODULE) ZIP_FIELD=$ZIP_FIELD CITY_FIELD=$CITY_FIELD MODULE=$MODULE MASS_EDIT=$MASS_EDIT}
                                    {/if}
                                    {* state, then country — full width *}
                                    {foreach item=GF from=$GROUP.fields}
                                        {if $GF.role eq 'state'}{include file=vtemplate_path($ADDR_FIELD_TPL,$MODULE) FIELD_MODEL=$BLOCK_FIELDS[$GF.name] COL='col-12' MODULE=$MODULE MASS_EDIT=$MASS_EDIT}{/if}
                                    {/foreach}
                                    {foreach item=GF from=$GROUP.fields}
                                        {if $GF.role eq 'country'}{include file=vtemplate_path($ADDR_FIELD_TPL,$MODULE) FIELD_MODEL=$BLOCK_FIELDS[$GF.name] COL='col-12' MODULE=$MODULE MASS_EDIT=$MASS_EDIT}{/if}
                                    {/foreach}
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                    {* Any block field not covered by a mapped group — rendered full width
                       so nothing is lost (e.g. an extra custom field in the address block). *}
                    {foreach key=FIELD_NAME item=LEFTOVER_MODEL from=$BLOCK_FIELDS}
                        {assign var=IS_CONSUMED value=false}
                        {foreach item=EG from=$EDIT_GROUPS}
                            {if isset($BLOCK_FIELDS[$EG.map.zip])}
                                {foreach item=EF from=$EG.fields}
                                    {if $EF.name eq $FIELD_NAME}{assign var=IS_CONSUMED value=true}{/if}
                                {/foreach}
                            {/if}
                        {/foreach}
                        {if !$IS_CONSUMED}{include file=vtemplate_path($ADDR_FIELD_TPL,$MODULE) FIELD_MODEL=$LEFTOVER_MODEL COL='col-12' MODULE=$MODULE MASS_EDIT=$MASS_EDIT}{/if}
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}
{/if}
