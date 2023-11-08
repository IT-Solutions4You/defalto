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
            <div class="row py-3 border-bottom">
                <div class="col-2">
                    <div class="popover-image text-center">{$MODULE_MODEL->getModuleIcon($RECORD_MODEL->get('calendar_type'))}</div>
                </div>
                <div class="col">
                    <div class="popover-name">{$RECORD_MODEL->getName()}</div>
                    <div class="text-muted">{$DATE_FIELDS}</div>
                </div>
                <div class="col-auto">
                    <div class="close-popover-container">
                        <div class="btn btn-close"></div>
                    </div>
                </div>
            </div>
            {if !empty($HEADER_VALUES)}
                <div class="popover-headers">
                    {foreach from=$HEADER_VALUES key=HEADER_LABEL item=HEADER_VALUE}
                        <div class="row py-2">
                            <div class="col-lg-4 text-end text-muted">
                                {$HEADER_LABEL}
                            </div>
                            <div class="col-lg fw-semibold">
                                {$HEADER_VALUE}
                            </div>
                        </div>
                    {/foreach}
                </div>
            {/if}
            <div class="row py-3 border-top">
                <div class="col-2"></div>
                <div class="col">
                    <div class="btn-toolbar btn-group-sm">
                        {if !$EVENT_TYPE->isMarkAsDone()}
                            <a href="javascript:ITS4YouCalendar_Calendar_Js.markAsDone({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}','{$EVENT_TYPE->getMarkAsDoneField()}','{$EVENT_TYPE->getMarkAsDoneValue()}');"
                               class="btn text-secondary me-2 markAsDoneAction{$RECORD_MODEL->getId()}" title="{vtranslate('LBL_MARK_AS_DONE', $QUALIFIED_MODULE)}">
                                <i class="fa fa-check"></i>
                            </a>
                        {/if}
                        <a href="{$RECORD_MODEL->getDetailViewUrl()}" class="btn text-secondary me-2 showDetailOverlay" title="{vtranslate('LBL_DETAIL_OVERLAY', $QUALIFIED_MODULE)}">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn text-secondary me-2 showEditOverlay" title="{vtranslate('LBL_EDIT_OVERLAY', $QUALIFIED_MODULE)}">
                            <i class="fa fa-pencil"></i>
                        </a>
                        <a href="javascript:ITS4YouCalendar_Calendar_Js.deleteEvent({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}')" class="btn text-secondary me-2" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}