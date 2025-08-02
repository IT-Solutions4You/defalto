{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if php7_count($BLOCK_FIELDS)}
    <div class="fieldBlockContainer border-bottom" data-block="{$BLOCK_LABEL}">
        <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
        <div class="container-fluid py-3 px-4">
            <div class="row">
                {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                    {if $FIELD_MODEL->getName() eq 'theme' or $FIELD_MODEL->getName() eq 'rowheight'}
                        <input type="hidden" name="{$FIELD_MODEL->getName()}" value="{$FIELD_MODEL->get('fieldvalue')}"/>
                        {continue}
                    {/if}
                    {if $FIELD_MODEL->isEditable() eq true}
                        {assign var=IS_FULL_WIDTH value=$FIELD_MODEL->isTableFullWidth()}
                        <div class="py-2 {if $IS_FULL_WIDTH}col-lg-12{else}col-lg-6{/if}">
                            <div class="row">
                                <div class="fieldLabel {if $IS_FULL_WIDTH}col-sm-2{else}col-sm-4{/if}">
                                    {vtranslate($FIELD_MODEL->get('label'), $MODULE)}
                                    {if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
                                </div>
                                <div class="fieldValue {if $IS_FULL_WIDTH}col-sm-10{else}col-sm-8{/if}">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </div>
                            </div>
                        </div>
                    {/if}
                {/foreach}

            </div>
        </div>
    </div>
    <br>
{/if}
