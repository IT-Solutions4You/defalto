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
				<div class="row mb-3">
					<div class="col-lg">
						<i>{vtranslate('LBL_LAST_SAVED_ON', $MODULE)}</i> {Vtiger_Util_Helper::formatDateTimeIntoDayString($WIDGET->getLastSavedDate())}
					</div>
					<div class="col-lg-auto">
						<button class="btn btn-sm btn-outline-secondary dashboard_notebookWidget_edit">
							<strong>{vtranslate('LBL_EDIT', $MODULE)}</strong>
						</button>
					</div>
				</div>
				<div class="pushDown2per col-lg-12">
					<div class="dashboard_notebookWidget_viewarea boxSizingBorderBox form-control">
						{$WIDGET->getContent()|nl2br}
					</div>
				</div>
			</div>
			<div class="dashboard_notebookWidget_text" style="display:none;">
				<div class="row mb-3">
					<div class="col-lg">
						<i>{vtranslate('LBL_LAST_SAVED_ON', $MODULE)}</i> {Vtiger_Util_Helper::formatDateTimeIntoDayString($WIDGET->getLastSavedDate())}
					</div>
					<div class="col-lg-auto">
						<button class="btn btn-sm btn-primary dashboard_notebookWidget_save">
							<strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
						</button>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<textarea class="dashboard_notebookWidget_textarea boxSizingBorderBox form-control" data-note-book-id="{$WIDGET->get('id')}">
							{$WIDGET->getContent()}
						</textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
