{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<!--Confirmation modal for updating Recurring Events-->
<div class="modal-dialog modelContainer recurringRecordUpdate modal-content hide" style='min-width:350px;'>
    {assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_RECURRING_EVENT', $MODULE)}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    <div class="modal-body">
        <div class="container-fluid">
            <div class="row" style="padding: 1%;padding-left: 3%;">{vtranslate('LBL_EDIT_RECURRING_EVENTS_INFO', $MODULE)}</div>
            <div class="row" style="padding: 1%;">
				<span class="col-sm-12">
					<span class="col-sm-4">
						<button class="btn btn-default onlyThisEvent" style="width : 150px">{vtranslate('LBL_ONLY_THIS_EVENT', $MODULE)}</button>
					</span>
					<span class="col-sm-8">{vtranslate('LBL_ONLY_THIS_EVENT_EDIT_INFO', $MODULE)}</span>
				</span>
            </div>
            <div class="row" style="padding: 1%;">
				<span class="col-sm-12">
					<span class="col-sm-4">
						<button class="btn btn-default futureEvents" style="width : 150px">{vtranslate('LBL_FUTURE_EVENTS', $MODULE)}</button>
					</span>
					<span class="col-sm-8">{vtranslate('LBL_FUTURE_EVENTS_EDIT_INFO', $MODULE)}</span>
				</span>
            </div>
            <div class="row" style="padding: 1%;">
				<span class="col-sm-12">
					<span class="col-sm-4">
						<button class="btn btn-default allEvents" style="width : 150px">{vtranslate('LBL_ALL_EVENTS', $MODULE)}</button>
					</span>
					<span class="col-sm-8">{vtranslate('LBL_ALL_EVENTS_EDIT_INFO', $MODULE)}</span>
				</span>
            </div>
        </div>
    </div>
</div>
<!--Confirmation modal for updating Recurring Events-->

