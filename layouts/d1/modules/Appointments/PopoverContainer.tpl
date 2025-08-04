{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="popover-container calendar-popover-container rounded bg-body">
        <div class="container-fluid">
            <div class="row pt-3 pb-1 text-muted align-items-center">
                <div class="col-2 text-end fs-3">
                    <span class="popover-image">{$MODULE_MODEL->getModuleIcon('', $RECORD_MODEL->get('calendar_type'))}</span>
                </div>
                <div class="col">
                    <div class="popover-name fs-5 fw-bold">{$RECORD_MODEL->getName()}</div>
                    <div>{$DATE_FIELDS}</div>
                </div>
                <div class="col-auto pb-4">
                    <div class="close-popover-container">
                        <div class="btn btn-close"></div>
                    </div>
                </div>
            </div>
            <div class="row py-1">
                <div class="col-2"></div>
                {if !$RECORD_MODEL->isEmpty('account_id')}
                    <div class="col-auto text">
                        <div class="rounded bg-body-secondary px-2 py-1">
                            <i class="bi bi-house"></i>
                            <span class="ms-2">{strip_tags($RECORD_MODEL->getDisplayValue('account_id'))}</span>
                        </div>
                    </div>
                {/if}
                {if !$RECORD_MODEL->isEmpty('contact_id')}
                    {assign var=RECORD_MODEL_CONTACTS value=array_filter(explode('<br>', $RECORD_MODEL->getDisplayValue('contact_id')))}
                    {if !empty($RECORD_MODEL_CONTACTS)}
                        <div class="col-auto text" title="{strip_tags(implode(', ', $RECORD_MODEL_CONTACTS))}">
                            <div class="rounded bg-body-secondary px-2 py-1">
                                <i class="bi bi-person-lines-fill"></i>
                                <span class="ms-2">{strip_tags($RECORD_MODEL_CONTACTS[0])}</span>
                            </div>
                        </div>
                    {/if}
                {/if}
            </div>
            {if !$RECORD_MODEL->isEmpty('location')}
                <div class="row py-1 align-items-center">
                    <div class="col-2 text-end">
                        <i class="text-secondary fs-5 bi bi-geo-alt"></i>
                    </div>
                    <div class="col">
                        {$RECORD_MODEL->get('location')}
                    </div>
                </div>
            {/if}
            {if !$RECORD_MODEL->isEmpty('description')}
                <div class="row py-1">
                    <div class="col-2 text-end">
                        <i class="text-secondary fs-5 bi bi-justify-left"></i>
                    </div>
                    <div class="col">
                        <div class="descriptionThreeLines">
                            {strip_tags($RECORD_MODEL->get('description'))}
                        </div>
                    </div>
                </div>
            {/if}
            <div class="row py-1 align-items-center">
                {if !$RECORD_MODEL->isEmpty('assigned_user_id')}
                    <div class="col-2 text-end">
                        <i class="text-secondary fs-5 bi bi-person"></i>
                    </div>
                    <div class="col-4">
                        <div class="text-truncate">{strip_tags($RECORD_MODEL->getDisplayValue('assigned_user_id'))}</div>
                    </div>
                {/if}
                {if !$RECORD_MODEL->isEmpty('invite_users')}
                    <div class="col-auto text-end">
                        <i class="text-secondary fs-5 bi bi-arrow-right-circle-fill"></i>
                    </div>
                    <div class="col-4">
                        <div class="text-truncate">{strip_tags(str_replace('<br>', ', ', $RECORD_MODEL->getDisplayValue('invite_users')))}</div>
                    </div>
                {/if}
            </div>
            {if !empty($HEADER_VALUES)}
                <div class="popover-headers row border-top">
                    {foreach from=$HEADER_VALUES key=HEADER_LABEL item=HEADER_VALUE}
                        <div class="row py-1">
                            <div class="col-4 text-end text-muted">
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
                            <a href="javascript:Appointments_Calendar_Js.markAsDone({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}','{$EVENT_TYPE->getMarkAsDoneField()}','{$EVENT_TYPE->getMarkAsDoneValue()}');"
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
                        {if Appointments_Events_Model::isSupportedSaveOverlay($RECORD_MODEL->getModuleName())}
                            <a href="{$RECORD_MODEL->getEditViewUrl()}" class="btn text-primary me-2 showEditOverlay">
                                <i class="fa fa-pencil"></i>
                                <span class="ms-2">{vtranslate('LBL_EDIT_OVERLAY', $QUALIFIED_MODULE)}</span>
                            </a>
                        {/if}
                        <a href="javascript:Appointments_Calendar_Js.deleteEvent({$RECORD_MODEL->getId()},'{$RECORD_MODEL->getModuleName()}')" class="btn text-danger me-2">
                            <i class="fa fa-trash"></i>
                            <span class="ms-2">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}