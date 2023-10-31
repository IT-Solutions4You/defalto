{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<div class="p-2">
	<div class="row">
            <div class="dashboard_notebookWidget_view" style="word-break: break-all">
			<div class="">
				<span class="col-lg-10">
					<i>{vtranslate('LBL_LAST_SAVED_ON', $MODULE)}</i> {Vtiger_Util_Helper::formatDateTimeIntoDayString($WIDGET->getLastSavedDate())}
				</span>
				<span class="col-lg-2">
					<span class="pull-right">
						<button class="btn btn-default btn-sm pull-right dashboard_notebookWidget_edit">
							<strong>{vtranslate('LBL_EDIT', $MODULE)}</strong>
						</button>
					</span>
				</span>
			</div>
                        <br><br>
			<div class="pushDown2per col-lg-12">
				<div class="dashboard_notebookWidget_viewarea boxSizingBorderBox">
					{$WIDGET->getContent()|nl2br}
				</div>
			</div>
		</div>
		<div class="dashboard_notebookWidget_text" style="display:none;">
			<div class="">
				<span class="col-lg-10">
					<i>{vtranslate('LBL_LAST_SAVED_ON', $MODULE)}</i> {Vtiger_Util_Helper::formatDateTimeIntoDayString($WIDGET->getLastSavedDate())}
				</span>
				<span class="col-lg-2">
					<span class="pull-right">
						<button class="btn btn-mini btn-success pull-right dashboard_notebookWidget_save">
							<strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
						</button>
					</span>
				</span>
			</div>
                        <br><br>
			<div class="">
				<span class="col-lg-12">
					<textarea class="dashboard_notebookWidget_textarea boxSizingBorderBox" data-note-book-id="{$WIDGET->get('id')}">
						{$WIDGET->getContent()}
					</textarea>
				</span>
			</div>
		</div>
	</div>
</div>
{/strip}
