/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Sorting queue — one modal for everything the AI triage does with new
 * mail: the conversations a sync just sorted (the label each got, where it
 * was moved) and, after a weekend, the ones still waiting — the inbox keeps
 * asking the server for the next few until none is left. Closing the modal
 * does not stop the sorting
 *
 * Mixin of MailManager_ProIndex_Js — loaded before ProIndex.js
 */
/** @var MailManager_ProTriageQueue_Mixin */
window.MailManager_ProTriageQueue_Mixin = {

    /** Folder type => icon of an automatic move */
    TRIAGE_MOVE_ICONS: {spam: 'fa-solid fa-ban', trash: 'fa-solid fa-trash'},

    /** Steps in a row that sorted nothing before the queue gives up (another run holds the mail) */
    TRIAGE_IDLE_STEPS: 2,

    /** Conversations the server sorts in one step (MailManager_ProTriageQueue_Model::STEP) */
    TRIAGE_STEP: 5,

    triageQueueRunning: false,
    triageModal: null,
    /** reason of the AI block the user was already told about on this page (silent syncs do not repeat it) */
    aiBlockShown: '',
    /** the next step is the first one of a sorting the user started himself */
    triageForce: false,

    /**
     * After a sync: shows what it sorted and goes on with what still waits.
     * The window opens for an automatic move (the mail left the inbox) or
     * for waiting mail — a plain label needs no window
     *
     * @param {object[]} done rows of the conversations the sync just sorted
     * @param {number} pending conversations still waiting
     * @return {boolean} false = the window could not show the moves (the caller reports them)
     */
    showTriageQueue: function (done, pending) {
        const self = this,
            moved = jQuery.grep(done || [], function (result) {
                return !!result.moved;
            }).length > 0,
            open = this.isTriageModalOpen();

        done = done || [];
        if (!pending && !moved && !(open && done.length)) {
            return true;
        }
        if (open) {
            // the window is there already (opened by Refresh, or still open
            // from before) — the new rows join it: sorted ones on top, the
            // waiting ones below
            jQuery.each(done, function (index, result) {
                self.addTriageResult(result, true);
            });
            self.triageQueueTotal += done.length;
            self.triageQueueDone += done.length;
            this.fetchTriagePending(pending, function (waiting) {
                const rows = self.triageModal.find('.its4you-triage-rows');
                jQuery.each(waiting, function (index, item) {
                    if (!rows.find('.its4you-triage-row[data-threadid="' + item.threadid + '"]').length) {
                        rows.append(self.buildTriageRow(item));
                        self.triageQueueTotal++;
                    }
                });
                self.updateTriageStatus(waiting.length || self.triageQueueRunning
                    ? '' : app.vtranslate('JS_TRIAGE_DONE'));
                self.continueTriageQueue(waiting.length);
            });
            return true;
        }
        if (jQuery('.modal.show:visible, .its4you-composer-overlay').length) {
            // showModal() would close the user's own window — the sorting
            // goes on without one
            self.triageModal = jQuery('<div></div>');
            self.triageQueueTotal = self.triageQueueDone = 0;
            self.continueTriageQueue(pending);
            return false;
        }

        this.fetchTriagePending(pending, function (waiting) {
            self.triageQueueTotal = done.length + waiting.length;
            self.triageQueueDone = done.length;
            app.helper.showModal(self.buildTriageModal(waiting), {cb: function (modal) {
                self.triageModal = modal;
                jQuery.each(done, function (index, result) {
                    self.addTriageResult(result, true);
                });
                self.updateTriageStatus(waiting.length ? '' : app.vtranslate('JS_TRIAGE_DONE'));
                self.continueTriageQueue(waiting.length);
            }});
        });

        return true;
    },

    /**
     * Refresh was clicked — the window opens before the mail server answers
     * and waits for what the sync brings
     *
     * @return {boolean} false = it cannot open (another modal, the composer) or already sorts
     */
    openTriageWindow: function () {
        if (this.isTriageModalOpen()) {
            return !this.triageQueueRunning;
        }
        if (this.triageQueueRunning || jQuery('.modal.show:visible, .its4you-composer-overlay').length) {
            return false;
        }
        this.triageQueueTotal = this.triageQueueDone = 0;
        // the user asks himself — the provider is tried even if it refused a moment ago
        this.triageForce = true;
        this.triageModal = app.helper.showModal(this.buildTriageModal([]));
        this.triageModal.find('.its4you-triage-status').text(app.vtranslate('JS_TRIAGE_CHECKING'));
        this.triageModal.find('.progress-bar').addClass('progress-bar-striped progress-bar-animated').css('width', '100%');

        return true;
    },

    /**
     * @param {number} delay milliseconds the last line stays readable
     */
    closeTriageWindow: function (delay) {
        const self = this;
        setTimeout(function () {
            // only the window that still has nothing to show
            if (self.isTriageModalOpen() && !self.triageQueueRunning
                && !self.triageModal.find('.its4you-triage-row').length) {
                app.helper.hideModal();
            }
        }, delay);
    },

    /**
     * The AI provider refuses the account (credit used up, key rejected, limit) —
     * its own window says what happened and what helps. A silent sync shows
     * it once per page and reason; a sorting the user started always
     *
     * @param {object} block {reason, title, text, hint, detail, settings_url}
     * @param {boolean} asked the user started the sync / sorting himself
     */
    showAiBlock: function (block, asked) {
        const self = this;

        if (!block || (!asked && this.aiBlockShown === block.reason)
            || jQuery('.its4you-ai-block-modal:visible, .its4you-composer-overlay').length
            || jQuery('.modal.show:visible').not(jQuery('.its4you-triage-modal').closest('.modal')).length) {
            return;
        }
        this.aiBlockShown = block.reason;

        const footer = jQuery('<div class="text-center w-100"></div>');
        if (block.settings_url) {
            footer.append(jQuery('<a class="btn btn-primary active"></a>').attr('href', block.settings_url)
                .text(app.vtranslate('JS_AI_BLOCK_SETTINGS')))
                .append('&nbsp;&nbsp;');
        }
        footer.append(jQuery('<a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal"></a>')
            .text(app.vtranslate('JS_CLOSE')));

        const dialog = jQuery('<div class="modal-dialog its4you-ai-block-modal"></div>').append(
                jQuery('<div class="modal-content"></div>')
                    .append(jQuery('<div class="modal-header"></div>')
                        .append('<button type="button" class="btn-close order-last ms-auto" data-bs-dismiss="modal"></button>')
                        .append(jQuery('<h5 class="modal-title"></h5>')
                            .append('<i class="fa-solid fa-triangle-exclamation"></i>&nbsp;')
                            .append(document.createTextNode(block.title))))
                    .append(jQuery('<div class="modal-body"></div>')
                        .append(jQuery('<p class="its4you-ai-block-text"></p>').text(block.text))
                        .append(jQuery('<p class="its4you-ai-block-hint"></p>').text(block.hint))
                        .append(block.detail
                            ? jQuery('<div class="its4you-ai-block-detail"></div>').text(block.detail)
                            : ''))
                    .append(jQuery('<div class="modal-footer"></div>').append(footer))
            ),
            open = function () {
                self.triageModal = app.helper.showModal(dialog);
            };

        // The sorting window (if open) is replaced. showModal() over a visible
        // modal lets the old one finish hiding AFTER the new content is in and
        // wipe it - wait for the old one to be gone first
        if (this.isTriageModalOpen()) {
            app.helper.hideModal().then(open);
        } else {
            open();
        }
    },

    /**
     * @return {boolean} the sorting window is on the screen
     */
    isTriageModalOpen: function () {
        return !!this.triageModal && this.triageModal.find('.its4you-triage-modal').is(':visible');
    },

    /**
     * @param {number} pending conversations the sync says still wait
     * @param {function} callback receives [{threadid, subject, sender}]
     */
    fetchTriagePending: function (pending, callback) {
        if (!pending) {
            callback([]);
            return;
        }
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProTriageQueue',
            mode: 'list',
            mailbox: this.mailboxId
        }}).then(function (err, data) {
            if (err || !data || !data.success) {
                // not "nothing waits" — the window must not claim everything is sorted
                app.helper.showErrorNotification({message: app.vtranslate('JS_LIST_FAILED')});
                app.helper.hideModal();
                return;
            }
            callback(data.pending || []);
        });
    },

    /**
     * Starts the steps when mail waits and nothing sorts it yet
     *
     * @param {number} pending
     */
    continueTriageQueue: function (pending) {
        if (!pending || this.triageQueueRunning) {
            return;
        }
        this.triageQueueRunning = true;
        this.triageQueueIdle = 0;
        this.runTriageStep();
    },

    /**
     * @param {object[]} waiting [{threadid, subject, sender}]
     * @return {jQuery} the modal dialog
     */
    buildTriageModal: function (waiting) {
        const self = this,
            rows = jQuery('<div class="its4you-triage-rows"></div>');

        jQuery.each(waiting, function (index, item) {
            rows.append(self.buildTriageRow(item));
        });

        return jQuery('<div class="modal-dialog modal-lg its4you-triage-modal"></div>').append(
            jQuery('<div class="modal-content"></div>')
                .append(jQuery('<div class="modal-header"></div>')
                    .append('<button type="button" class="btn-close order-last ms-auto" data-bs-dismiss="modal"></button>')
                    .append(jQuery('<h5 class="modal-title"></h5>')
                        .append('<i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;')
                        .append(document.createTextNode(app.vtranslate('JS_TRIAGE_TITLE')))))
                .append(jQuery('<div class="modal-body"></div>')
                    .append('<div class="its4you-triage-status"></div>')
                    .append('<div class="progress its4you-triage-progress">'
                        + '<div class="progress-bar"></div></div>')
                    .append(rows))
                .append(jQuery('<div class="modal-footer"></div>')
                    .append(jQuery('<div class="text-center w-100"></div>')
                        .append(jQuery('<button type="button" class="btn btn-primary cancelLink" data-bs-dismiss="modal"></button>')
                            .text(app.vtranslate('JS_CLOSE')))))
        );
    },

    /**
     * @param {object} item {threadid, subject, sender}
     * @return {jQuery}
     */
    buildTriageRow: function (item) {
        const self = this,
            // the subject leads to the mail — the window closes, the conversation opens
            subject = jQuery('<a href="javascript:void(0);" class="its4you-triage-open"></a>')
                .text(item.subject || '')
                .on('click', function () {
                    self.openTriagedThread(item.threadid);
                });

        return jQuery('<div class="its4you-triage-row"></div>')
            .attr('data-threadid', item.threadid)
            .append('<i class="fa-regular fa-clock its4you-triage-state"></i>')
            .append(jQuery('<div class="its4you-triage-mail"></div>')
                .append(jQuery('<div class="its4you-triage-subject"></div>').append(subject))
                .append(jQuery('<div class="its4you-triage-sender"></div>').text(item.sender || '')))
            .append('<div class="its4you-triage-result"></div>');
    },

    /**
     * Opens a conversation listed in the sorting window: the window closes
     * (a sorting still running goes on without it) and the mail shows
     *
     * @param {number} threadId
     */
    openTriagedThread: function (threadId) {
        const row = this.getContainer().find('.its4you-thread-row[data-threadid="' + threadId + '"]').first();

        app.helper.hideModal();
        if (row.length) {
            this.loadConversation(row);
        } else {
            // not in the list shown (another folder, moved by its label)
            this.getContainer().find('.its4you-thread-row').removeClass('selected');
            this.loadConversationById(threadId);
        }
    },

    /**
     * One step: the server classifies the next few conversations
     */
    runTriageStep: function () {
        const self = this;

        // the server takes the newest waiting ones — the first rows not done yet
        this.triageModal.find('.its4you-triage-row').not('.its4you-triage-done').slice(0, this.TRIAGE_STEP)
            .find('.its4you-triage-state').attr('class', 'fa-solid fa-rotate fa-spin its4you-triage-state');

        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProTriageQueue',
            mode: 'run',
            force: this.triageForce ? 1 : 0,
            mailbox: this.mailboxId
        }}).then(function (err, data) {
            const done = (data && data.done) || [];
            self.triageForce = false;
            if (err || !data || !data.success) {
                self.finishTriageQueue(false);
                return;
            }
            jQuery.each(done, function (index, result) {
                self.addTriageResult(result, false);
            });
            // no credit / rejected key: nothing more gets sorted now — the
            // waiting mail stays in the queue and the user learns why
            if (data.ai_block) {
                self.triageQueueDone += done.length;
                self.finishTriageQueue(false, data.ai_block.title);
                self.showAiBlock(data.ai_block, true);
                return;
            }
            self.triageQueueDone += done.length;
            self.triageQueueIdle = done.length ? 0 : self.triageQueueIdle + 1;
            self.updateTriageStatus();

            if (parseInt(data.pending, 10) > 0 && self.triageQueueIdle < self.TRIAGE_IDLE_STEPS) {
                self.runTriageStep();
            } else {
                self.finishTriageQueue(parseInt(data.pending, 10) === 0);
            }
        });
    },

    /**
     * Paints what happened to a conversation; a conversation the window does
     * not list yet (sorted by the sync itself, new mail meanwhile) gets its row
     *
     * @param {object} result {threadid, subject, sender, label, color, text_color, moved, moved_type, failed}
     * @param {boolean} onTop a new row goes above the waiting ones
     */
    addTriageResult: function (result, onTop) {
        let rows = this.triageModal.find('.its4you-triage-rows'),
            row = rows.find('.its4you-triage-row[data-threadid="' + result.threadid + '"]'),
            cell;
        if (!rows.length) {
            return;
        }
        if (!row.length) {
            row = this.buildTriageRow(result);
            if (onTop) {
                rows.prepend(row);
            } else {
                rows.append(row);
                this.triageQueueTotal++;
            }
        }
        cell = row.find('.its4you-triage-result').empty();
        row.addClass('its4you-triage-done');
        row.find('.its4you-triage-state').attr('class', result.failed
            ? 'fa-solid fa-triangle-exclamation its4you-triage-state its4you-triage-failed'
            : 'fa-solid fa-check its4you-triage-state its4you-triage-ok');

        if (result.failed) {
            // the reason next to it - "Failed" alone tells the user nothing
            cell.addClass('its4you-triage-result-failed')
                .append(jQuery('<span class="its4you-triage-failed"></span>').text(app.vtranslate('JS_TRIAGE_FAILED')))
                .append(jQuery('<span class="its4you-triage-error"></span>')
                    .text(result.error || '').attr('title', result.error || ''));
            return;
        }
        if (result.label) {
            cell.append(jQuery('<span class="its4you-label-chip"></span>')
                .css({background: result.color, color: result.text_color}).text(result.label));
        } else {
            cell.append(jQuery('<span class="its4you-triage-none"></span>').text(app.vtranslate('JS_TRIAGE_NO_LABEL')));
        }
        if (result.moved) {
            cell.append(jQuery('<span class="its4you-triage-moved"></span>')
                .addClass('its4you-triage-moved-' + result.moved_type)
                .append('<i class="fa-solid fa-arrow-right-long"></i>&nbsp;')
                .append(jQuery('<i></i>').addClass(this.TRIAGE_MOVE_ICONS[result.moved_type] || 'fa-regular fa-folder'))
                .append(document.createTextNode(' ' + result.moved)));
        }
    },

    /**
     * "Sorted 12 of 31" and the progress bar
     *
     * @param {string} text the final line instead of the count
     */
    updateTriageStatus: function (text) {
        const total = Math.max(this.triageQueueTotal, this.triageQueueDone, 1);
        this.triageModal.find('.progress-bar').removeClass('progress-bar-striped progress-bar-animated');
        this.triageModal.find('.its4you-triage-status').text(text || app.vtranslate('JS_TRIAGE_STATUS')
            .replace('%s', this.triageQueueDone).replace('%s', Math.max(this.triageQueueTotal, this.triageQueueDone)));
        this.triageModal.find('.progress-bar').css('width', Math.round(this.triageQueueDone / total * 100) + '%');
    },

    /**
     * The queue is empty (or the sorting could not go on) — the list and the
     * sidebar numbers follow what was labeled and moved
     *
     * @param {boolean} complete nothing waits any more
     * @param {string} reason why it stopped, instead of the general "interrupted"
     */
    finishTriageQueue: function (complete, reason) {
        let failed = this.triageModal.find('.its4you-triage-row .its4you-triage-state.its4you-triage-failed').length,
            text = app.vtranslate(complete ? 'JS_TRIAGE_DONE' : 'JS_TRIAGE_PAUSED');

        // "all sorted" over a list of failures would be a lie
        if (reason) {
            text = reason;
        } else if (complete && failed) {
            text = app.vtranslate('JS_TRIAGE_DONE_FAILED').replace('%s', failed);
        }
        this.triageQueueRunning = false;
        this.triageModal.find('.its4you-triage-row').not('.its4you-triage-done')
            .find('.its4you-triage-state').attr('class', 'fa-regular fa-clock its4you-triage-state');
        this.updateTriageStatus(text);
        this.triageModal.find('.progress-bar')
            .toggleClass('bg-warning', !complete && !reason)
            .toggleClass('bg-danger', Boolean(reason) || (complete && failed > 0));
        this.refreshSidebarCounts();
        if (this.threadListPage <= 1) {
            this.loadThreads(1, false);
        }
    }
};
