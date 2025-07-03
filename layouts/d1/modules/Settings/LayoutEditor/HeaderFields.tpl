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

                        {section name=FIELD_LOOP start=0 loop=5}
                            {assign var=INDEX value=$smarty.section.FIELD_LOOP.index}
                            {assign var=IS_SAVED value=array_key_exists($INDEX, $SELECTED_FIELDS)}
                            <button type="button" class="{if empty($IS_SAVED)}openSelectFields{/if} headerFieldBtn p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="{$INDEX+1}" {if $IS_SAVED}data-fieldvalue="{$SELECTED_FIELDS[$INDEX]['fieldname']}"{/if}>

                                {if $IS_SAVED}
                                    {vtranslate($SELECTED_FIELDS[$INDEX]['fieldlabel'], $SOURCE_MODULE)}
                                    &nbsp;<i class="fa fa-times clearHeaderField" title="{vtranslate('LBL_REMOVE', $SOURCE_MODULE)}"></i>
                                {else}
                                    {$CLICK_HERE_LABEL}
                                {/if}
                            </button>
                        {/section}

                        <button type="button" class="p-2 bg-body-secondary border-dashed text-nowrap me-1" data-id="6" >
                            {vtranslate('Assigned To', $SOURCE_MODULE)}
                        </button>

                        {if $PRIMARY_MODULE}
                            {assign var=FIELD_OPTIONS value=$HEADER_FIELDS_MODEL->getFieldOptions($PRIMARY_MODULE)}
                            {assign var=LABEL_OPTIONS value=$HEADER_FIELDS_MODEL->getLabelOptions($PRIMARY_MODULE, [])}
                            <div class="labelFields visually-hidden">{json_encode($LABEL_OPTIONS)}</div>
                            <div class="fieldOptions visually-hidden">{json_encode($FIELD_OPTIONS)}</div>
                        {/if}

                    </div>
                </div>

            </div>
        </div>
    </div>
{/strip}