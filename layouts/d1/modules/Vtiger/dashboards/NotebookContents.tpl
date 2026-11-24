{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="p-2">
		<div class="d-flex flex-column">
			<div class="dashboard_notebookWidget_view">
				<div class="row gx-0 mb-3">
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
					<div class="dashboard_notebookWidget_viewarea">
						{$WIDGET->getDisplayContent()}
					</div>
				</div>
			</div>
			<div class="dashboard_notebookWidget_text" style="display:none;">
				<div class="row gx-0 mb-3">
					<div class="col-lg">
						<i>{vtranslate('LBL_LAST_SAVED_ON', $MODULE)}</i> {Vtiger_Util_Helper::formatDateTimeIntoDayString($WIDGET->getLastSavedDate())}
					</div>
					<div class="col-lg-auto">
						<button class="btn btn-sm btn-primary dashboard_notebookWidget_save">
							<strong>{vtranslate('LBL_SAVE', $MODULE)}</strong>
						</button>
					</div>
				</div>
				<div class="row gx-0">
					<div class="col-lg-12">
						<textarea id="notebookContent_{$WIDGET->get('id')|escape:'html'}" class="dashboard_notebookWidget_textarea boxSizingBorderBox form-control" data-note-book-id="{$WIDGET->get('id')|escape:'html'}">{$WIDGET->getDisplayContent()|escape:'html'}</textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
