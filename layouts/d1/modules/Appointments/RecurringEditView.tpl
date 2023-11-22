{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<!--Confirmation modal for updating Recurring Events-->
<div class="modal-dialog modal-lg modelContainer recurringRecordUpdate modal-content hide">
    {assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_RECURRING_EVENT', $MODULE)}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    <div class="modal-body">
        <div class="container-fluid">
            <div class="row py-2">{vtranslate('LBL_EDIT_RECURRING_EVENTS_INFO', $MODULE)}</div>
            <div class="row py-2">
				<div class="col-sm-3">
					<button class="btn btn-primary w-100 onlyThisEvent">{vtranslate('LBL_ONLY_THIS_EVENT', $MODULE)}</button>
				</div>
				<div class="col-sm">{vtranslate('LBL_ONLY_THIS_EVENT_EDIT_INFO', $MODULE)}</div>
            </div>
            <div class="row py-2">
				<div class="col-sm-3">
					<button class="btn btn-primary w-100 futureEvents">{vtranslate('LBL_FUTURE_EVENTS', $MODULE)}</button>
				</div>
				<div class="col-sm">{vtranslate('LBL_FUTURE_EVENTS_EDIT_INFO', $MODULE)}</div>
            </div>
            <div class="row py-2">
				<div class="col-sm-3">
					<button class="btn btn-primary w-100 allEvents">{vtranslate('LBL_ALL_EVENTS', $MODULE)}</button>
				</div>
				<div class="col-sm">{vtranslate('LBL_ALL_EVENTS_EDIT_INFO', $MODULE)}</div>
            </div>
        </div>
    </div>
</div>
<!--Confirmation modal for updating Recurring Events-->

