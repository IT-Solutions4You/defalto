/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var DragAndDrop_Detail_Js */
Vtiger.Class('DragAndDrop_Detail_Js', {
    instance: false,
    getInstance() {
        if (!this.instance) {
            this.instance = new DragAndDrop_Detail_Js();
        }

        return this.instance;
    }
}, {

    overlayId: 'dragAndDropOverlay',
    dragCounter: 0,
    uploadAllowed: false,

    getContainer() {
        return $('.detailViewContainer').first();
    },

    getFormContainer(container) {
        const targetContainer = container || this.getContainer();

        return targetContainer.find('form[name="dragAndDropUpload"]').first();
    },

    // Translations for the current language come from languages/{lang}/DragAndDrop.php.
    // The global overlay cannot read them from #js_strings (that only holds the page module),
    // so they are fetched once via view=Strings (cached in sessionStorage).
    strings: {},

    registerEvents() {
        if (!this.isDetailView()) {
            return;
        }
        this.uploadAllowed = this.hasDocumentsRelation();
        this.loadStrings();
        this.loadOverlay();
    },

    /**
     * Fetches the module jsLanguageStrings through Vtiger_Language_Handler::export().
     * A cache hit fills this.strings synchronously; on a miss the overlay text is refreshed once the response arrives.
     */
    loadStrings() {
        const self = this,
            lang = (typeof app.getUserLanguage === 'function') ? app.getUserLanguage() : 'en_us',
            cacheKey = 'dragAndDropStrings_' + lang;

        try {
            const cached = window.sessionStorage.getItem(cacheKey);
            if (cached) {
                this.strings = JSON.parse(cached);
                return;
            }
        } catch (e) { /* sessionStorage unavailable */
        }

        app.request.get({'url': 'index.php?module=DragAndDrop&view=Strings'}).then(function (err, data) {
            if (err || !data || typeof data !== 'object') {
                return;
            }
            self.strings = data;
            try {
                window.sessionStorage.setItem(cacheKey, JSON.stringify(data));
            } catch (e) { /* sessionStorage unavailable */
            }
        });
    },

    isDetailView() {
        return this.getContainer().find('#detailView').length > 0;
    },

    /**
     * A module without a Documents relation (e.g. Vendors) has no Documents
     * related tab means drag and drop is not activated (SaveAjax with
     * relationOperation would otherwise fail on a non-existent relation).
     * Related tabs carry an explicit data-module attribute
     * (ModuleRelatedTabs.tpl), including the "More" dropdown.
     */
    hasDocumentsRelation() {
        return this.getContainer().find('.related-tabs [data-module="Documents"]').length > 0;
    },

    loadOverlay() {
        const self = this;

        app.request.get({'url': 'index.php?module=DragAndDrop&view=Overlay'}).then(function (err, data) {
            if (err || !data) {
                return;
            }

            self.getContainer().append(data);
            self.updateOverlayState();
            self.registerContainerDragEvents();
        });
    },

    updateOverlayState() {
        this.getOverlay().toggleClass('drag-and-drop-upload-allowed', this.uploadAllowed);
    },

    getOverlay() {
        return this.getContainer().find('#' + this.overlayId);
    },

    showOverlay() {
        this.getOverlay().addClass('active');
    },

    hideOverlay() {
        this.dragCounter = 0;
        this.getOverlay().removeClass('active dragover');
    },

    dragHasFiles(e) {
        const dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (!dt || !dt.types) {
            return false;
        }
        return Array.prototype.indexOf.call(dt.types, 'Files') !== -1;
    },

    getRecordId() {
        if (typeof app.getRecordId === 'function') {
            return app.getRecordId();
        }
        return this.getContainer().find('#recordId').val();
    },

    formatFileSize(bytes) {
        if (typeof vtUtils !== 'undefined' && typeof vtUtils.convertFileSizeInToDisplayFormat === 'function') {
            return vtUtils.convertFileSizeInToDisplayFormat(bytes);
        }
        return Math.round(bytes / 1024) + ' KB';
    },

    fileTitle(fileName) {
        return fileName.replace(/\.[^.]+$/, '');
    },

    /**
     * Translation with an English fallback. this.strings is filled from the module
     * jsLanguageStrings through view=Strings. Nothing is written to the
     * shared languages/custom/{lang}/Vtiger.php.
     */
    t(key, fallback) {
        return this.strings[key] || fallback;
    },

    registerContainerDragEvents() {
        const self = this,
            container = this.getContainer();

        container.off('.dragAndDrop');

        // Show the drop zone while a file is dragged over the detail container.
        container.on('dragenter.dragAndDrop', function (e) {
            if (!self.dragHasFiles(e)) {
                return;
            }
            e.preventDefault();
            self.dragCounter++;
            self.showOverlay();
        });

        container.on('dragover.dragAndDrop', function (e) {
            if (self.dragHasFiles(e)) {
                e.preventDefault();
            }
        });

        container.on('dragleave.dragAndDrop', function (e) {
            if (!self.dragHasFiles(e)) {
                return;
            }
            self.dragCounter--;
            if (self.dragCounter <= 0) {
                self.hideOverlay();
            }
        });

        // highlight when the cursor is directly over the overlay
        container.on('dragover.dragAndDrop', '#' + this.overlayId, function () {
            $(this).addClass('dragover');
        });

        container.on('drop.dragAndDrop', function (e) {
            e.preventDefault();

            const droppedOnOverlay = $(e.target).closest('#' + self.overlayId).length > 0,
                dt = e.originalEvent.dataTransfer;
            self.hideOverlay();

            if (!droppedOnOverlay || !dt || !dt.files || !dt.files.length) {
                return;
            }

            if (!self.uploadAllowed) {
                app.helper.showAlertNotification({
                    'message': self.t('JS_DND_UPLOAD_NOT_AVAILABLE', 'File upload is not available')
                        + ' — ' + self.t('JS_DND_NO_DOCUMENTS_RELATION', 'This module has no relation to Documents')
                });
                return;
            }

            self.openUploadModal(Array.prototype.slice.call(dt.files));
        });
    },

    /**
     * Opens the module upload modal with native Documents fields and SaveAjax.
     */
    openUploadModal(files) {
        const self = this,
            url = 'index.php?module=DragAndDrop&view=UploadModal'
                + '&targetModule=Documents'
                + '&sourceModule=' + app.getModuleName()
                + '&sourceRecord=' + self.getRecordId();

        app.helper.showProgress();
        app.request.get({'url': url}).then(function (e, resp) {
            app.helper.hideProgress();
            if (e) {
                app.helper.showErrorNotification({'message': e.message || 'Permission denied'});
                return;
            }
            app.helper.showModal(resp, {
                'cb': function (modalContainer) {
                    self.setupModal(modalContainer, files);
                }
            });
        });
    },

    setupModal(modalContainer, files) {
        const self = this,
            form = this.getFormContainer(modalContainer),
            maxLimit = parseInt(form.find('input[name="max_upload_limit"]').val(), 10) || 0;

        // select2 / owner field initialization of the native fields
        if (typeof vtUtils !== 'undefined') {
            vtUtils.applyFieldElementsView(modalContainer);
        }

        // drop files exceeding the limit
        const queue = [],
            skipped = [];
        files.forEach(function (file) {
            if (maxLimit && file.size > maxLimit) {
                skipped.push(file.name);
            } else {
                queue.push(file);
            }
        });

        if (skipped.length) {
            app.helper.showAlertNotification({
                'message': app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE') + ': ' + skipped.join(', ')
            });
        }

        if (!queue.length) {
            app.helper.hideModal();
            return;
        }

        this.renderFileList(modalContainer, queue);

        // Title: editable for a single file, hidden for multiple (per file = file name)
        const titleField = form.find('[name="notes_title"]'),
            titleRow = modalContainer.find('#dragAndDropTitleRow'),
            titleHint = modalContainer.find('#dragAndDropTitleHint');
        if (queue.length === 1) {
            titleField.val(this.fileTitle(queue[0].name));
            titleRow.removeClass('d-none');
            titleHint.addClass('d-none');
        } else {
            titleRow.addClass('d-none');
            titleHint.removeClass('d-none');
        }

        // remove a file from the list
        form.on('click', '.drag-and-drop-batch-remove', function () {
            const row = $(this).closest('.drag-and-drop-batch-row');
            queue[row.data('idx')] = null;
            row.remove();
            const left = queue.filter(function (f) {
                return f !== null;
            });
            if (!left.length) {
                app.helper.hideModal();
            } else if (left.length === 1) {
                titleField.val(self.fileTitle(left[0].name));
                titleRow.removeClass('d-none');
                titleHint.addClass('d-none');
            }
        });

        form.on('submit', function (e) {
            e.preventDefault();
            const left = queue.filter(function (f) {
                return f !== null;
            });
            if (!left.length) {
                return;
            }
            modalContainer.find('#dragAndDropUploadBtn').prop('disabled', true);
            modalContainer.find('.drag-and-drop-batch-remove').hide();
            modalContainer.find('[data-bs-dismiss="modal"]')
                .removeAttr('data-bs-dismiss')
                .prop('disabled', true)
                .addClass('disabled')
                .attr('aria-disabled', 'true');
            self.uploadQueue(modalContainer, form, left);
        });
    },

    renderFileList(modalContainer, queue) {
        const self = this,
            fileList = modalContainer.find('#dragAndDropFileList').empty(),
            rowTemplate = modalContainer.find('#dragAndDropFileRowTemplate').html();

        queue.forEach(function (file, idx) {
            const row = $(rowTemplate).filter('.drag-and-drop-batch-row').attr('data-idx', idx);
            row.find('.drag-and-drop-batch-name').text(file.name);
            row.find('.drag-and-drop-batch-size').text(self.formatFileSize(file.size));
            row.find('.drag-and-drop-batch-remove')
                .attr('title', self.t('JS_DND_REMOVE', 'Remove'))
                .attr('aria-label', self.t('JS_DND_REMOVE', 'Remove'));
            fileList.append(row);
        });
    },

    /**
     * Uploads a single file using FormData from the modal and selected file,
     * POSTed to the native Documents SaveAjax (same principle as core _upload).
     * Direct $.ajax for xhr.upload progress events; the CSRF token is added
     * automatically by csrf-magic.js (patched XMLHttpRequest.send).
     */
    uploadFile(form, file, title, onProgress) {
        const aDeferred = $.Deferred(),
            formData = new FormData(form[0]);
        formData.set('action', 'SaveAjax');
        formData.append('filename', file);
        formData.set('notes_title', title);

        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            xhr: function () {
                const xhr = $.ajaxSettings.xhr();
                if (xhr.upload && typeof onProgress === 'function') {
                    xhr.upload.addEventListener('progress', function (ev) {
                        if (ev.lengthComputable) {
                            onProgress(Math.round((ev.loaded / ev.total) * 100));
                        }
                    });
                }
                return xhr;
            }
        }).done(function (response) {
            if (response && response.success) {
                aDeferred.resolve(response.result);
            } else {
                aDeferred.reject(response);
            }
        }).fail(function (err) {
            aDeferred.reject(err);
        });

        return aDeferred.promise();
    },

    uploadQueue(modalContainer, form, queue) {
        const self = this,
            results = {ok: 0, failed: []},
            single = queue.length === 1,
            singleTitle = form.find('[name="notes_title"]').val(),
            uploadBtn = modalContainer.find('#dragAndDropUploadBtn'),
            uploadBtnText = uploadBtn.find('strong'),
            // Snapshot rows before uploads because completed rows are removed.
            rows = modalContainer.find('.drag-and-drop-batch-row'),
            uploadNext = function (idx) {
                if (idx >= queue.length) {
                    // Brief pause so the user sees the completed progress bars.
                    uploadBtnText.text(self.t('JS_DND_DONE', 'Done'));
                    setTimeout(function () {
                        self.finishUpload(modalContainer, form, results, queue.length);
                    }, 1000);
                    return;
                }

                const file = queue[idx],
                    row = rows.eq(idx),
                    progressBar = row.find('.drag-and-drop-batch-progress-bar');
                row.find('.drag-and-drop-batch-status .fa').attr('class', 'fa-solid fa-spinner fa-spin');
                uploadBtnText.text(self.t('JS_DND_UPLOADING', 'Uploading')
                    + '… (' + (idx + 1) + '/' + queue.length + ')');

                if (row.length && row[0].scrollIntoView) {
                    row[0].scrollIntoView({'block': 'nearest'});
                }

                const title = (single && singleTitle) ? singleTitle : self.fileTitle(file.name);

                self.uploadFile(form, file, title, function (percent) {
                    progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
                }).then(function () {
                    results.ok++;
                    progressBar
                        .css('width', '100%')
                        .attr('aria-valuenow', 100)
                        .addClass('drag-and-drop-success');
                    row.find('.drag-and-drop-batch-status .fa').attr('class', 'fa-solid fa-check');
                    // Remove a completed file after a short confirmation delay.
                    setTimeout(function () {
                        row.fadeOut(300, function () {
                            row.remove();
                        });
                    }, 350);
                    // A small pause between files keeps progress easy to follow.
                    setTimeout(function () {
                        uploadNext(idx + 1);
                    }, 700);
                }, function () {
                    // A failed file stays in the list and is marked red.
                    results.failed.push(file.name);
                    progressBar
                        .css('width', '100%')
                        .attr('aria-valuenow', 100)
                        .addClass('drag-and-drop-error');
                    row.find('.drag-and-drop-batch-status .fa').attr('class', 'fa-solid fa-xmark');
                    setTimeout(function () {
                        uploadNext(idx + 1);
                    }, 700);
                });
            };

        uploadNext(0);
    },

    finishUpload(modalContainer, form, results, total) {
        const folderid = form.find('[name="folderid"]').val();

        app.helper.hideModal();

        if (results.failed.length) {
            app.helper.showErrorNotification({
                'message': app.vtranslate('JS_UPLOAD_FAILED') + ' (' + results.failed.length + '/' + total + '): '
                    + results.failed.join(', ')
            });
        }
        if (results.ok) {
            app.helper.showSuccessNotification({
                'message': app.vtranslate('JS_UPLOAD_SUCCESSFUL') + ' (' + results.ok + '/' + total + ')'
            });
        }

        this.reloadAfterSave();
        app.event.trigger('post.documents.save', {'folderid': folderid});
    },

    /**
     * Refreshes Documents data on the page: counts on the related tabs (native
     * updateRelatedRecordsCount), then the related list / summary widget.
     */
    reloadAfterSave() {
        if (typeof Vtiger_Detail_Js !== 'undefined') {
            Vtiger_Detail_Js.getInstance().updateRelatedRecordsCount();
        }

        if (typeof Documents_Index_Js !== 'undefined') {
            Documents_Index_Js.getInstance().reloadList();
            return;
        }

        const widgetInput = this.getContainer().find('input[name="relatedModule"][value="Documents"]');
        if (!widgetInput.length) {
            return;
        }
        const widget = widgetInput.closest('div[class*="widgetContainer_"]'),
            url = widget.data('url');
        if (!widget.length || !url) {
            return;
        }
        app.request.get({'url': url}).then(function (e, resp) {
            if (!e) {
                widget.html(resp);
            }
        });
    }
});

jQuery(function () {
    const dragAndDrop = DragAndDrop_Detail_Js.getInstance();

    dragAndDrop.registerEvents();
});
