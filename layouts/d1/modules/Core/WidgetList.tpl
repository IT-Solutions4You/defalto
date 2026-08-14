{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !empty($LIST_ENTRIES)}
        <div class="summaryListWidget table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        {foreach item=HEADER from=$LIST_HEADERS}
                            <th>{vtranslate($HEADER->get('label'), $RELATED_MODULE)}</th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {foreach item=ENTRY from=$LIST_ENTRIES}
                        <tr>
                            {foreach key=FIELD_NAME item=HEADER from=$LIST_HEADERS name=widgetFields}
                                {assign var=DISPLAY_VALUE value=$ENTRY->get($FIELD_NAME)}
                                {if $HEADER->getFieldDataType() eq 'currency'}
                                    {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($ENTRY->getCurrencyId())}
                                    {assign var=DISPLAY_VALUE value=CurrencyField::appendCurrencySymbol($DISPLAY_VALUE, $CURRENCY_INFO['symbol'])}
                                {/if}
                                <td>
                                    {if $smarty.foreach.widgetFields.first}
                                        <a class="btn-link" href="{$ENTRY->getDetailViewUrl()}">{$DISPLAY_VALUE}</a>
                                    {else}
                                        {$DISPLAY_VALUE}
                                    {/if}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        {include file='SummaryWidgetEmpty.tpl'|vtemplate_path:'Vtiger'
            EMPTY_STATE_LABEL='LBL_NO_RECORDS_FOUND'
            EMPTY_STATE_MODULE=$RELATED_MODULE
            EMPTY_STATE_SUFFIX=''}
    {/if}
    {if $PAGING_MODEL->getCurrentPage() gt 1 || $PAGING_MODEL->isNextPageExists()}
        <div class="d-flex justify-content-between pt-3">
            <button type="button" class="btn btn-sm btn-light summaryListWidgetPage"
                    data-page="{$PAGING_MODEL->getCurrentPage()-1}"{if $PAGING_MODEL->getCurrentPage() lte 1} disabled{/if}>
                <i class="fa-solid fa-chevron-left"></i>
                <span class="ms-2">{vtranslate('LBL_PREVIOUS', 'Vtiger')}</span>
            </button>
            <button type="button" class="btn btn-sm btn-light summaryListWidgetPage"
                    data-page="{$PAGING_MODEL->getCurrentPage()+1}"{if !$PAGING_MODEL->isNextPageExists()} disabled{/if}>
                <span class="me-2">{vtranslate('LBL_NEXT', 'Vtiger')}</span>
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    {/if}
{/strip}
