/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The open conversation — loading, message frames, toolbar moves and the
 * conversation head
 *
 * Mixin of MailManager_ProIndex_Js — the methods run on the controller
 * instance (shared state, cross-calls through this/self); loaded before ProIndex.js
 */
window.MailManager_ProConversation_Mixin = {

    /**
     * A keyboard shortcut applies only to the inbox in view: no modifier key, not while
     * typing (fields, the editor), with the composer or a window open, or in My settings
     *
     * @param {KeyboardEvent} event
     * @param {jQuery} container
     * @return {boolean}
     */
    isShortcutAllowed: function (event, container) {
        const target = jQuery(event.target);

        return !(event.ctrlKey || event.altKey || event.metaKey
            || target.is('input, textarea, select, [contenteditable], [contenteditable] *')
            || jQuery('.its4you-composer-overlay:visible, .modal.show').length
            || !container.is(':visible') || container.hasClass('its4you-settings-open'));
    },

    /**
     * Loads a conversation by row click
     *
     * @param {jQuery} row clicked thread row
     */
    loadConversation: function (row) {
        let container = this.getContainer();
        container.find('.its4you-thread-row').removeClass('selected');
        row.addClass('selected').removeClass('unread');
        this.loadConversationById(row.data('threadid'));
    },

    /**
     * Loads a conversation panel + CRM rail by thread id (row optional —
     * used by the record-widget deep link)
     *
     * @param {number} threadId
     */
    loadConversationById: function (threadId) {
        this.currentThreadId = threadId;
        let self = this,
            container = this.getContainer(),
            content = container.find('.its4you-conversation-content'),
            params = {
                module: app.getModuleName(),
                view: 'ProIndexAjax',
                mode: 'conversation',
                mailbox: this.mailboxOf(threadId),
                thread: threadId
            };

        this.loadCrmPanel(threadId);
        this.markThreadRead(threadId);
        // the assistant tab shows the chat of the conversation opened now
        if (this.assistantOpen) {
            this.loadAssistant(threadId);
        }

        app.request.post({data: params}).then(function (err, html) {
            // a quicker click on another thread already took the panel
            if (err || self.currentThreadId !== threadId) {
                return;
            }
            content.html(html);
            // Frames grow to their full content height (no inner scrollbar) —
            // the conversation panel is the single scrolling element. Older
            // messages are collapsed, so the panel stays at the top and the
            // AI summary remains visible (no auto-scroll to the newest mail)
            // collapsed messages keep their HTML lazy (data-src) — only the
            // expanded newest frame renders and gets watched now
            content.find('.its4you-message-frame[srcdoc]').each(function () {
                self.watchFrame(this);
            });
            // the CRM rail response may have arrived before this render
            self.placeAiBanner();
            self.maybeTriageThread(threadId);
        });
    },

    /**
     * Lazy AI triage — an opened conversation the sync batch has not
     * classified gets one on-demand AI call; the header (chip, flag) and the
     * triage banner then refresh in place without touching the message list
     *
     * @param {number} threadId
     */
    maybeTriageThread: function (threadId) {
        let self = this,
            container = this.getContainer(),
            content = container.find('.its4you-conversation-content');

        if (parseInt(content.find('.its4you-conversation-head').data('triagePending'), 10) !== 1) {
            return;
        }

        let params = {
            module: app.getModuleName(),
            action: 'ProTriageThread',
            mailbox: this.mailboxOf(threadId),
            thread: threadId
        };
        app.request.post({data: params}).then(function (err, data) {
            if (err || !data || !data.triaged || self.currentThreadId !== threadId) {
                return;
            }
            // refresh the head (chip, flag) from a fresh partial — the messages
            // the user is reading stay untouched; the CRM rail reloads to show
            // the AI suggestion card and the list row gets its chip right away
            self.loadCrmPanel(threadId);
            self.refreshConversationHead(threadId);
            // the triage gave the conversation its label and priority
            self.refreshSidebarCounts();
            self.loadThreads(1, false);
        });
    },

    /**
     * Server-first thread move (Spam / Archive / picked folder) — on success
     * clears the conversation panel and refreshes the list
     *
     * @param {number} threadId
     * @param {object} extraParams MoveThread params (target, folderid)
     * @param {string} confirmMessage '' = no confirmation dialog
     * @param {string} successMessage toast after the move
     */
    /**
     * Saves the own state / owner of a conversation and repaints what shows
     * it — the list (the tab may no longer hold the row) and the open head
     *
     * @param {number[]} threadIds
     * @param {Object} params {state: 'done'|'open'} or {assignee: userid}
     */
    updateThreadState: function (threadIds, params) {
        let self = this,
            left = threadIds.length,
            failed = false;

        jQuery.each(threadIds, function (index, threadId) {
            app.request.post({data: jQuery.extend({
                module: app.getModuleName(),
                action: 'ProUpdateThread',
                mailbox: self.mailboxOf(threadId),
                thread: threadId
            }, params)}).then(function (err, data) {
                failed = failed || !!err || !data || !data.success;
                if (--left > 0) {
                    return;
                }
                if (failed) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                }
                self.loadThreads(1, false);
                if (self.currentThreadId && jQuery.inArray(self.currentThreadId, threadIds) !== -1) {
                    self.refreshConversationHead(self.currentThreadId);
                }
            });
        });
    },

    moveThread: function (threadId, extraParams, confirmMessage, successMessage) {
        let self = this,
            container = this.getContainer(),
            neighbors = this.getNeighborThreads(threadId),
            run = function () {
                app.helper.showProgress();
                app.request.post({data: jQuery.extend({
                    module: app.getModuleName(),
                    action: 'ProMoveThread',
                    mailbox: self.mailboxOf(threadId),
                    thread: threadId
                }, extraParams)}).then(function (err, data) {
                    app.helper.hideProgress();
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});
                        return;
                    }
                    app.helper.showSuccessNotification({message: successMessage});
                    self.refreshSidebarCounts();
                    container.find('.its4you-conversation-content').html(
                        '<div class="its4you-conversation-empty text-center"><i class="fa-regular fa-comments"></i>'
                        + '<p>' + app.vtranslate('JS_SELECT_CONVERSATION') + '</p></div>'
                    );
                    container.find('.its4you-crm-rail').addClass('hide');
                    self.currentThreadId = 0;
                    self.loadThreads(1, false, function () {
                        self.openNextThread(neighbors.next, neighbors.prev);
                    });
                });
            };

        if (confirmMessage) {
            app.helper.showConfirmationBox({message: confirmMessage}).then(run);
        } else {
            run();
        }
    },

    /**
     * Re-renders only the conversation head (chip, flag, assignment) from a
     * fresh partial without touching the messages the user is reading
     *
     * @param {number} threadId
     */
    refreshConversationHead: function (threadId) {
        let content = this.getContainer().find('.its4you-conversation-content'),
            params = {
                module: app.getModuleName(),
                view: 'ProIndexAjax',
                mode: 'conversation',
                mailbox: this.mailboxOf(threadId),
                thread: threadId
            };
        app.request.post({data: params}).then(function (err, html) {
            let head = content.find('.its4you-conversation-head');
            if (err || head.data('threadid') !== threadId) {
                // the user switched to another conversation meanwhile
                return;
            }
            head.replaceWith(jQuery('<div></div>').append(html).find('.its4you-conversation-head'));
        });
    },

    /**
     * Fits a sandboxed message frame to its document height
     *
     * @param {HTMLIFrameElement} frame
     */
    resizeFrame: function (frame) {
        try {
            let body = frame.contentWindow.document.body;
            if (body) {
                frame.style.height = Math.max(body.scrollHeight + 30, 60) + 'px';
            }
        } catch (e) {
            // sandbox denied — keep the min height
        }
    },

    /**
     * Keeps a sandboxed message frame fitted while it loads. The srcdoc DOM
     * renders long before remote newsletter images finish (onload can take
     * many seconds on slow/dead trackers) — early resizes show the text
     * immediately, the later ones absorb the loaded images
     *
     * @param {HTMLIFrameElement} frame
     */
    watchFrame: function (frame) {
        let self = this,
            resize = function () {
                self.resizeFrame(frame);
            };

        frame.setAttribute('scrolling', 'no');
        frame.onload = resize;
        [50, 300, 800, 2000, 4000].forEach(function (delay) {
            setTimeout(resize, delay);
        });
    },

    /**
     * Keeps the action toolbar inside its column: with the texts it would
     * run out of it (a small screen, the CRM panel open, a long language),
     * the buttons show their icons only. Measured, not a fixed breakpoint —
     * the width the texts need differs by language and by the buttons shown
     */
    fitConversationToolbar: function () {
        let toolbar = this.getContainer().find('.its4you-conversation-toolbar');

        if (!toolbar.length) {
            return;
        }
        // the text is the tooltip too — an icon alone must say what it does
        toolbar.find('.its4you-btn-text').each(function () {
            let text = jQuery(this),
                button = text.closest('.btn');
            if (!button.attr('title')) {
                button.attr('title', jQuery.trim(text.text()));
            }
        });
        toolbar.removeClass('its4you-toolbar-compact');
        // the buttons' own widths — not scrollWidth: an open menu reaching
        // out of the row would count in and squeeze the toolbar for nothing
        let items = toolbar.children().not('.its4you-toolbar-spacer'),
            needed = (items.length - 1) * (parseFloat(toolbar.css('column-gap')) || 0);
        items.each(function () {
            needed += this.getBoundingClientRect().width;
        });
        if (needed > toolbar[0].clientWidth + 1) {
            toolbar.addClass('its4you-toolbar-compact');
        }
    },

    /**
     * Refits the toolbar whenever the conversation is (re)rendered or its
     * column changes width
     *
     * @param {jQuery} container
     */
    registerConversationToolbarFit: function (container) {
        let self = this,
            content = container.find('.its4you-conversation-content'),
            fit = function () {
                self.fitConversationToolbar();
            };

        if (!content.length) {
            return;
        }
        if (window.MutationObserver) {
            new MutationObserver(fit).observe(content[0], {childList: true});
        }
        if (window.ResizeObserver) {
            new ResizeObserver(fit).observe(content[0]);
        } else {
            jQuery(window).on('resize', fit);
        }
    },

    /**
     * Open conversation — messages, AI summary, toolbar actions, labels
     *
     * @param {jQuery} container
     */
    /**
     * The preview window of a message's images and PDFs — arrows (buttons and keys)
     * move between them, Download saves the shown one
     *
     * @param {jQuery} chip the clicked attachment
     */
    openAttachmentPreview: function (chip) {
        const self = this,
            chips = chip.closest('.its4you-message-attachments').find('.its4you-attachment-chip[data-preview]'),
            title = jQuery('<h5 class="modal-title text-truncate"></h5>'),
            body = jQuery('<div class="modal-body its4you-preview-body"></div>'),
            download = jQuery('<a class="btn btn-primary active" download></a>')
                .append('<i class="fa-solid fa-download me-2"></i>')
                .append(document.createTextNode(app.vtranslate('JS_ATTACHMENT_DOWNLOAD'))),
            previous = jQuery('<button type="button" class="btn btn-outline-secondary"><i class="fa-solid fa-chevron-left"></i></button>')
                .attr('title', app.vtranslate('JS_ATTACHMENT_PREVIOUS')),
            next = jQuery('<button type="button" class="btn btn-outline-secondary"><i class="fa-solid fa-chevron-right"></i></button>')
                .attr('title', app.vtranslate('JS_ATTACHMENT_NEXT')),
            modal = jQuery('<div class="modal-dialog modal-xl its4you-preview-modal"></div>').append(
                jQuery('<div class="modal-content"></div>')
                    .append(jQuery('<div class="modal-header"></div>')
                        .append('<button type="button" class="btn-close order-last ms-auto" data-bs-dismiss="modal"></button>')
                        .append(title))
                    .append(body)
                    .append(jQuery('<div class="modal-footer justify-content-between"></div>')
                        .append(jQuery('<div class="btn-group"></div>').append(previous, next))
                        .append(download))
            );
        let index = chips.index(chip);

        const show = function (position) {
                const current = chips.eq(position),
                    url = current.attr('href'),
                    message = function (key) {
                        body.empty().append(jQuery('<div class="alert alert-warning m-0"></div>').text(app.vtranslate(key)));
                    };

                index = position;
                title.text(current.data('name'));
                download.attr('href', url);
                previous.prop('disabled', position <= 0);
                next.prop('disabled', position >= chips.length - 1);

                if (String(current.data('ready')) !== '1') {
                    // mail sent from the CRM: its files come with the Sent folder sync
                    message('JS_ATTACHMENT_NOT_READY');

                    return;
                }

                if (current.data('preview') === 'pdf') {
                    const file = new URL(url + '&disposition=preview', window.location.href).href;

                    body.empty().append(jQuery('<iframe class="its4you-preview-frame"></iframe>')
                        .attr('src', 'libraries/jquery/pdfjs/web/viewer.html?file=' + encodeURIComponent(file)));

                    return;
                }

                body.empty().append(jQuery('<img class="its4you-preview-image" alt="">')
                    .on('error', function () {
                        message('JS_ATTACHMENT_PREVIEW_FAILED');
                    })
                    .attr('src', url + '&disposition=inline'));
            },
            onKey = function (event) {
                if (event.key === 'ArrowLeft' && index > 0) {
                    show(index - 1);
                } else if (event.key === 'ArrowRight' && index < chips.length - 1) {
                    show(index + 1);
                }
            };

        previous.on('click', function () {
            show(index - 1);
        });
        next.on('click', function () {
            show(index + 1);
        });
        // one attachment has no neighbours to move to
        previous.add(next).toggleClass('hide', chips.length < 2);

        app.helper.showModal(modal, {cb: function (shown) {
            show(index);
            jQuery(document).on('keydown.its4youPreview', onKey);
            shown.closest('.modal').one('hidden.bs.modal', function () {
                jQuery(document).off('keydown.its4youPreview');
            });
        }});
    },

    registerConversationEvents: function (container) {
        let self = this;

        self.registerConversationToolbarFit(container);

        // Attachment preview — images and PDFs open in a window, the rest downloads
        container.on('click', '.its4you-attachment-chip[data-preview]', function (event) {
            if (event.ctrlKey || event.metaKey || event.shiftKey) {
                return;
            }

            event.preventDefault();
            self.openAttachmentPreview(jQuery(this));
        });

        // Unread again — the newest received message, here and on the mail server
        container.on('click', '.its4you-thread-unread', function () {
            let threadId = jQuery(this).closest('.its4you-conversation-head').data('threadid');

            self.bulkUpdateRequest([threadId], {op: 'unread'}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                container.find('.its4you-thread-row[data-threadid="' + threadId + '"]').addClass('unread');
                self.refreshSidebarCounts();
            });
        });

        // Translation of a received message — the tabs Translation | Original
        // replace the button; the original stays one click away
        container.on('click', '.its4you-translate', function (event) {
            let link = jQuery(this),
                message = link.closest('.its4you-message'),
                threadId = link.closest('.its4you-conversation-content').find('.its4you-conversation-head').data('threadid')
                    || self.currentThreadId;

            // the head itself toggles the message — the click is the button's only
            event.stopPropagation();
            if (link.data('running')) {
                return;
            }
            link.data('running', true).find('.fa-language').addClass('fa-spin');
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProTranslate',
                mailbox: self.mailboxOf(threadId),
                thread: threadId,
                message: link.data('message')
            }}).then(function (err, data) {
                link.data('running', false).find('.fa-language').removeClass('fa-spin');
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_AI_FAILED')});
                    return;
                }
                let tabs = jQuery('<div class="its4you-translate-tabs"></div>')
                    .append(jQuery('<a href="javascript:void(0);" class="active" data-view="translation"></a>')
                        .text(link.data('translation') + (data.language ? ' (' + data.language + ')' : '')))
                    .append(jQuery('<a href="javascript:void(0);" data-view="original"></a>').text(link.data('original')));
                message.removeClass('its4you-message-collapsed');
                message.find('.its4you-message-body').addClass('hide')
                    .before(tabs)
                    .before(jQuery('<div class="its4you-translation-text"></div>').html(data.html));
                link.remove();
            });
        });

        container.on('click', '.its4you-translate-tabs a', function () {
            let tab = jQuery(this),
                message = tab.closest('.its4you-message'),
                original = tab.data('view') === 'original';

            tab.addClass('active').siblings().removeClass('active');
            message.find('.its4you-translation-text').toggleClass('hide', original);
            message.find('.its4you-message-body').toggleClass('hide', !original);
            if (original) {
                // a frame shown again measures its height anew
                message.find('.its4you-message-frame').each(function () {
                    self.resizeFrame(this);
                });
            }
        });

        // Message header toggles the body — expanded messages collapse again
        // on a second click; frame heights are recomputed on expand (a hidden
        // iframe reports zero)
        container.on('click', '.its4you-message-head', function () {
            let message = jQuery(this).closest('.its4you-message');
            if (message.hasClass('its4you-message-collapsed')) {
                message.removeClass('its4you-message-collapsed');
                message.find('.its4you-message-frame').each(function () {
                    // lazy message — the HTML parses on the first expand only
                    if (!this.getAttribute('srcdoc') && jQuery(this).attr('data-src')) {
                        this.setAttribute('srcdoc', jQuery(this).attr('data-src'));
                        self.watchFrame(this);
                    } else {
                        self.resizeFrame(this);
                    }
                });
            } else {
                message.addClass('its4you-message-collapsed');
            }
        });

        // Remote content (images, fonts) of a message is blocked by the frame's
        // Content-Security-Policy until the user allows it for that message
        container.on('click', '.its4you-allow-remote', function (event) {
            const message = jQuery(this).closest('.its4you-message'),
                policy = /^<meta http-equiv="Content-Security-Policy"[^>]*>/i;

            event.stopPropagation();
            message.find('.its4you-message-frame').each(function () {
                const srcdoc = this.getAttribute('srcdoc');

                if (srcdoc) {
                    this.setAttribute('srcdoc', srcdoc.replace(policy, ''));
                    self.watchFrame(this);
                } else if (this.getAttribute('data-src')) {
                    this.setAttribute('data-src', this.getAttribute('data-src').replace(policy, ''));
                }
            });
            message.find('.its4you-remote-blocked').remove();
        });

        // Delete / Spam — no confirmation; the list bar offers "Undo" for a
        // few seconds before the server is asked (ProThreadList.js)
        container.on('click', '.its4you-thread-delete', function () {
            self.undoableThreadAction(jQuery(this).closest('.its4you-conversation-head').data('threadid'), 'delete');
        });

        // Keyboard shortcuts (ITS4YouMailManager 4.76): Delete — the ticked conversations,
        // otherwise the open one, through the same "Undo" bar as the button; ↑ / ↓ — the
        // previous / next conversation of the list; R — reply. Never while typing (fields,
        // the composer, the editor), with a window open or in My settings
        jQuery(document).on('keydown.its4youShortcuts', function (event) {
            if (!self.isShortcutAllowed(event, container)) {
                return;
            }

            if (event.key === 'Delete') {
                let threadIds = self.getCheckedThreadIds();

                if (!threadIds.length && self.currentThreadId) {
                    threadIds = [self.currentThreadId];
                }
                if (threadIds.length) {
                    event.preventDefault();
                    self.undoableThreadAction(threadIds, 'delete');
                }
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                const rows = container.find('.its4you-thread-row:visible'),
                    index = rows.index(rows.filter('.selected')),
                    next = rows.eq(event.key === 'ArrowDown' ? index + 1 : Math.max(index - 1, 0));

                if (next.length && (index === -1 || next[0] !== rows[index])) {
                    event.preventDefault();
                    next.trigger('click');
                    next[0].scrollIntoView({block: 'nearest'});
                }
            } else if ((event.key === 'r' || event.key === 'R') && !event.shiftKey && self.currentThreadId) {
                const reply = container.find('.its4you-conversation-content .its4you-reply-btn').first();

                if (reply.length) {
                    event.preventDefault();
                    reply.trigger('click');
                }
            }
        });

        container.on('click', '.its4you-thread-spam', function () {
            self.undoableThreadAction(jQuery(this).closest('.its4you-conversation-head').data('threadid'), 'spam');
        });

        // Done / Reopen and "Assign to" of a conversation without a record
        container.on('click', '.its4you-thread-state, .its4you-thread-assign', function () {
            let link = jQuery(this),
                threadId = link.closest('.its4you-conversation-head').data('threadid'),
                params = link.hasClass('its4you-thread-assign')
                    ? {assignee: link.data('assignee')}
                    : {state: link.data('state')};

            self.updateThreadState([threadId], params);
        });

        // "Not spam" / "Restore" — back to the Inbox, through the same "Undo" queue
        container.on('click', '.its4you-thread-restore', function () {
            self.undoableThreadAction(jQuery(this).closest('.its4you-conversation-head').data('threadid'), 'restore');
        });

        container.on('click', '.its4you-thread-archive', function () {
            let threadId = jQuery(this).closest('.its4you-conversation-head').data('threadid');
            self.moveThread(threadId, {target: 'archive'}, '', app.vtranslate('JS_THREAD_ARCHIVED'));
        });

        // the conversation's folder in the head — opens it in the sidebar
        container.on('click', '.its4you-open-folder', function () {
            self.openFolder(jQuery(this).data('folderid'));
        });

        // split conversation — the mail in the Inbox joins the rest in its folder
        container.on('click', '.its4you-file-with-thread', function () {
            let threadId = jQuery(this).closest('.its4you-conversation-head').data('threadid');
            self.moveThread(threadId, {target: 'folder', folderid: jQuery(this).data('folderid'), from: 'inbox'},
                '', app.vtranslate('JS_THREAD_MOVED'));
        });

        container.on('click', '.its4you-thread-move', function () {
            let threadId = jQuery(this).closest('.its4you-conversation-head').data('threadid');
            self.moveThread(threadId, {target: 'folder', folderid: jQuery(this).data('folderid')},
                '', app.vtranslate('JS_THREAD_MOVED'));
        });

        // Move-to dropdown — type-ahead folder filter (matches anywhere in
        // the full path, diacritics-insensitive)
        let searchKey = function (text) {
            return String(text).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        };

        container.on('click', '.its4you-move-search input', function (event) {
            // bootstrap closes the dropdown on any menu click — not for typing
            event.stopPropagation();
        });

        container.on('shown.bs.dropdown', '.its4you-move-dd', function () {
            jQuery(this).find('.its4you-move-search input').val('').trigger('input').focus();
        });

        container.on('input', '.its4you-move-search input', function () {
            let term = searchKey(jQuery(this).val()).trim(),
                menu = jQuery(this).closest('.its4you-move-menu');
            menu.find('.its4you-move-item').each(function () {
                let item = jQuery(this);
                item.toggleClass('hide', term !== ''
                    && searchKey(item.find('a').data('path') || item.text()).indexOf(term) === -1);
            });
        });

        // Manual priority — the flag in the conversation head opens a
        // dropdown; the pick overrides the AI's assessment
        container.on('click', '.its4you-priority-set', function () {
            let item = jQuery(this),
                threadId = item.closest('.its4you-conversation-head').data('threadid'),
                priority = item.data('priority');
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProUpdateThread',
                mailbox: self.mailboxOf(threadId),
                thread: threadId,
                priority: priority
            }}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                // the list row first — it must not wait for the reloads below
                self.paintRowTags([threadId], {priority: priority}, item);
                self.refreshConversationHead(threadId);
                self.refreshSidebarCounts();
                self.loadThreads(1, false);
            });
        });

        // "Suspicious content for AI" (I16) — the user checked the mail, the flag goes
        container.on('click', '.its4you-ai-suspicious-safe', function () {
            const threadId = jQuery(this).closest('.its4you-conversation-head').data('threadid');

            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProUpdateThread',
                mailbox: self.mailboxOf(threadId),
                thread: threadId,
                ai_safe: '1'
            }}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});

                    return;
                }

                self.refreshConversationHead(threadId);
            });
        });

        // Manual label change — the chip in the conversation head opens a
        // dropdown; the pick overrides the AI classification
        container.on('click', '.its4you-label-set', function () {
            let item = jQuery(this),
                threadId = item.closest('.its4you-conversation-head').data('threadid'),
                params = {
                    module: app.getModuleName(),
                    action: 'ProUpdateThread',
                    mailbox: self.mailboxOf(threadId),
                    thread: threadId,
                    labelid: jQuery(this).data('labelid')
                };
            app.request.post({data: params}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                // the list row first — it must not wait for the reloads below
                self.paintRowTags([threadId], {labelid: params.labelid}, item);
                self.refreshConversationHead(threadId);
                self.loadCrmPanel(threadId);
                self.refreshSidebarCounts();
                self.loadThreads(1, false);
            });
        });
    }
};
