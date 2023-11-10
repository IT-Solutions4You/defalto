{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="popover-container calendar-popover-container rounded">
        <div class="container-fluid">
            <div class="row py-3 text-muted align-items-center">
                <div class="col-2 text-end fs-3">
                    <span class="popover-image">{$MODULE_MODEL->getModuleIcon('', $RECORD_MODEL->get('calendar_type'))}</span>
                </div>
                <div class="col">
                    <div class="popover-name">{$RECORD_MODEL->getName()}</div>
                    <div>{$DATE_FIELDS}</div>
                </div>
                <div class="col-auto pb-4">
                    <div class="close-popover-container">
                        <div class="btn btn-close"></div>
                    </div>
                </div>
            </div>
            {if !$RECORD_MODEL->isEmpty('account_id')}
                <div class="row py-2">
                    <div class="col-2 text-end">
                        <i class="bi bi-house"></i>
                    </div>
                    <div class="col">
                        {$RECORD_MODEL->getDisplayValue('account_id')}
                    </div>
                </div>
            {/if}
            {if !$RECORD_MODEL->isEmpty('description')}
                <div class="row py-2">
                    <div class="col-2 text-end">
                        <i class="fs-5 bi bi-justify-left"></i>
                    </div>
                    <div class="col">
                        {strip_tags($RECORD_MODEL->get('description'))}
                    </div>
                </div>
            {/if}
            {if !$RECORD_MODEL->isEmpty('assigned_user_id')}
                <div class="row py-2">
                    <div class="col-2 text-end">
                        <i class="fs-5 bi bi-person"></i>
                    </div>
                    <div class="col">
                        {$RECORD_MODEL->getDisplayValue('assigned_user_id')}
                    </div>
                </div>
            {/if}
            {if !empty($HEADER_VALUES)}
                <div class="popover-headers border-top">
                    {foreach from=$HEADER_VALUES key=HEADER_LABEL item=HEADER_VALUE}
                        <div class="row py-2">
                            <div class="col-5 text-end text-muted">
                                {$HEADER_LABEL}
                            </div>
                            <div class="col fw-semibold">
                                <div class="text-truncate">{$HEADER_VALUE}</div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {/if}
            <div class="row py-3 border-top">
                <div class="col-auto">
                    <div class="btn-toolbar btn-group-sm">
                        {if !$EVENT_TYPE->isMarkAsDone()}
                            <a href="javascript:ITS4YouCalendar_Calendar_Js.markAsDone({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}','{$EVENT_TYPE->getMarkAsDoneField()}','{$EVENT_TYPE->getMarkAsDoneValue()}');"
                               class="btn text-success me-2 markAsDoneAction{$RECORD_MODEL->getId()}">
                                <i class="fa fa-check"></i>
                                <span class="ms-2">{vtranslate('LBL_MARK_AS_DONE', $QUALIFIED_MODULE)}</span>
                            </a>
                        {/if}
                    </div>
                </div>
                <div class="col-auto ms-auto">
                    <div class="btn-toolbar btn-group-sm">
                        <a href="{$RECORD_MODEL->getDetailViewUrl()}" class="btn text-secondary me-2 showDetailOverlay" title="">
                            <i class="fa fa-eye"></i>
                            <span class="ms-2">{vtranslate('LBL_DETAIL_OVERLAY', $QUALIFIED_MODULE)}</span>
                        </a>
                        <a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn text-primary me-2 showEditOverlay">
                            <i class="fa fa-pencil"></i>
                            <span class="ms-2">{vtranslate('LBL_EDIT_OVERLAY', $QUALIFIED_MODULE)}</span>
                        </a>
                        <a href="javascript:ITS4YouCalendar_Calendar_Js.deleteEvent({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}')" class="btn text-danger me-2">
                            <i class="fa fa-trash"></i>
                            <span class="ms-2">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}