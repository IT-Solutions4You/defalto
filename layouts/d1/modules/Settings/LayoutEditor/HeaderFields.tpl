{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

{strip}
    <div class="headerFieldsDiv padding20 py-3">
        <div class="row">
            <div class="col-sm-12">
                <div class="containerSelectFields">
                    <div class="containerFields" data-field="" data-label="">
                        <div class="modalFields visually-hidden">
                            {include file='uitypes/FieldsNewFieldModal.tpl'|vtemplate_path:$QUALIFIED_MODULE}
                        </div>

                        {assign var=CLICK_HERE_LABEL value=vtranslate('LBL_CLICK_HERE_ADD_COLUMN', $QUALIFIED_MODULE)}
                        <input type="hidden" name="click_here_label" value="{$CLICK_HERE_LABEL}" />

                        {assign var=IS_SAVED1 value=array_key_exists(0, $SELECTED_FIELDS)}
                        <button type="button" class="{if empty($IS_SAVED1)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="1" {if $IS_SAVED1}data-fieldvalue="{$SELECTED_FIELDS[0]['fieldname']}"{/if}>
                            {if $IS_SAVED1}
                                {vtranslate($SELECTED_FIELDS[0]["fieldlabel"], $SOURCE_MODULE)}
                                &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                            {else}
                                {$CLICK_HERE_LABEL}
                            {/if}
                        </button>

                        {assign var=IS_SAVED2 value=array_key_exists(1, $SELECTED_FIELDS)}
                        <button type="button" class="{if empty($IS_SAVED2)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="2" {if $IS_SAVED2}data-fieldvalue="{$SELECTED_FIELDS[1]['fieldname']}"{/if}>
                            {if $IS_SAVED2}
                                {vtranslate($SELECTED_FIELDS[1]["fieldlabel"], $SOURCE_MODULE)}
                                &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                            {else}
                                {$CLICK_HERE_LABEL}
                            {/if}
                        </button>

                        {assign var=IS_SAVED3 value=array_key_exists(2, $SELECTED_FIELDS)}
                        <button type="button" class="{if empty($IS_SAVED3)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="3" {if $IS_SAVED3}data-fieldvalue="{$SELECTED_FIELDS[2]['fieldname']}"{/if}>
                            {if $IS_SAVED3}
                                {vtranslate($SELECTED_FIELDS[2]["fieldlabel"], $SOURCE_MODULE)}
                                &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                            {else}
                                {$CLICK_HERE_LABEL}
                            {/if}
                        </button>

                        {assign var=IS_SAVED4 value=array_key_exists(3, $SELECTED_FIELDS)}
                        <button type="button" class="{if empty($IS_SAVED4)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="4" {if $IS_SAVED4}data-fieldvalue="{$SELECTED_FIELDS[3]['fieldname']}"{/if}>
                            {if $IS_SAVED4}
                                {vtranslate($SELECTED_FIELDS[3]["fieldlabel"], $SOURCE_MODULE)}
                                &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                            {else}
                                {$CLICK_HERE_LABEL}
                            {/if}
                        </button>

                        {assign var=IS_SAVED5 value=array_key_exists(4, $SELECTED_FIELDS)}
                        <button type="button" class="{if empty($IS_SAVED5)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="5" {if $IS_SAVED5}data-fieldvalue="{$SELECTED_FIELDS[4]['fieldname']}"{/if}>
                            {if $IS_SAVED5}
                                {vtranslate($SELECTED_FIELDS[4]["fieldlabel"], $SOURCE_MODULE)}
                                &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                            {else}
                                {$CLICK_HERE_LABEL}
                            {/if}
                        </button>

                        <button type="button" class="p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="6" >
                            {vtranslate('Assigned To', $SOURCE_MODULE)}
                        </button>

                        {if $PRIMARY_MODULE}
                            {assign var=FIELD_OPTIONS value=$UITYPE_MODEL->getFieldOptions($PRIMARY_MODULE)}
                            {assign var=LABEL_OPTIONS value=$UITYPE_MODEL->getLabelOptions($PRIMARY_MODULE, [])}
                            <div class="labelFields visually-hidden">{json_encode($LABEL_OPTIONS)}</div>
                            <div class="fieldOptions visually-hidden">{json_encode($FIELD_OPTIONS)}</div>
                        {/if}

                    </div>
                </div>

            </div>
        </div>
    </div>
{/strip}