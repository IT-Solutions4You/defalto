{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* Detail view of an address block. Each mapped group (Billing / Shipping / …) is
   rendered as a standard detail field: a label column + a value column, two groups
   per row — so two addresses read as four columns (label | value | label | value),
   exactly like the rest of the detail view. The value stacks the address parts on
   their own lines: street / "zip city" / state / country (empty parts skipped).
   The inline-edit ✎ lives inside .fieldValue so it only shows on hover, like every
   other field. Modules without an address mapping fall back to the standard block
   rendering so nothing is lost. *}

{assign var=ADDR_GROUPS value=Core_Address_BlockUIType::getFormattedGroups($MODULE_NAME, $RECORD)}

{if empty($ADDR_GROUPS)}
    {include file=vtemplate_path('blockuitypes/Base.tpl',$MODULE_NAME)}
{else}
    {assign var=IS_HIDDEN value=$BLOCK->isHidden()}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    {assign var=CAN_EDIT value=$IS_AJAX_ENABLED && $RECORD->isEditable()}
    {if $CAN_EDIT}{assign var=ADDR_EDIT_GROUPS value=Core_Address_BlockUIType::getEditGroups($MODULE_NAME)}{/if}
    <div class="mt-3 bg-body rounded block block_{$BLOCK_LABEL_KEY} addressBlock" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}" data-address-module="{$MODULE_NAME}">
        <div class="p-3">
            <div class="text-truncate d-flex align-items-center">
                <span class="btn btn-outline-secondary blockToggle {if !$IS_HIDDEN}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                    <i class="fa fa-plus"></i>
                </span>
                <span class="btn btn-outline-secondary blockToggle {if $IS_HIDDEN}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
                    <i class="fa fa-minus"></i>
                </span>
                <span class="ms-3 fs-4 fw-bold">{vtranslate($BLOCK_LABEL_KEY,$QUALIFIED_MODULE)}</span>
            </div>
        </div>
        <div class="blockData p-3 border-top border-light-subtle {if $IS_HIDDEN}hide{/if}">
            <div class="container-fluid detailview-table">
                <div class="row">
                {foreach item=GROUP from=$ADDR_GROUPS}
                    {* Address parts in display order; each rendered on its own line, empty parts skipped. *}
                    {assign var=ZIP_CITY value=php7_trim("`$GROUP.zip` `$GROUP.city`")}
                    {assign var=ADDR_PARTS value=[$GROUP.street, $ZIP_CITY, $GROUP.state, $GROUP.country]}
                    <div id="{$MODULE_NAME}_Detail_field_addressGroup{$GROUP.index}" class="py-2 col-lg-6 addressGroup" data-address-group-index="{$GROUP.index}">
                        <div class="h-100">
                            <div class="row py-2 border-bottom border-light-subtle h-100 align-items-center">
                                <div class="fieldLabel text-truncate col-lg-4 {$WIDTHTYPE}">
                                    <span class="muted">{$GROUP.label|escape}</span>
                                </div>
                                <div class="fieldValue fw-semibold col-lg-8 {$WIDTHTYPE}">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <span class="value addressFormatted w-100">
                                            {assign var=FIRST value=true}
                                            {foreach $ADDR_PARTS as $PART}
                                                {if php7_trim($PART) ne ''}
                                                    {if !$FIRST}<br>{/if}{$PART|escape}{assign var=FIRST value=false}
                                                {/if}
                                            {/foreach}
                                        </span>
                                        {if $CAN_EDIT}
                                            <span class="action"><a href="#" onclick="return false;" class="editAction editAddressGroup fa fa-pencil" data-address-group-index="{$GROUP.index}"></a></span>
                                        {/if}
                                    </div>
                                    {if $CAN_EDIT}
                                        {assign var=EDIT_GROUP value=$ADDR_EDIT_GROUPS[$GROUP.index]}
                                        {if $EDIT_GROUP}
                                            {include file=vtemplate_path('blockuitypes/AddressGroupEditForm.tpl',$MODULE_NAME) MODULE_NAME=$MODULE_NAME GROUP_INDEX=$GROUP.index EDIT_GROUP=$EDIT_GROUP FIELD_MODELS=$FIELD_MODEL_LIST}
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
                </div>
            </div>
        </div>
    </div>
{/if}
