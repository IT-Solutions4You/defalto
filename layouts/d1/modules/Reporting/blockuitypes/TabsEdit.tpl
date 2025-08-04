{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{if $BLOCK_FIELDS|php7_count gt 0}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    <div class="fieldBlockContainer mb-3 border-bottom {if 1 neq $smarty.foreach.blockIterator.iteration}{/if}" data-block="{$BLOCK_LABEL}">
        {if $PRIMARY_MODULE}
            <div class="container-fluid px-4 pt-3">
                {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                    <input type="hidden" name="{$FIELD_MODEL->get('name')}" value="{$RECORD->get($FIELD_NAME)}">
                {/foreach}
                <div class="nav nav-tabs">
                    {foreach from=$BLOCK_LIST item=BLOCK key=BLOCK_LABEL}
                        {if false eq Reporting_Block_Model::isNavigationTab($BLOCK_LABEL)}{continue}{/if}
                        <li class="nav-item me-2">
                            <button type="button" class="nav-link" data-show-block="{$BLOCK_LABEL}">
                                {$BLOCK->getIcon()}
                                <span class="ms-2">{vtranslate($BLOCK_LABEL, $QUALIFIED_MODULE)}</span>
                            </button>
                        </li>
                    {/foreach}
                </div>
            </div>
        {else}
            <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate('LBL_SELECT_MODULE', $MODULE)}</h4>
            <div class="container-fluid px-4 py-3">
                {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
                    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                    {if $FIELD_MODEL->isEditable() eq true}
                        <div class="row py-2">
                            <div class="col-lg-3 text-secondary">
                                {vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}
                            </div>
                            <div class="col-lg-4">
                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                            </div>
                        </div>
                    {/if}
                {/foreach}
                <div class="row py-2">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-4">
                        <button class="selectModule btn btn-primary" type="button">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</button>
                    </div>
                </div>
            </div>
        {/if}
    </div>
{/if}
