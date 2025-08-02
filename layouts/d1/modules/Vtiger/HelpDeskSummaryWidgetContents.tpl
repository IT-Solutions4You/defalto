{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="containerHelpDeskSummaryWidgetContents">
        <div class="container-fluid">
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                <div class="row recentActivitiesContainer py-2">
                    <div class="col">
                        <a class="btn-link" href="{$RELATED_RECORD->getDetailViewUrl()}" title="{$RELATED_RECORD->getDisplayValue('ticket_title')}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
                            <span class="fw-bold">{$RELATED_RECORD->getDisplayValue('ticket_title')}</span>
                        </a>
                    </div>
                    <div class="row">
                        <div class="col">
                            <span>{vtranslate('LBL_TICKET_PRIORITY',$MODULE)}</span>
                            <span class="mx-2">:</span>
                            <strong> {$RELATED_RECORD->getDisplayValue('ticketpriorities')}</strong>
                        </div>
                    </div>
                    {assign var=DESCRIPTION value="{$RELATED_RECORD->getDescriptionValue()}"}
                    {if !empty($DESCRIPTION)}
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="text-truncate w-100">
                                    <span>{vtranslate('LBL_DESCRIPTION',$MODULE)}</span>
                                    <span class="mx-2">:</span>
                                    <span>{$DESCRIPTION}</span>
                                </div>
                            </div>
                            <div class="col-lg-2 text-end">
                                <a href="{$RELATED_RECORD->getDetailViewUrl()}" class="btn-link text-primary" target="_blank">{vtranslate('LBL_MORE',$MODULE)}</a>
                            </div>
                        </div>
                    {/if}
                </div>
            {/foreach}
            {assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
            {if $NUMBER_OF_RECORDS eq 5}
                <div class="row py-2">
                    <div class="col text-center">
                        <a target="_blank" href="index.php?{$RELATION_LIST_URL}&tab_label=HelpDesk" class="moreRecentTickets btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}