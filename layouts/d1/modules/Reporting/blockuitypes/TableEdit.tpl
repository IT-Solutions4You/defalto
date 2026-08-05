{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="px-3 py-3">
    <div class="reportingTablePreview">
        <div class="d-flex justify-content-end gap-2 mb-3">
            <button
                class="toggleTableWidthBarButton btn btn-outline-secondary"
                type="button"
                aria-expanded="false"
                disabled
            >
                <i class="fa-solid fa-ruler-horizontal me-2"></i>
                {vtranslate('LBL_COLUMN_WIDTHS', $QUALIFIED_MODULE)}
            </button>
            <button class="renderTableButton btn btn-primary" type="button" disabled>
                <i class="renderTableIcon fa-solid fa-rotate me-2"></i>
                {vtranslate('LBL_RENDER_TABLE', $QUALIFIED_MODULE)}
            </button>
        </div>
        <div
            class="reportingTableWidthConfig visually-hidden"
            data-auto-label="{vtranslate('LBL_AUTO_WIDTH', $QUALIFIED_MODULE)}"
            data-width-label="{vtranslate('LBL_WIDTH', $QUALIFIED_MODULE)}"
        ></div>
        <div class="reportingTableTutorial border rounded bg-body-secondary bg-opacity-50 p-4" aria-live="polite">
            <div class="d-flex align-items-start">
                <i class="bi bi-table fs-2 text-secondary me-3"></i>
                <div class="w-100">
                    <h5 class="mb-1">{vtranslate('LBL_REPORT_PREVIEW_TUTORIAL', $QUALIFIED_MODULE)}</h5>
                    <p class="text-body-secondary mb-3">{vtranslate('LBL_REPORT_PREVIEW_TUTORIAL_DESCRIPTION', $QUALIFIED_MODULE)}</p>
                    <div class="row g-2">
                        <div class="col-12 col-lg">
                            <div class="reportingPreviewStep d-flex align-items-center border rounded bg-body p-3" data-preview-requirement="module">
                                <i class="previewStepPendingIcon bi bi-circle text-secondary me-2"></i>
                                <i class="previewStepCompleteIcon bi bi-check-circle-fill text-success me-2 d-none"></i>
                                <span>{vtranslate('LBL_REPORT_PREVIEW_STEP_MODULE', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                        <div class="col-12 col-lg">
                            <div class="reportingPreviewStep d-flex align-items-center border rounded bg-body p-3" data-preview-requirement="columns">
                                <i class="previewStepPendingIcon bi bi-circle text-secondary me-2"></i>
                                <i class="previewStepCompleteIcon bi bi-check-circle-fill text-success me-2 d-none"></i>
                                <span>{vtranslate('LBL_REPORT_PREVIEW_STEP_COLUMNS', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                        <div class="col-12 col-lg reportingPreviewGroupingStep">
                            <div class="reportingPreviewStep d-flex align-items-center border rounded bg-body p-3" data-preview-requirement="grouping">
                                <i class="previewStepPendingIcon bi bi-circle text-secondary me-2"></i>
                                <i class="previewStepCompleteIcon bi bi-check-circle-fill text-success me-2 d-none"></i>
                                <span>{vtranslate('LBL_REPORT_PREVIEW_STEP_GROUPING', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="renderedTableContainer d-none"></div>
    </div>
</div>
