/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Thread list of the inbox — folders, Workspace, filters, paging, search and
 * the background sync
 *
 * Mixin of MailManager_ProIndex_Js — the methods run on the controller
 * instance (shared state, cross-calls through this/self); loaded before ProIndex.js
 */
window.MailManager_ProThreadList_Mixin = {

    /** Seconds a delete / move to Spam can be taken back */
    UNDO_SECONDS: 5,

    /** Subjects listed on the "Undo" card of a bulk action */
    UNDO_LISTED: 3,

    /** Folder type => [icon, title] of the "moved automatically" card */
    AUTO_MOVED: {
        spam: ['fa-solid fa-ban', 'JS_AUTO_MOVED_SPAM'],
        trash: ['fa-solid fa-trash', 'JS_AUTO_MOVED_TRASH']
    },

    /** Undoable action => [card icon, text for one conversation, text for many (%s)] */
    UNDO_ACTIONS: {
        'delete': ['fa-solid fa-trash', 'JS_THREAD_DELETED', 'JS_THREADS_DELETED'],
        spam: ['fa-solid fa-ban', 'JS_THREAD_MOVED_SPAM', 'JS_THREADS_MOVED_SPAM'],
        archive: ['fa-solid fa-archive', 'JS_THREAD_ARCHIVED', 'JS_THREADS_ARCHIVED'],
        restore: ['fa-solid fa-undo', 'JS_THREAD_RESTORED', 'JS_THREADS_RESTORED'],
        folder: ['fa-solid fa-folder-open', 'JS_THREAD_MOVED', 'JS_THREADS_MOVED']
    },

    /**
     * Switches between the Workspace view and folder browsing
     *
     * @param {boolean} on
     */
    setWorkspaceMode: function (on) {
        this.workspaceMode = !!on;
    },

    /**
     * The list tabs (All/Unread/Mine/Open/…) filter the Workspace and every
     * folder, the picked tab stays while switching them; the sidebar label
     * and priority views filter the mailbox their own way and hide them
     *
     * @param {boolean} visible
     */
    setListTabsVisible: function (visible) {
        let container = this.getContainer();
        container.find('.its4you-list-tabs').toggleClass('hide', !visible);
        if (!visible && this.listFilter !== 'all') {
            this.listFilter = 'all';
            container.find('.its4you-list-tab').removeClass('active')
                .filter('[data-filter="all"]').addClass('active');
        }
    },

    /**
     * Loads the thread list of a folder
     *
     * @param {number} page
     * @param {boolean} append
     */
    loadThreads: function (page, append, callback, retried) {
        // Only the newest request may render — a slower answer of an older
        // one (previous folder, previous search) must not overwrite it
        let requestId = ++this.threadListRequest,
            self = this,
            container = this.getContainer(),
            list = container.find('.its4you-thread-list'),
            params = {
                module: app.getModuleName(),
                view: 'ProIndexAjax',
                mode: 'threadList',
                mailbox: this.mailboxId,
                folder: this.folderId,
                workspace: this.workspaceMode ? 1 : 0,
                filter: this.listFilter,
                search: this.searchTerm,
                label: this.labelFilter,
                priority: this.priorityFilter,
                page: page || 1
            },
            // the same list reloaded keeps its ticks, another list starts clean
            listKey = [params.mailbox, params.folder, params.workspace, params.filter, params.search, params.label,
                params.priority].join('|'),
            ticked = [];

        if (append) {
            // rows deleted / moved meanwhile shifted the pages — the next
            // ones follow the rows the list holds (the hidden "Undo" ones
            // are still on the server)
            // Best: continue strictly after the last row shown — a count of
            // rows skips conversations as soon as one left the result (a mail
            // read in "Unread", a label changed in a label view)
            let lastRow = list.find('.its4you-thread-row').last();
            if (lastRow.data('lastat')) {
                params.before_at = lastRow.data('lastat');
                params.before_id = lastRow.data('threadid');
            } else {
                params.offset = list.find('.its4you-thread-row').length;
            }
        }

        app.request.post({data: params}).then(function (err, html) {
            if (requestId !== self.threadListRequest) {
                return;
            }
            if (err) {
                // a list that silently stays as it was shows mail that is
                // gone — one more try, then the user learns about it
                if (!retried) {
                    self.loadThreads(page, append, callback, true);
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_LIST_FAILED')});
                }
                return;
            }
            self.threadListPage = append ? params.page : 1;
            if (append) {
                // New mail arriving between two pages shifts the rows — a
                // thread already listed must not appear a second time
                let page = jQuery('<div></div>').append(html);
                page.find('.its4you-thread-row').each(function () {
                    if (list.find('.its4you-thread-row[data-threadid="' + jQuery(this).data('threadid') + '"]').length) {
                        jQuery(this).remove();
                    }
                });
                list.find('.its4you-load-more-wrap').remove();
                list.append(page.contents());
                self.dropRemovedThreads(list, requestId);
                self.hidePendingThreads(list);
                // the master tick counts the new rows too
                self.updateBulkBar();
            } else {
                if (listKey === self.threadListKey) {
                    ticked = self.getCheckedThreadIds();
                }
                self.threadListKey = listKey;
                list.html(html);
                self.dropRemovedThreads(list, requestId);
                // a delete / spam still waiting for its "Undo" stays out of the list
                self.hidePendingThreads(list);
                // a refresh (sync, an action elsewhere) keeps the selection
                jQuery.each(ticked, function (index, threadId) {
                    list.find('.its4you-thread-row[data-threadid="' + threadId + '"]')
                        .not('.its4you-thread-pending').find('.its4you-thread-check').prop('checked', true);
                });
                self.updateBulkBar();
                // keep the open conversation highlighted after a list refresh
                if (self.currentThreadId) {
                    list.find('.its4you-thread-row[data-threadid="' + self.currentThreadId + '"]').addClass('selected');
                }
            }
            if (typeof callback === 'function') {
                callback();
            }
        });
    },

    /**
     * Opens the follow-up conversation after a delete/move: the one that used
     * to sit below the removed row, then the one above it, then the first
     *
     * @param {number} preferredThreadId the row below the removed one
     * @param {number} fallbackThreadId the row above the removed one
     */
    openNextThread: function (preferredThreadId, fallbackThreadId) {
        let container = this.getContainer(),
            row = container.find('.its4you-thread-row[data-threadid="' + preferredThreadId + '"]');
        if (!row.length && fallbackThreadId) {
            row = container.find('.its4you-thread-row[data-threadid="' + fallbackThreadId + '"]');
        }
        if (!row.length) {
            row = container.find('.its4you-thread-row').not('.its4you-thread-pending').first();
        }
        if (row.length) {
            this.loadConversation(row);
        }
    },

    /**
     * Thread ids of the rows around a thread in the current list — captured
     * BEFORE the list refresh removes the row
     *
     * @param {number} threadId
     * @return {object} {next, prev}
     */
    getNeighborThreads: function (threadId) {
        let row = this.getContainer().find('.its4you-thread-row[data-threadid="' + threadId + '"]');

        // rows waiting for their "Undo" are as good as gone
        return {
            next: row.nextAll('.its4you-thread-row').not('.its4you-thread-pending')
                .first().data('threadid') || 0,
            prev: row.prevAll('.its4you-thread-row').not('.its4you-thread-pending')
                .first().data('threadid') || 0
        };
    },

    /**
     * Delete / move (Spam, Archive, a folder) without a confirmation: the
     * conversations leave the list at once, a card offers "Undo" for
     * UNDO_SECONDS and only then the requests go to the server. One click =
     * one card, whether it is one conversation or a bulk selection. Leaving
     * the page sends what is still waiting right away (registerPendingFlush)
     *
     * @param {number|number[]} threadIds
     * @param {string} action key of UNDO_ACTIONS: delete | spam | archive | restore | folder
     * @param {number} folderId target of the "folder" action
     */
    undoableThreadAction: function (threadIds, action, folderId) {
        let self = this,
            container = this.getContainer(),
            openThreadId = 0,
            neighbors,
            rows = [],
            batchId,
            bar = this.getUndoBar();

        this.pendingThreadActions = this.pendingThreadActions || {};
        threadIds = jQuery.grep(jQuery.makeArray(threadIds), function (threadId) {
            return threadId && !self.isThreadPending(threadId);
        });
        if (!threadIds.length) {
            return;
        }

        jQuery.each(threadIds, function (index, threadId) {
            let row = container.find('.its4you-thread-row[data-threadid="' + threadId + '"]');
            rows.push({
                subject: jQuery.trim(row.find('.its4you-thread-subject').text())
                    || (self.currentThreadId === threadId ? jQuery.trim(container.find('.its4you-conversation-subject').text()) : ''),
                sender: jQuery.trim(row.find('.its4you-thread-from').text())
            });
            row.addClass('hide its4you-thread-pending').find('.its4you-thread-check').prop('checked', false);
            if (self.currentThreadId === threadId) {
                openThreadId = threadId;
            }
        });
        this.updateBulkBar();
        this.updateListBusy();

        if (openThreadId) {
            // the rows are hidden already — the neighbours skip them
            neighbors = this.getNeighborThreads(openThreadId);
            container.find('.its4you-conversation-content').html(
                '<div class="its4you-conversation-empty text-center"><i class="fa-regular fa-comments"></i>'
                + '<p>' + app.vtranslate('JS_SELECT_CONVERSATION') + '</p></div>'
            );
            container.find('.its4you-crm-rail').addClass('hide');
            this.currentThreadId = 0;
            this.openNextThread(neighbors.next, neighbors.prev);
        }

        batchId = ++this.pendingBatchSeq;
        this.pendingThreadActions[batchId] = {
            threadIds: threadIds,
            action: action,
            folderId: folderId || 0,
            openThreadId: openThreadId,
            item: this.buildUndoCard(batchId, rows, action).appendTo(bar),
            timer: setTimeout(function () {
                self.commitThreadAction(batchId);
            }, this.UNDO_SECONDS * 1000)
        };
    },

    /**
     * The "Undo" card of one batch: what happened, which conversations (the
     * first UNDO_LISTED subjects, then "+ n more") and the Undo button
     *
     * @param {number} batchId
     * @param {object[]} rows [{subject, sender}]
     * @param {string} action key of UNDO_ACTIONS
     * @return {jQuery}
     */
    buildUndoCard: function (batchId, rows, action) {
        let self = this,
            single = rows.length === 1,
            texts = this.UNDO_ACTIONS[action],
            title = single
                ? app.vtranslate(texts[1])
                : app.vtranslate(texts[2]).replace('%s', rows.length),
            text = jQuery('<div class="its4you-undo-text"></div>')
                .append(jQuery('<div class="its4you-undo-action"></div>')
                    .append(jQuery('<i></i>').addClass(texts[0]))
                    .append(document.createTextNode(' ' + title)));

        jQuery.each(rows.slice(0, single ? 1 : this.UNDO_LISTED), function (index, row) {
            text.append(jQuery('<div class="its4you-undo-subject"></div>')
                .toggleClass('its4you-undo-subject-small', !single).text(row.subject));
            if (single) {
                text.append(jQuery('<div class="its4you-undo-sender"></div>').text(row.sender));
            }
        });
        if (rows.length > this.UNDO_LISTED) {
            text.append(jQuery('<div class="its4you-undo-sender"></div>')
                .text(app.vtranslate('JS_AND_MORE').replace('%s', rows.length - self.UNDO_LISTED)));
        }

        return jQuery('<div class="its4you-undo-item"></div>')
            .attr('data-batch', batchId)
            .append(text)
            .append(jQuery('<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm its4you-undo-link"></a>')
                .append('<i class="fa-solid fa-undo"></i>')
                .append(document.createTextNode(' ' + app.vtranslate('JS_UNDO'))))
            .append(jQuery('<div class="its4you-undo-progress"></div>')
                .css('animation-duration', this.UNDO_SECONDS + 's'));
    },

    /**
     * @param {object} pending batch of pendingThreadActions
     * @param {number} threadId
     * @return {object} request data of one conversation
     */
    pendingThreadParams: function (pending, threadId) {
        let params = {
            module: app.getModuleName(),
            action: pending.action === 'delete' ? 'ProDeleteThread' : 'ProMoveThread',
            mailbox: this.mailboxOf(threadId),
            thread: threadId
        };
        if (pending.action !== 'delete') {
            // spam | archive | restore | folder (+ the folder picked)
            params.target = pending.action;
        }
        if (pending.action === 'folder') {
            params.folderid = pending.folderId;
        }

        return params;
    },

    /**
     * @param {number} threadId
     * @return {boolean} the conversation waits for its "Undo"
     */
    isThreadPending: function (threadId) {
        let found = false;
        jQuery.each(this.pendingThreadActions || {}, function (batchId, pending) {
            found = found || jQuery.inArray(threadId, pending.threadIds) !== -1;
        });

        return found;
    },

    /**
     * The "Undo" time ran out — the batch really happens, one conversation
     * after another (the mail server works on one at a time anyway)
     *
     * @param {number} batchId
     */
    commitThreadAction: function (batchId) {
        let self = this,
            pending = (this.pendingThreadActions || {})[batchId],
            failed = '',
            next = function () {
                let threadId = pending.threadIds[0];
                if (!threadId) {
                    delete self.pendingThreadActions[batchId];
                    self.updateListBusy();
                    if (failed !== '') {
                        app.helper.showErrorNotification({message: failed});
                        // what could not be removed is still there — the list shows it again
                        self.loadThreads(1, false);
                    } else if (jQuery.isEmptyObject(self.pendingThreadActions)) {
                        // the list tops up right away; the folder counts come
                        // from the mail server — a quiet sync (slower) repaints
                        // the sidebar badges once nothing else waits for its "Undo"
                        if (self.threadListPage <= 1) {
                            self.loadThreads(1, false);
                        }
                        self.refreshSidebarCounts();
                        self.syncNow(true);
                    }
                    return;
                }
                pending.inFlight = threadId;
                app.request.post({data: self.pendingThreadParams(pending, threadId)}).then(function (err, data) {
                    pending.threadIds.shift();
                    pending.inFlight = 0;
                    if (err || !data || !data.success) {
                        failed = app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED');
                        self.returnFailedThread(pending, threadId);
                    } else {
                        self.getContainer().find('.its4you-thread-row[data-threadid="' + threadId + '"]').remove();
                        // a list answer asked for before this moment may still
                        // carry the row — it must not bring it back
                        self.removedThreads = self.removedThreads || {};
                        self.removedThreads[threadId] = self.threadListRequest;
                    }
                    next();
                });
            };
        if (!pending || pending.committing) {
            return;
        }
        // the batch stays in the queue until its last request is back — a
        // reloaded list keeps its rows hidden and leaving the page sends the rest
        pending.committing = true;
        pending.item.remove();
        next();
    },

    /**
     * The server refused the action for one conversation — it is still where it was:
     * its row shows again and, when it was the open one and nothing else opened since
     * (a deep link has no neighbours), the conversation opens again
     *
     * @param {object} pending batch of pendingThreadActions
     * @param {number} threadId
     */
    returnFailedThread: function (pending, threadId) {
        let row = this.getContainer().find('.its4you-thread-row[data-threadid="' + threadId + '"]')
            .removeClass('hide its4you-thread-pending');

        if (pending.openThreadId !== threadId || this.currentThreadId) {
            return;
        }
        if (row.length) {
            this.loadConversation(row);
        } else {
            this.loadConversationById(threadId);
        }
    },

    /**
     * "Undo" — nothing was sent; the conversations return to the list (and
     * the one that was open opens again)
     *
     * @param {number} batchId
     */
    undoThreadAction: function (batchId) {
        let pending = (this.pendingThreadActions || {})[batchId],
            container = this.getContainer(),
            openRow;
        if (!pending || pending.committing) {
            return;
        }
        clearTimeout(pending.timer);
        delete this.pendingThreadActions[batchId];
        pending.item.remove();
        jQuery.each(pending.threadIds, function (index, threadId) {
            container.find('.its4you-thread-row[data-threadid="' + threadId + '"]').removeClass('hide its4you-thread-pending');
        });
        this.updateListBusy();
        // the master tick counts the returned rows again
        this.updateBulkBar();
        openRow = container.find('.its4you-thread-row[data-threadid="' + pending.openThreadId + '"]');
        if (pending.openThreadId && openRow.length) {
            this.loadConversation(openRow);
        }
    },

    /**
     * A list answer requested before the server confirmed a delete / move
     * was built from the old state — its rows of those conversations go. An
     * answer requested later is the truth (the conversation may rightly be
     * there: the target folder, a label view) and ends the watch
     *
     * @param {jQuery} list .its4you-thread-list
     * @param {number} requestId number of the list request just rendered
     */
    dropRemovedThreads: function (list, requestId) {
        let self = this;
        jQuery.each(this.removedThreads || {}, function (threadId, confirmedAt) {
            if (requestId > confirmedAt) {
                delete self.removedThreads[threadId];
            } else {
                list.find('.its4you-thread-row[data-threadid="' + threadId + '"]').remove();
            }
        });
    },

    /**
     * A label / priority just saved shows in the list rows at once — the
     * list reload that follows only confirms it
     *
     * @param {number[]} threadIds
     * @param {object} params what was saved: labelid and / or priority
     * @param {jQuery} source the clicked menu item (carries the label chip)
     */
    paintRowTags: function (threadIds, params, source) {
        let container = this.getContainer();
        if (typeof params.labelid === 'undefined' && typeof params.priority === 'undefined') {
            return;
        }
        jQuery.each(threadIds, function (index, threadId) {
            let row = container.find('.its4you-thread-row[data-threadid="' + threadId + '"]'),
                tags = row.find('.its4you-thread-tags'),
                chip;
            if (!row.length) {
                return;
            }
            if (!tags.length) {
                tags = jQuery('<div class="its4you-thread-tags"></div>').insertAfter(row.find('.its4you-thread-bottom'));
            }
            if (typeof params.labelid !== 'undefined') {
                tags.find('.its4you-label-chip').remove();
                chip = parseInt(params.labelid, 10) ? source.find('.its4you-label-chip').first().clone() : jQuery();
                tags.prepend(chip);
            }
            if (typeof params.priority !== 'undefined') {
                let subject = row.find('.its4you-thread-subject'),
                    hot = params.priority === 'high' || params.priority === 'urgent';

                row.find('.its4you-priority').remove();
                subject.removeClass('its4you-thread-subject-high its4you-thread-subject-urgent');
                if (params.priority) {
                    // the flag leads the subject; high / urgent colours it too
                    subject.toggleClass('its4you-thread-subject-' + params.priority, hot)
                        .prepend(jQuery('<span class="its4you-priority its4you-subject-flag"><i class="fa-solid fa-flag"></i></span>')
                            .addClass('its4you-priority-' + params.priority));
                }
            }
        });
    },

    /**
     * Keeps the conversations waiting for their "Undo" out of a (re)loaded list
     *
     * @param {jQuery} list .its4you-thread-list
     */
    hidePendingThreads: function (list) {
        jQuery.each(this.pendingThreadActions || {}, function (batchId, pending) {
            jQuery.each(pending.threadIds, function (index, threadId) {
                list.find('.its4you-thread-row[data-threadid="' + threadId + '"]').addClass('hide its4you-thread-pending');
            });
        });
        this.updateListBusy();
    },

    /**
     * A list emptied by a delete / spam still in progress says so instead of
     * looking empty — the next conversations load when the batch is done
     */
    updateListBusy: function () {
        let list = this.getContainer().find('.its4you-thread-list'),
            busy = list.find('.its4you-thread-pending').length > 0
                && !list.find('.its4you-thread-row').not('.its4you-thread-pending').length;

        list.find('.its4you-list-busy').remove();
        list.find('.its4you-load-more-wrap').toggleClass('hide', busy);
        if (busy) {
            list.prepend(jQuery('<div class="its4you-thread-list-empty its4you-list-busy text-center"></div>')
                .append('<i class="fa-solid fa-refresh fa-spin"></i>')
                .append(jQuery('<p></p>').text(app.vtranslate('JS_LIST_BUSY'))));
        }
    },

    /**
     * Leaving the page must not lose a delete / spam still waiting for its
     * "Undo" — each is sent as a beacon (survives the unload)
     */
    registerPendingFlush: function () {
        let self = this;

        window.addEventListener('pagehide', function () {
            jQuery.each(self.pendingThreadActions || {}, function (batchId, pending) {
                clearTimeout(pending.timer);
                jQuery.each(pending.threadIds, function (index, threadId) {
                    let body;
                    // the request of this one is on its way already
                    if (threadId === pending.inFlight) {
                        return;
                    }
                    body = new URLSearchParams(self.pendingThreadParams(pending, threadId));
                    if (typeof csrfMagicName !== 'undefined') {
                        body.append(csrfMagicName, csrfMagicToken);
                    }
                    navigator.sendBeacon('index.php', body);
                });
            });
            self.pendingThreadActions = {};
        });
    },

    /**
     * Bulk selection bar above the list — how many loaded conversations are
     * ticked; the bulk delete needs at least one
     */
    updateBulkBar: function () {
        let container = this.getContainer(),
            rows = container.find('.its4you-thread-row').not('.its4you-thread-pending'),
            checked = rows.find('.its4you-thread-check:checked').length,
            list = container.find('.its4you-thread-list'),
            count = container.find('.its4you-bulk-count');

        // a ticked row is highlighted as a whole
        rows.each(function () {
            let row = jQuery(this);
            row.toggleClass('its4you-thread-checked', row.find('.its4you-thread-check').is(':checked'));
        });

        // nothing ticked — the "selected only" view has nothing to show
        if (checked === 0) {
            list.removeClass('its4you-only-checked');
        }
        count.text(checked ? app.vtranslate('JS_SELECTED_COUNT').replace('%s', checked) : '')
            .toggleClass('active', list.hasClass('its4you-only-checked'))
            .attr('title', count.data(list.hasClass('its4you-only-checked') ? 'all' : 'show'));
        container.find('.its4you-bulk-action').prop('disabled', checked === 0);
        // Spam / Trash open — the way back ("Not spam" / "Restore") stands in for "Move to Spam"
        let junk = rows.first().data('junk') || '',
            restore = container.find('.its4you-bulk-restore');
        restore.toggleClass('hide', junk === '').attr('title', restore.data(junk === 'spam' ? 'titleSpam' : 'titleTrash'));
        container.find('.its4you-bulk-spam').toggleClass('hide', junk !== '');
        // all ticked / some (a dash) / none
        container.find('.its4you-bulk-all')
            .prop('checked', checked > 0 && checked === rows.length)
            .prop('indeterminate', checked > 0 && checked < rows.length);
    },

    /**
     * The mailbox a conversation lies in. The Workspace lists conversations
     * of every mailbox the user works in and they open in place — whatever is
     * then done with one (reply, move, link, state…) must go to its mailbox,
     * not to the one open in the sidebar. Every list row carries its mailbox;
     * it is remembered, the conversation outlives its row (a reloaded list)
     *
     * @param {number} threadId 0 = no conversation (a new mail…)
     * @return {number} its mailbox, the open one when not known
     */
    mailboxOf: function (threadId) {
        let openMailbox = parseInt(this.mailboxId, 10) || 0,
            mailboxId;

        threadId = parseInt(threadId, 10) || 0;
        if (!threadId) {
            return openMailbox;
        }
        this.threadMailboxes = this.threadMailboxes || {};
        mailboxId = parseInt(this.getContainer()
            .find('.its4you-thread-row[data-threadid="' + threadId + '"]').first().attr('data-mailbox'), 10) || 0;
        if (mailboxId) {
            this.threadMailboxes[threadId] = mailboxId;
        }

        return this.threadMailboxes[threadId] || openMailbox;
    },

    /**
     * One bulk update of ticked conversations — a request per mailbox (the
     * selection of the Workspace may span several), answered as one
     *
     * @param {number[]} threadIds
     * @param {object} params what to set (op, labelid, priority, state)
     * @return {Promise} resolved like app.request: (err, {success, updated})
     */
    bulkUpdateRequest: function (threadIds, params) {
        let self = this,
            groups = {},
            deferred = jQuery.Deferred(),
            left,
            updated = 0;

        jQuery.each(threadIds, function (index, threadId) {
            let mailboxId = self.mailboxOf(threadId);
            groups[mailboxId] = groups[mailboxId] || [];
            groups[mailboxId].push(threadId);
        });
        left = Object.keys(groups).length;
        jQuery.each(groups, function (mailboxId, ids) {
            app.request.post({data: jQuery.extend({
                module: app.getModuleName(),
                action: 'ProBulkUpdate',
                mailbox: mailboxId,
                threads: JSON.stringify(ids)
            }, params)}).then(function (err, data) {
                updated += !err && data && data.success ? parseInt(data.updated, 10) || 0 : 0;
                if (--left === 0) {
                    deferred.resolve(null, {success: updated > 0, updated: updated});
                }
            });
        });

        return deferred.promise();
    },

    /**
     * @return {number[]} the conversations ticked in the list
     */
    getCheckedThreadIds: function () {
        let threadIds = [];
        this.getContainer().find('.its4you-thread-row').not('.its4you-thread-pending')
            .find('.its4you-thread-check:checked').each(function () {
                threadIds.push(jQuery(this).closest('.its4you-thread-row').data('threadid'));
            });

        return threadIds;
    },

    /**
     * Ticking conversations, the master tick (the list shown), "selected
     * only" and the bulk delete through the "Undo" queue
     *
     * @param {jQuery} container
     */
    registerBulkSelection: function (container) {
        let self = this;

        // the tick must not open the conversation
        container.on('click', '.its4you-thread-check', function (event) {
            event.stopPropagation();
            self.updateBulkBar();
        });

        // "n selected" — shows only the ticked conversations (they stay
        // ticked), a second click the whole list again
        container.on('click', '.its4you-bulk-count', function () {
            container.find('.its4you-thread-list').toggleClass('its4you-only-checked');
            self.updateBulkBar();
        });

        // the master tick — every conversation of the list shown, or none
        container.on('change', '.its4you-bulk-all', function () {
            container.find('.its4you-thread-row').not('.its4you-thread-pending')
                .find('.its4you-thread-check').prop('checked', jQuery(this).is(':checked'));
            self.updateBulkBar();
        });

        // Archive / Spam / Delete / Move to — through the "Undo" queue
        container.on('click', '.its4you-bulk-queue', function () {
            let threadIds = self.getCheckedThreadIds(),
                action = jQuery(this).data('action');

            // the folders offered are those of the open mailbox — a
            // conversation of another one has no such folder to go to
            if (action === 'folder' && jQuery.grep(threadIds, function (threadId) {
                return self.mailboxOf(threadId) !== self.mailboxOf(0);
            }).length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_MOVE_OTHER_MAILBOX')});
                return;
            }
            self.undoableThreadAction(threadIds, action, jQuery(this).data('folderid'));
        });

        // mark read, label, priority — at once, one request for the selection
        container.on('click', '.its4you-bulk-update', function () {
            let link = jQuery(this),
                threadIds = self.getCheckedThreadIds();
            if (!threadIds.length) {
                return;
            }

            app.helper.showProgress();
            self.bulkUpdateRequest(threadIds, link.data('params')).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                app.helper.showSuccessNotification({
                    message: app.vtranslate('JS_THREADS_UPDATED').replace('%s', data.updated)
                });
                self.paintRowTags(threadIds, link.data('params') || {}, link);
                // the open conversation may be one of them
                if (self.currentThreadId && jQuery.inArray(self.currentThreadId, threadIds) !== -1) {
                    self.refreshConversationHead(self.currentThreadId);
                }
                self.refreshSidebarCounts();
                self.loadThreads(1, false);
            });
        });
    },

    /**
     * Drag & drop of conversations — onto a folder of the open mailbox (the same
     * move with "Undo" as the Move to menu) or onto a label of the sidebar. A ticked
     * row carries the whole selection, any other row itself; a click stays a click
     * (the drag starts after a few pixels)
     *
     * @param {jQuery} container
     */
    registerThreadDrag: function (container) {
        const self = this,
            dropTargets = '.its4you-folder-item, .its4you-filter-item[data-labelid]';

        // the rows are rendered again with every list load — made draggable on the first hover
        container.on('mouseenter', '.its4you-thread-row:not(.ui-draggable):not(.its4you-thread-pending)', function () {
            jQuery(this).draggable({
                appendTo: 'body',
                cancel: 'input, a, button',
                cursorAt: {left: -10, top: -10},
                distance: 6,
                revert: 'invalid',
                revertDuration: 0,
                zIndex: 10000,
                helper: function () {
                    const row = jQuery(this);

                    self.dragThreadIds = row.find('.its4you-thread-check').is(':checked')
                        ? self.getCheckedThreadIds()
                        : [row.data('threadid')];

                    return jQuery('<div class="its4you-drag-helper"></div>')
                        .append('<i class="fa-solid fa-envelope me-2"></i>')
                        .append(document.createTextNode(self.dragThreadIds.length > 1
                            ? app.vtranslate('JS_DRAG_THREADS').replace('%s', self.dragThreadIds.length)
                            : jQuery.trim(row.find('.its4you-thread-subject').text())));
                },
                start: function () {
                    container.find(dropTargets).not('.ui-droppable').droppable({
                        tolerance: 'pointer',
                        hoverClass: 'its4you-drop-hover',
                        accept: '.its4you-thread-row',
                        drop: function () {
                            self.dropThreads(jQuery(this), self.dragThreadIds || []);
                        }
                    });
                    container.addClass('its4you-dragging');
                },
                stop: function () {
                    container.removeClass('its4you-dragging');
                }
            });
        });
    },

    /**
     * @param {jQuery} target the folder or label the conversations were dropped on
     * @param {number[]} threadIds
     */
    dropThreads: function (target, threadIds) {
        const self = this;

        if (!threadIds.length) {
            return;
        }

        if (target.hasClass('its4you-folder-item')) {
            // the folders shown are those of the open mailbox
            if (jQuery.grep(threadIds, function (threadId) {
                return self.mailboxOf(threadId) !== self.mailboxOf(0);
            }).length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_MOVE_OTHER_MAILBOX')});

                return;
            }

            if (parseInt(target.data('folderid'), 10) !== parseInt(this.folderId, 10) || this.workspaceMode) {
                this.undoableThreadAction(threadIds, 'folder', target.data('folderid'));
            }

            return;
        }

        const labelId = target.data('labelid'),
            chipSource = jQuery('<div></div>').append(target.find('.its4you-label-chip').clone().removeClass('hide')),
            done = function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});

                    return;
                }

                self.paintRowTags(threadIds, {labelid: labelId}, chipSource);

                if (self.currentThreadId && jQuery.inArray(self.currentThreadId, threadIds) !== -1) {
                    self.refreshConversationHead(self.currentThreadId);
                }

                self.refreshSidebarCounts();
            };

        // one conversation like the label menu of its head, more like the bulk action
        if (threadIds.length === 1) {
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProUpdateThread',
                mailbox: this.mailboxOf(threadIds[0]),
                thread: threadIds[0],
                labelid: labelId
            }}).then(done);
        } else {
            this.bulkUpdateRequest(threadIds, {op: 'label', labelid: labelId}).then(done);
        }
    },

    /**
     * Background read-marking — cache + \Seen flag push to the mail server
     * (so the message shows as read in Thunderbird/Outlook too)
     *
     * @param {number} threadId
     */
    markThreadRead: function (threadId) {
        let self = this,
            params = {
                module: app.getModuleName(),
                action: 'ProMarkThreadRead',
                mailbox: this.mailboxOf(threadId),
                thread: threadId
            };
        // the unread badges of the sidebar follow the read conversation
        app.request.post({data: params}).then(function () {
            self.refreshSidebarCounts();
        });
    },

    /**
     * On-demand load of an unscanned folder (Junk, Archive, ...) — a one-off
     * server sync of that folder, then the regular thread list renders it.
     * Large folders load progressively: each call continues from the stored
     * cursor within the server time budget
     *
     * @param {number} folderId
     */
    loadFolderOnDemand: function (folderId) {
        let self = this,
            container = this.getContainer(),
            button = container.find('.its4you-refresh i');

        // the list of the folder left behind may still be on its way — it must
        // not render under this one while the server loads it
        this.threadListRequest++;
        container.find('.its4you-thread-list').html(
            '<div class="its4you-thread-list-empty text-center"><i class="fa-solid fa-refresh fa-spin"></i><p>'
            + app.vtranslate('JS_FOLDER_LOADING') + '</p></div>'
        );
        button.addClass('fa-spin');
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProSyncNow',
            mailbox: this.mailboxId,
            folder: folderId
        }}).then(function (err, data) {
            button.removeClass('fa-spin');
            if (err || !data || !data.success) {
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SYNC_FAILED')});
            }
            self.updateFolderBadges(data && data.unread, data && data.workspace_unread);
            if (self.folderId === folderId) {
                self.loadThreads(1, false);
            }
        });
    },

    /**
     * Opens a folder as if picked in the sidebar — its collapsed parents
     * unfold first so the active entry is visible
     *
     * @param {number} folderId
     */
    openFolder: function (folderId) {
        let container = this.getContainer(),
            item = container.find('.its4you-folder-item[data-folderid="' + folderId + '"]'),
            delimiter = String(item.closest('.its4you-folder-list').data('delimiter') || ''),
            segments = delimiter ? String(item.data('path')).split(delimiter) : [],
            path = '';

        if (!item.length) {
            return;
        }
        segments.slice(0, -1).forEach(function (segment) {
            path = path ? path + delimiter + segment : segment;
            let caret = container.find('.its4you-folder-caret').filter(function () {
                return String(jQuery(this).data('path')) === path;
            });
            if (caret.hasClass('fa-caret-right')) {
                caret.trigger('click');
            }
        });
        item.trigger('click');
    },

    /**
     * Runs the current search on the mail server — the matches the cache
     * does not hold are fetched, then the local search lists them
     *
     * @param {jQuery} button .its4you-search-server
     */
    searchOnServer: function (button) {
        let self = this,
            term = this.searchTerm;

        // a repeated click for the same term resumes at the folder the
        // previous one stopped at
        if (!this.serverSearch || this.serverSearch.term !== term) {
            this.serverSearch = {term: term, resumeFolder: 0};
        }
        button.prop('disabled', true).html('<i class="fa-solid fa-refresh fa-spin"></i>&nbsp;' + app.vtranslate('JS_SEARCHING_SERVER'));
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProServerSearch',
            mailbox: this.mailboxId,
            search: term,
            resume_folder: this.serverSearch.resumeFolder
        }}).then(function (err, data) {
            if (err || !data || !data.success) {
                button.closest('.its4you-load-more-wrap').remove();
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SYNC_FAILED')});
                return;
            }
            // the user typed another term meanwhile — that list is current
            if (self.searchTerm !== term) {
                return;
            }
            self.serverSearch.resumeFolder = data.resume_folder || 0;
            let message = data.imported > 0
                ? app.vtranslate('JS_SERVER_FOUND').replace('%s', data.imported)
                : app.vtranslate('JS_SERVER_NOTHING_NEW');
            if ((data.skipped || []).length) {
                message += ' ' + app.vtranslate('JS_SERVER_SKIPPED').replace('%s', app.htmlEncode(data.skipped.join(', ')));
            }
            if (data.resume_folder) {
                message += ' ' + app.vtranslate('JS_SERVER_SEARCH_MORE');
            }
            app.helper.showSuccessNotification({message: message});
            self.loadThreads(1, false);
        });
    },

    /**
     * Fetches older mail of the open folder from the server and re-reads the
     * last list page — the loaded threads land below the current rows
     * (rows already listed are skipped by loadThreads)
     *
     * @param {jQuery} button .its4you-load-older
     */
    loadOlder: function (button) {
        let self = this,
            folderId = this.folderId,
            list = this.getContainer().find('.its4you-thread-list');

        button.prop('disabled', true).html('<i class="fa-solid fa-refresh fa-spin"></i>&nbsp;' + app.vtranslate('JS_LOADING_OLDER'));
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProLoadOlder',
            mailbox: this.mailboxId,
            folder: folderId
        }}).then(function (err, data) {
            if (err || !data || !data.success) {
                button.closest('.its4you-load-more-wrap').remove();
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SYNC_FAILED')});
                return;
            }
            if (self.folderId !== folderId) {
                return;
            }
            app.helper.showSuccessNotification({
                message: data.imported > 0
                    ? app.vtranslate('JS_OLDER_LOADED').replace('%s', data.imported)
                    : app.vtranslate('JS_NO_OLDER')
            });
            // an empty folder shows its placeholder — render it anew
            if (!list.find('.its4you-thread-row').length) {
                self.loadThreads(1, false);
            } else {
                self.loadThreads(button.data('page'), true);
            }
        });
    },

    syncNow: function (silent) {
        let self = this,
            button = this.getContainer().find('.its4you-refresh i');

        button.addClass('fa-spin');
        let params = {
                module: app.getModuleName(),
                action: 'ProSyncNow',
                mailbox: this.mailboxId,
                // an open Workspace digest asks for its fingerprint (I19)
                digest: this.getContainer().hasClass('its4you-digest-open') ? '1' : '0'
            },
            // a click on Refresh opens the sorting window at once and the
            // sorting runs in it step by step — the sync itself sorts nothing
            watched = !silent && String(this.getContainer().data('triage')) === '1' && this.openTriageWindow();
        if (watched) {
            params.triage = 0;
        }
        app.request.post({data: params}).then(function (err, data) {
            button.removeClass('fa-spin');
            if (err || !data || !data.success) {
                if (watched) {
                    self.triageForce = false;
                    self.closeTriageWindow(0);
                }
                if (!silent) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SYNC_FAILED')});
                }
                return;
            }
            // The provider refuses the account — its own window says why and what
            // helps. A Refresh the user clicked does not trust what is remembered:
            // its sorting asks the provider again and reports what it answers
            self.showAiOutage(data.ai_outage);
            let block = watched ? null : data.ai_block;
            if (block) {
                self.showAiBlock(block, false);
            }
            if (watched && !(parseInt(data.triage_pending, 10) > 0)) {
                // nothing new to sort — the window says so and leaves
                self.triageForce = false;
                self.updateTriageStatus(app.vtranslate('JS_TRIAGE_NOTHING'));
                self.closeTriageWindow(1500);
            }
            self.updateFolderBadges(data.unread, data.workspace_unread);
            self.refreshSidebarCounts();
            // an open Workspace digest follows new mail (I19) — sections and lines, the kept summary stays
            if (self.refreshWorkspaceDigestAfterSync) {
                self.refreshWorkspaceDigestAfterSync(data);
            }
            // New mail a label moved away by itself must not vanish unnoticed,
            // and more may wait than one pass sorts (a Monday morning) — one
            // window shows both: what was sorted and what is being sorted.
            // Only when it cannot open (another modal, the composer) the
            // cards in the corner report the moves
            if (!self.showTriageQueue(data.triage_done, block ? 0 : parseInt(data.triage_pending, 10) || 0)) {
                self.showAutoMoved(data.auto_moved);
            }
            // The background tick must not throw away the pages the user
            // loaded with "Load more" — the badges show the new mail, the
            // list refreshes on the next manual Refresh / folder click
            if (silent && self.threadListPage > 1) {
                return;
            }
            self.loadThreads(1, false);
        });
    },

    /**
     * New mail a label moved to Spam / Trash by itself — a card per folder
     * that stays until the user closes it (a toast was gone before it was
     * read): what was moved, a way to the folder. Light, with the folder's
     * icon — not to be taken for the dark "Undo" card of an own action.
     * Further moves while the card is open add to it
     *
     * @param {object} moved type (spam | trash) => {folderid, items: [{subject, sender}]}
     */
    showAutoMoved: function (moved) {
        let self = this,
            bar = this.getUndoBar();

        jQuery.each(this.AUTO_MOVED, function (type, texts) {
            let entry = (moved || {})[type],
                card = bar.find('.its4you-auto-item[data-type="' + type + '"]'),
                items;
            if (!entry || !entry.items || !entry.items.length) {
                return;
            }
            items = (card.data('items') || []).concat(entry.items);
            card.remove();

            card = jQuery('<div class="its4you-undo-item its4you-auto-item"></div>')
                .addClass('its4you-auto-' + type)
                .attr('data-type', type)
                .data('items', items);
            let text = jQuery('<div class="its4you-undo-text"></div>')
                .append(jQuery('<div class="its4you-undo-action"></div>')
                    .append(jQuery('<i></i>').addClass(texts[0]))
                    .append(document.createTextNode(' ' + app.vtranslate(texts[1]).replace('%s', items.length))));
            // the newest first
            jQuery.each(items.slice(-self.UNDO_LISTED).reverse(), function (index, item) {
                text.append(jQuery('<div class="its4you-undo-subject its4you-undo-subject-small"></div>')
                    .text(item.subject).attr('title', item.sender));
            });
            if (items.length > self.UNDO_LISTED) {
                text.append(jQuery('<div class="its4you-undo-sender"></div>')
                    .text(app.vtranslate('JS_AND_MORE').replace('%s', items.length - self.UNDO_LISTED)));
            }
            card.append(text)
                .append(jQuery('<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm its4you-auto-open"></a>')
                    .attr('data-folderid', entry.folderid)
                    .append('<i class="fa-regular fa-folder-open"></i>')
                    .append(document.createTextNode(' ' + app.vtranslate('JS_AUTO_MOVED_OPEN'))))
                .append('<a href="javascript:void(0);" class="its4you-auto-close"><i class="fa-solid fa-times"></i></a>')
                .appendTo(bar);
        });
    },

    /**
     * @return {jQuery} the bar of the "Undo" and "moved automatically" cards
     */
    getUndoBar: function () {
        let self = this,
            bar = jQuery('.its4you-undo-bar');

        if (!bar.length) {
            bar = jQuery('<div class="its4you-undo-bar"></div>').appendTo('body');
            bar.on('click', '.its4you-undo-link', function () {
                self.undoThreadAction(jQuery(this).closest('.its4you-undo-item').data('batch'));
            });
            bar.on('click', '.its4you-auto-close', function () {
                jQuery(this).closest('.its4you-auto-item').remove();
            });
            bar.on('click', '.its4you-auto-open', function () {
                self.openFolder(jQuery(this).data('folderid'));
                jQuery(this).closest('.its4you-auto-item').remove();
            });
        }

        return bar;
    },

    /**
     * Repaints the sidebar unread badges from a SyncNow response — new mail
     * in Junk shows up without a page reload
     *
     * @param {object} unread map folderid -> unread count
     */
    updateFolderBadges: function (unread, workspaceUnread) {
        if (!unread) {
            return;
        }
        let setBadge = function (item, count) {
            let badge = item.find('.its4you-folder-unread');
            if (count > 0) {
                if (!badge.length) {
                    badge = jQuery('<span class="its4you-folder-unread"></span>').appendTo(item);
                }
                badge.text(count);
            } else {
                badge.remove();
            }
        };
        this.getContainer().find('.its4you-folder-item').each(function () {
            let item = jQuery(this);
            // the Workspace entry is no mail folder — its own count follows
            if (item.hasClass('its4you-workspace-item')) {
                setBadge(item, parseInt(workspaceUnread, 10) || 0);
                return;
            }
            setBadge(item, parseInt(unread[item.data('folderid')], 10) || 0);
        });
    },

    /**
     * Repaints the sidebar numbers — the unread badges of the folders and
     * the Workspace, the counts of the label and priority filters — after a
     * read, delete, move, label / priority change or new mail
     */
    refreshSidebarCounts: function () {
        let self = this,
            container = this.getContainer(),
            setCount = function (item, counts) {
                let badge = item.find('.its4you-filter-count'),
                    count = parseInt((counts || {}).unread, 10) || 0;
                // the badge = unread conversations, the total in the tooltip
                item.attr('title', app.vtranslate('JS_FILTER_TOTAL')
                    .replace('%s', parseInt((counts || {}).total, 10) || 0));
                if (count > 0) {
                    if (!badge.length) {
                        badge = jQuery('<span class="its4you-filter-count"></span>').appendTo(item);
                    }
                    badge.text(count);
                } else {
                    badge.remove();
                }
            };

        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProSidebarCounts',
            mailbox: this.mailboxId
        }}).then(function (err, data) {
            if (err || !data || !data.success) {
                return;
            }
            self.updateFolderBadges(data.unread, data.workspace_unread);
            container.find('.its4you-filter-item').each(function () {
                let item = jQuery(this);
                if (item.data('labelid')) {
                    setCount(item, data.labels[item.data('labelid')]);
                } else if (item.data('priority')) {
                    setCount(item, data.priorities[item.data('priority')]);
                }
            });
        });
    },

    /**
     * The bar "Main AI (Claude) is unavailable since 12:40 – the backup (OpenAI) runs" (I17)
     *
     * @param {object|null} outage {text} from the background refresh, null = none
     */
    showAiOutage: function (outage) {
        const bar = jQuery('.its4you-ai-outage-bar');

        bar.find('.its4you-ai-outage-text').text(outage ? outage.text : '');
        bar.toggleClass('hide', !outage);
    },

    /**
     * Shows the open folder, label or priority as the page name in the top bar
     * (next to the module name) — the view name of the URL would always say Inbox
     */
    updatePageTitle: function () {
        const container = this.getContainer(),
            item = container.find('.its4you-filter-item.active').first().length
                ? container.find('.its4you-filter-item.active').first()
                : container.find('.its4you-folder-item.active').first(),
            name = item.find('.its4you-folder-name, .its4you-filter-name').first().text().trim(),
            target = jQuery('.module-title').first().closest('.col-auto').nextAll('a.fs-4.text-secondary').last();

        if (name && target.length) {
            target.text(name).attr('title', name);
        }
    },

    /**
     * Folder tree, sidebar filters, search and the thread list
     *
     * @param {jQuery} container
     */
    registerThreadListEvents: function (container) {
        let self = this;

        this.registerPendingFlush();
        this.registerBulkSelection(container);
        this.registerThreadDrag(container);

        // runs after the folder / filter handlers below have set the active item
        container.on('click', '.its4you-folder-item, .its4you-filter-item', function () {
            setTimeout(function () {
                self.updatePageTitle();
            }, 0);
        });
        self.updatePageTitle();

        // Mailbox switcher and My settings — the sidebar scrolls and would clip
        // the menus; they open as fixed layers (data-bs-popper-config strategy
        // "fixed" in the template) and a scroll of the sidebar closes them
        container.find('.its4you-inbox-sidebar').on('scroll', function () {
            container.find('.its4you-mailbox-card.show, .its4you-my-settings-link.show').each(function () {
                const dropdown = bootstrap.Dropdown.getInstance(this);

                if (dropdown) {
                    dropdown.hide();
                }
            });
        });

        // Folder tree — subfolders start collapsed; the caret expands one
        // level at a time (same pattern as the wizard folder tree)
        container.on('click', '.its4you-folder-caret', function (event) {
            event.stopPropagation();
            // the caret's own list — the Workspace list above carries no delimiter
            let caret = jQuery(this),
                delimiter = String(caret.closest('.its4you-folder-list').data('delimiter') || ''),
                prefix = String(caret.data('path')) + delimiter,
                depth = parseInt(caret.data('depth'), 10),
                expanding = caret.hasClass('fa-caret-right');

            caret.toggleClass('fa-caret-right', !expanding).toggleClass('fa-caret-down', expanding);

            container.find('.its4you-folder-item').each(function () {
                let item = jQuery(this);
                if (String(item.data('path')).indexOf(prefix) !== 0) {
                    return;
                }
                if (expanding) {
                    if (parseInt(item.data('depth'), 10) === depth + 1) {
                        item.removeClass('hide');
                    }
                } else {
                    item.addClass('hide');
                    item.find('.its4you-folder-caret').removeClass('fa-caret-down').addClass('fa-caret-right');
                }
            });
        });

        container.on('click', '.its4you-folder-item', function () {
            let item = jQuery(this);
            container.find('.its4you-folder-item, .its4you-filter-item').removeClass('active');
            item.addClass('active');
            // Switching views leaves search mode and the sidebar filters
            self.searchTerm = '';
            self.labelFilter = 0;
            self.priorityFilter = '';
            container.find('.its4you-search-input').val('');
            container.find('.its4you-search-clear').addClass('hide');
            // The Workspace entry is a virtual view, not a mail folder
            self.setWorkspaceMode(item.hasClass('its4you-workspace-item'));
            self.setListTabsVisible(true);
            if (self.workspaceMode) {
                self.loadThreads(1, false);
                return;
            }
            self.folderId = item.data('folderid');
            if (item.data('scanned') !== 1) {
                // Unscanned folders load on demand from the mail server
                self.loadFolderOnDemand(self.folderId);
            } else {
                self.loadThreads(1, false);
            }
        });

        // Sidebar label / priority filters — mailbox-wide virtual views;
        // clicking the active one again returns to the folder view
        container.on('click', '.its4you-filter-item', function () {
            let item = jQuery(this),
                wasActive = item.hasClass('active');

            container.find('.its4you-filter-item').removeClass('active');
            self.labelFilter = 0;
            self.priorityFilter = '';
            self.searchTerm = '';
            container.find('.its4you-search-input').val('');
            container.find('.its4you-search-clear').addClass('hide');

            if (wasActive) {
                // deselect — back home, to the Workspace
                container.find('.its4you-workspace-item').addClass('active');
                self.setWorkspaceMode(true);
                self.setListTabsVisible(true);
                self.loadThreads(1, false);
                return;
            }

            container.find('.its4you-folder-item').removeClass('active');
            item.addClass('active');
            self.setWorkspaceMode(false);
            self.setListTabsVisible(false);
            self.labelFilter = item.data('labelid') || 0;
            self.priorityFilter = item.data('priority') || '';
            self.loadThreads(1, false);
        });

        container.on('click', '.its4you-thread-row', function () {
            // a conversation of another mailbox (the Workspace spans them)
            // opens right here — everything done with it goes to ITS mailbox
            // (mailboxOf), so a reply leaves from the right address
            self.loadConversation(jQuery(this));
        });

        // Row hover quick action — mark done without opening the thread
        container.on('click', '.its4you-row-state', function (event) {
            event.stopPropagation();
            self.updateThreadState(
                [jQuery(this).closest('.its4you-thread-row').data('threadid')],
                {state: jQuery(this).data('state')}
            );
        });

        // Row hover quick actions — spam / delete without opening the thread
        container.on('click', '.its4you-row-action', function (event) {
            event.stopPropagation();
            self.undoableThreadAction(
                jQuery(this).closest('.its4you-thread-row').data('threadid'),
                jQuery(this).data('action')
            );
        });

        container.on('click', '.its4you-load-more', function () {
            self.loadThreads(jQuery(this).data('page'), true);
        });

        // End of the cached folder — the next batch of history from the server
        container.on('click', '.its4you-load-older', function () {
            self.loadOlder(jQuery(this));
        });

        // End of the search results — the same search on the mail server
        container.on('click', '.its4you-search-server', function () {
            self.searchOnServer(jQuery(this));
        });

        container.on('click', '.its4you-refresh', function () {
            let activeFolder = container.find('.its4you-folder-item.active');
            if (activeFolder.length && !activeFolder.hasClass('its4you-workspace-item')
                && activeFolder.data('scanned') !== 1) {
                // Refresh of an on-demand folder continues its server fetch
                self.loadFolderOnDemand(self.folderId);
                return;
            }
            self.syncNow(false);
        });

        // Mailbox search — debounced, spans all folders of the mailbox
        container.on('input', '.its4you-search-input', function () {
            let value = jQuery(this).val().trim();
            container.find('.its4you-search-clear').toggleClass('hide', value === '');
            clearTimeout(self.searchTimer);
            self.searchTimer = setTimeout(function () {
                if (value === self.searchTerm) {
                    return;
                }
                self.searchTerm = value;
                self.loadThreads(1, false);
            }, 300);
        });
        container.on('click', '.its4you-search-clear', function () {
            let input = container.find('.its4you-search-input');
            input.val('');
            jQuery(this).addClass('hide');
            if (self.searchTerm !== '') {
                self.searchTerm = '';
                self.loadThreads(1, false);
            }
            input.trigger('focus');
        });

        // Shared mailbox teamwork — assignment tabs above the thread list
        container.on('click', '.its4you-list-tab, .its4you-folder-item, .its4you-filter-item', function () {
            // a search typed a moment ago must not start under the new view
            clearTimeout(self.searchTimer);
        });
        container.on('click', '.its4you-list-tab', function () {
            let tab = jQuery(this);
            container.find('.its4you-list-tab').removeClass('active');
            tab.addClass('active');
            self.listFilter = tab.data('filter');
            self.loadThreads(1, false);
        });
    }
};
