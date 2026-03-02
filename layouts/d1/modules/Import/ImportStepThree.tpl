{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<span>
    <h4>{'LBL_IMPORT_MAP_FIELDS'|@vtranslate:$MODULE}</h4>
</span>
<hr>
<div id="savedMapsContainer">
    {include file="Import_Saved_Maps.tpl"|@vtemplate_path:'Import'}
</div>
<div class="py-2">
    {include file="Import_Mapping.tpl"|@vtemplate_path:'Import'}
</div>
<div class="row py-2 align-items-center justify-content-end">
    <div class="col-lg-2 text-secondary">
        <label for="save_map" class="form-check">
            <input type="checkbox" class="form-check-input" name="save_map" id="save_map">
            <span class="ms-2">{'LBL_SAVE_AS_CUSTOM_MAPPING'|@vtranslate:$MODULE}</span>
        </label>
    </div>
    <div class="col-lg-4">
        <input type="text" name="save_map_as" id="save_map_as" class="form-control">
    </div>
</div>
<div class="py-2">
    {if !isset($IMPORTABLE_FIELDS) || !$IMPORTABLE_FIELDS}
        {assign var=IMPORTABLE_FIELDS value=$AVAILABLE_FIELDS}
    {/if}
    {include file="Import_Default_Values_Widget.tpl"|@vtemplate_path:'Import' IMPORTABLE_FIELDS=$IMPORTABLE_FIELDS}
</div>