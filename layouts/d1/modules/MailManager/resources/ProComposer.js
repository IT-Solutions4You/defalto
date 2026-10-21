/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Mail composer — CKEditor overlay, recipients, attachments, AI draft, send
 * and save draft
 *
 * Mixin of the Pro index controller (MailManager_ProIndex_Js) — the methods run
 * on the controller instance (shared state, cross-calls through this/self);
 * loaded before ProIndex.js
 */
/** @var MailManager_ProComposer_Mixin */
window.MailManager_ProComposer_Mixin = {

    /**
     * Opens the composer in a large centered popup overlay. The overlay
     * lives INSIDE the module container — the vtiger .myModal sits outside
     * it and the delegated composer handlers would never fire there
     *
     * @param {string} mode reply | replyall | forward | new | draft
     * @param {number} threadId
     * @param {number} draftId cached messageid when continuing a draft
     */
    openComposer: function (mode, threadId, draftId) {
        let self = this,
            container = this.getContainer(),
            params = {
                module: app.getModuleName(),
                view: 'ProIndexAjax',
                mode: 'composer',
                composeMode: mode,
                mailbox: this.mailboxOf(threadId || 0),
                thread: threadId || 0,
                draft: draftId || 0
            };

        // A double click sends two requests — only the latest may mount,
        // two overlays would share one editor id
        // a mail waiting for its "Undo" goes out first; a failed one comes back
        // instead of the new composer (its text must not be lost)
        if (this.delayedSend) {
            this.delayedSend.go().then(function (sent) {
                if (sent) {
                    self.openComposer(mode, threadId, draftId);
                }
            });

            return;
        }

        let requestId = ++this.composerRequest;
        this.destroyComposer();
        app.request.post({data: params}).then(function (err, html) {
            if (err || requestId !== self.composerRequest) {
                return;
            }
            self.destroyComposer();
            let overlay = jQuery('<div class="its4you-composer-overlay"></div>').append(html);
            // BODY mount — a transformed ancestor inside the page re-anchors
            // position:fixed and cuts the popup; the composer handlers bind
            // to the overlay itself (container delegation cannot reach it)
            jQuery(document.body).append(overlay);
            // an error of the last attempt goes away once the user changes something
            overlay.on('input change', 'input, select, textarea', function () {
                self.clearComposerError();
            });
            overlay.on('click', '.its4you-composer-send', function () {
                // the second send button also marks the conversation done
                self.sendComposer(String(jQuery(this).data('done')) === '1');
            });
            // Esc = Cancel (with its question) — not while a window, the recipient
            // list or an editor dialog is open on top of the composer
            jQuery(document).off('keydown.its4youComposer').on('keydown.its4youComposer', function (event) {
                if (event.key !== 'Escape' || !overlay.is(':visible') || jQuery('.modal.show').length
                    || jQuery('.select2-container--open').length || jQuery('.cke_dialog:visible').length) {
                    return;
                }

                event.preventDefault();
                overlay.find('.its4you-composer-cancel').first().trigger('click');
            });
            overlay.on('click', '.its4you-composer-cancel', function () {
                // text the user wrote is not thrown away without a word
                if (self.htmlToText(self.getOwnComposerText(true)) === '') {
                    self.destroyComposer();
                    return;
                }
                app.helper.showConfirmationBox({message: app.vtranslate('JS_COMPOSER_DISCARD_CONFIRM')})
                    .then(function () {
                        self.destroyComposer();
                    });
            });
            // "Send later" — the time field opens at the next full hour
            overlay.on('click', '.its4you-schedule-toggle', function () {
                const row = overlay.find('.its4you-schedule-row').toggleClass('hide'),
                    input = row.find('.its4you-schedule-time');

                // the next full hour in the user's CRM time zone (the server knows it)
                if (!row.hasClass('hide') && !input.val()) {
                    input.val(overlay.find('.its4you-composer').data('schedule-default'));
                }
            });
            overlay.on('click', '.its4you-schedule-send', function () {
                const value = overlay.find('.its4you-schedule-time').val();

                self.clearComposerError();

                if (!value) {
                    self.showComposerError(app.vtranslate('JS_SCHEDULE_INVALID'), ['.its4you-schedule-time']);

                    return;
                }

                self.sendComposer(false, value);
            });
            overlay.on('click', '.its4you-composer-savedraft', function () {
                self.saveDraftComposer();
            });
            // Attachments — kept in our own array; a FileList is immutable, so
            // removing a single file means rebuilding the list ourselves
            self.composerFiles = [];
            overlay.on('click', '.its4you-attach-btn', function () {
                overlay.find('.its4you-attach-input').trigger('click');
            });
            overlay.on('change', '.its4you-attach-input', function () {
                jQuery.each(this.files, function (index, file) {
                    self.composerFiles.push(file);
                });
                jQuery(this).val('');
                self.renderComposerFiles(overlay);
            });
            overlay.on('click', '.its4you-attach-remove', function () {
                self.composerFiles.splice(parseInt(jQuery(this).data('index'), 10), 1);
                self.renderComposerFiles(overlay);
            });
            overlay.on('change', '.its4you-template-select', function () {
                const select = jQuery(this);

                if (select.val()) {
                    self.applyEmailTemplate(overlay, select.val());
                    select.val('');
                }
            });
            overlay.on('click', '.its4you-template-document-remove', function () {
                jQuery(this).closest('.its4you-composer-attachment').remove();
                overlay.find('.its4you-template-documents').toggleClass('hide', !overlay.find('.its4you-template-document').length);
            });
            overlay.on('click', '.its4you-ai-draft', function () {
                self.generateAiDraft(jQuery(this));
            });
            overlay.on('click', '.its4you-ai-skip', function () {
                // uncover only — the running generation still fills the
                // editor when it arrives
                overlay.find('.its4you-ai-generating').remove();
            });
            overlay.on('click', '.its4you-ai-undo', function () {
                if (window.CKEDITOR && CKEDITOR.instances.its4youComposerBody && self.aiDraftOriginal) {
                    CKEDITOR.instances.its4youComposerBody.setData(self.aiDraftOriginal);
                    self.aiDraftOriginal = '';
                }
                jQuery(this).addClass('hide');
            });
            // Ticket-linked reply — by mail or by a comment on the ticket; the
            // last choice opens preselected (a per-browser convenience)
            overlay.on('click', '.its4you-composer-channel .tab-item', function () {
                self.setReplyChannel(overlay, jQuery(this).data('channel'));
            });
            if (overlay.find('.its4you-composer-channel').length) {
                self.setReplyChannel(overlay, self.readReplyChannel());
            }
            // Copy / blind copy rows open on demand and close again while empty
            overlay.on('click', '.its4you-copy-link', function () {
                let link = jQuery(this);

                link.addClass('hide');
                overlay.find('.its4you-copy-row[data-row="' + link.data('row') + '"]').removeClass('hide')
                    .find('input').focus();
            });
            overlay.on('click', '.its4you-copy-close', function () {
                let row = jQuery(this).closest('.its4you-copy-row');

                row.addClass('hide').find('input').val('');
                overlay.find('.its4you-copy-link[data-row="' + row.data('row') + '"]').removeClass('hide');
            });
            self.initRecipientPicker(overlay);
            self.aiDraftOriginal = '';
            new Vtiger_CkEditor_Js().loadCkEditor(jQuery('#its4youComposerBody'), {height: '280px'});
            // The AI button follows the editor content: empty = generate a
            // draft, written = improve it ("našepkávač"). CKEditor loads its
            // script async on the first open and the instance flips to
            // 'ready' async on every open — polling covers both races
            let waitTries = 0;
            clearInterval(self.editorWaitTimer);
            self.editorWaitTimer = setInterval(function () {
                let aiEditor = window.CKEDITOR && CKEDITOR.instances
                    && CKEDITOR.instances.its4youComposerBody;
                if (!aiEditor || aiEditor.status !== 'ready') {
                    if (++waitTries > 100) {
                        clearInterval(self.editorWaitTimer);
                    }
                    return;
                }
                clearInterval(self.editorWaitTimer);
                aiEditor.on('change', function () {
                    self.clearComposerError();
                });
                // a reply the AI assistant proposed — its text above the signature
                if (self.pendingComposerDraft) {
                    let parts = self.splitSignature(aiEditor.getData());
                    aiEditor.setData('<div class="its4you-ai-draft">' + self.pendingComposerDraft + '</div>'
                        + (parts.signature ? '<br>' + parts.signature : ''));
                    self.pendingComposerDraft = '';
                    self.updateAiDraftMode();
                    return;
                }
                self.updateAiDraftMode();
                // ready to type — the caret on the first line, above the
                // signature (a continued draft keeps it at its start as well)
                try {
                    let range = aiEditor.createRange();

                    aiEditor.focus();
                    range.moveToElementEditStart(aiEditor.editable());
                    range.select();
                } catch (e) {
                    // an editor without a selection yet — the user clicks into it as before
                }
                aiEditor.on('change', function () {
                    clearTimeout(self.aiModeTimer);
                    self.aiModeTimer = setTimeout(function () {
                        self.updateAiDraftMode();
                    }, 300);
                });
                // Ticket-linked reply — the draft starts generating on its
                // own (server-side gate: data-auto-draft). Empty means no
                // TEXT: browser extensions (Grammarly) inject markup into
                // the editable area, raw getData() is never ''
                let composer = overlay.find('.its4you-composer'),
                    editorText = self.htmlToText(self.getOwnComposerText(true));
                if (composer.data('auto-draft') === 1 && editorText === '') {
                    // full cover = a visible intermediate step; the skip link
                    // uncovers the form while the draft keeps generating
                    composer.append(
                        '<div class="its4you-ai-generating"><i class="fa-solid fa-spinner fa-spin"></i>'
                        + '<div>' + app.vtranslate('JS_AI_GENERATING') + '</div>'
                        + '<a href="javascript:void(0);" class="btn btn-link its4you-ai-skip">'
                        + app.vtranslate('JS_AI_SKIP') + '</a></div>'
                    );
                    self.generateAiDraft(overlay.find('.its4you-ai-draft'));
                }
            }, 100);
            // original-mail preview frame fits its content like the
            // conversation frames do
            overlay.find('.its4you-message-frame').each(function () {
                self.watchFrame(this);
            });
        });
    },

    // localStorage key of the reply channel used last on a ticket conversation
    REPLY_CHANNEL_KEY: 'mailManagerProReplyChannel',

    /**
     * @return {string} mail | comment
     */
    readReplyChannel: function () {
        try {
            return window.localStorage.getItem(this.REPLY_CHANNEL_KEY) === 'comment' ? 'comment' : 'mail';
        } catch (e) {
            return 'mail';
        }
    },

    /**
     * Switches the composer between a mail and a ticket comment — the comment
     * needs no recipients, subject or draft, only the text
     *
     * @param {jQuery} overlay composer popup wrapper
     * @param {string} channel mail | comment
     */
    setReplyChannel: function (overlay, channel) {
        overlay.find('.its4you-composer').toggleClass('its4you-composer-as-comment', channel === 'comment');
        overlay.find('.its4you-composer-channel .tab-item').each(function () {
            const isActive = jQuery(this).data('channel') === channel;

            jQuery(this).toggleClass('active', isActive).find('.nav-link').toggleClass('active', isActive);
        });
        try {
            window.localStorage.setItem(this.REPLY_CHANNEL_KEY, channel);
        } catch (e) {
            // private window / blocked storage — the choice just is not remembered
        }
    },

    /**
     * Writes the composer text as a comment on the linked ticket
     *
     * @param {jQuery} composer
     * @param {string} body editor HTML — the server turns it into plain text
     */
    sendTicketComment: function (composer, body) {
        let self = this,
            files = this.composerFiles || [],
            threadId = parseInt(composer.data('thread'), 10) || 0,
            params = {
                module: app.getModuleName(),
                action: 'ProSendComment',
                mailbox: this.mailboxOf(threadId),
                thread: threadId,
                is_private: composer.find('.its4you-comment-private').is(':checked') ? '1' : '0',
                body: body
            },
            request = {data: params};

        self.clearComposerError();

        if (!self.htmlToText(body)) {
            self.showComposerError(app.vtranslate('JS_FILL_REQUIRED'), ['.its4you-composer-body']);
            return;
        }
        if (files.length > 1) {
            self.showComposerError(app.vtranslate('JS_COMMENT_ONE_FILE'), ['.its4you-composer-files-row']);
            return;
        }
        if (files.length && files[0].size > this.getUploadLimit(composer)) {
            self.showComposerError(app.vtranslate('JS_ATTACHMENTS_TOO_LARGE'), ['.its4you-composer-files-row']);
            return;
        }
        if (self.composerSending) {
            return;
        }
        if (files.length) {
            let formData = new FormData();
            jQuery.each(params, function (key, value) {
                formData.append(key, value);
            });
            formData.append('attachment', files[0], files[0].name);
            request = {url: 'index.php', type: 'POST', data: formData, processData: false, contentType: false};
        }

        self.composerSending = true;
        app.helper.showProgress();
        app.request.post(request).then(function (err, data) {
            self.composerSending = false;
            app.helper.hideProgress();
            if (err || !data || !data.success) {
                self.showComposerError((data && data.message) || app.vtranslate('JS_SEND_FAILED'));
                return;
            }
            app.helper.showSuccessNotification({message: app.vtranslate('JS_COMMENT_SAVED')});
            self.destroyComposer();
            self.loadConversationById(threadId);
            self.loadThreads(1, false);
        });
    },

    /**
     * Turns the To field into a CRM-record picker (select2 4 on a multiple
     * select — the picked addresses are the option values, getRecipients()
     * joins them comma separated, so the send action reads the field
     * unchanged). A valid address typed in is accepted as well, also without a
     * CRM record; prefilled reply addresses (preselected options) survive
     *
     * @param {jQuery} overlay composer popup wrapper
     */
    initRecipientPicker: function (overlay) {
        const self = this,
            picker = overlay.find('.its4you-recipient-picker'),
            // quotes too — the address also goes into a title attribute, and a
            // sender may legally carry them ("x"@host)
            escape = function (text) {
                return jQuery('<div></div>').text(String(text || '')).html()
                    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            };

        if (!picker.length || !jQuery.fn.select2) {
            return;
        }

        vtUtils.showSelect2ElementView(picker, {
            multiple: true,
            width: '100%',
            minimumInputLength: 2,
            placeholder: picker.data('placeholder'),
            dropdownCssClass: 'its4you-recipient-drop',
            // the composer is a fixed layer above the page — the result list must open inside it
            dropdownParent: overlay,
            // any valid address can be typed in as well (Enter, comma or semicolon)
            tags: true,
            tokenSeparators: [',', ';'],
            createTag: function (params) {
                const term = String(params.term || '').trim();

                if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(term)) {
                    return null;
                }

                return {id: term, text: term, email: term, newTag: true};
            },
            // every text is escaped above — the markup is ours
            escapeMarkup: function (markup) {
                return markup;
            },
            ajax: {
                delay: 250,
                transport: function (params, success, failure) {
                    app.request.post({data: {
                        module: app.getModuleName(),
                        action: 'ProSearchRecipients',
                        search: params.data.term || ''
                    }}).then(function (error, data) {
                        if (error) {
                            failure(error);

                            return;
                        }

                        success(data);
                    });
                },
                // the address is the token id — the option value
                processResults: function (data) {
                    const groups = (data && data.groups) || [];

                    jQuery.each(groups, function (groupIndex, group) {
                        jQuery.each(group.children || [], function (index, item) {
                            item.id = item.email;
                            item.text = item.label || item.email;
                        });
                    });

                    return {results: groups};
                }
            },
            templateResult: function (item) {
                if (item.loading) {
                    return escape(item.text);
                }
                // group headers carry `text` + `children`, not an address. The
                // icon markup comes from the server (Vtiger_Module_Model::getModuleIcon)
                if (item.children) {
                    return '<span class="its4you-recipient-group">'
                        + (item.icon || '') + escape(item.text) + '</span>';
                }

                // opted out of mass mail — still answerable, the user sees the flag
                return escape(item.name || item.email)
                    + '<span class="its4you-recipient-mail">' + escape(item.email) + '</span>'
                    + (item.optout
                        ? '<span class="its4you-recipient-optout">' + escape(app.vtranslate('JS_EMAIL_OPT_OUT')) + '</span>'
                        : '');
            },
            templateSelection: function (item) {
                // prefilled reply addresses have no CRM record — plain envelope.
                // The address is always spelled out: the user must see exactly
                // where the mail goes, not just whose record it is
                const email = item.email || item.id;
                let markup = item.icon || '<i class="fa-regular fa-envelope"></i>';

                markup += item.name
                    ? escape(item.name) + '<span class="its4you-recipient-token-mail">&lt;'
                        + escape(email) + '&gt;</span>'
                    : escape(email);

                return '<span class="its4you-recipient-token" title="' + escape(email) + '">'
                    + markup + '</span>';
            },
            language: {
                noResults: function () {
                    return app.vtranslate('JS_NO_CRM_RECIPIENT');
                },
                inputTooShort: function () {
                    return picker.data('placeholder');
                }
            }
        });
    },

    /**
     * Shows why the mail did not go out in the composer itself, above its buttons — a
     * notification would sit behind the overlay. The fields to fix are marked.
     *
     * @param {string} message plain text
     * @param {string[]} fields selectors of the fields to mark ('' = none)
     */
    showComposerError: function (message, fields) {
        const overlay = jQuery('.its4you-composer-overlay');

        this.clearComposerError();
        jQuery.each(fields || [], function (index, selector) {
            if (selector) {
                overlay.find(selector).closest('.its4you-composer-row, .its4you-composer-body, .its4you-schedule-row')
                    .addClass('its4you-composer-invalid');
                overlay.find(selector).filter('input').addClass('is-invalid');
            }
        });
        overlay.find('.its4you-composer-error').text(message).removeClass('hide');
    },

    /**
     * The error of the last attempt goes away — the next attempt or a change of the user
     */
    clearComposerError: function () {
        const overlay = jQuery('.its4you-composer-overlay');

        overlay.find('.its4you-composer-error').addClass('hide').text('');
        overlay.find('.its4you-composer-invalid').removeClass('its4you-composer-invalid');
        overlay.find('.is-invalid').removeClass('is-invalid');
    },

    /**
     * The picked To addresses, comma separated (the send action parses them)
     *
     * @param {jQuery} composer
     * @return {string}
     */
    getRecipients: function (composer) {
        const value = composer.find('[name="to"]').val();

        return (Array.isArray(value) ? value.join(',') : String(value || '')).trim();
    },

    /**
     * Removes the composer popup and its editor / picker instances
     */
    destroyComposer: function () {
        clearInterval(this.editorWaitTimer);
        jQuery(document).off('keydown.its4youComposer');
        if (window.CKEDITOR && CKEDITOR.instances.its4youComposerBody) {
            CKEDITOR.instances.its4youComposerBody.destroy(true);
        }
        // select2 keeps its dropdown on the body — destroy before removing
        let picker = jQuery('.its4you-composer-overlay .its4you-recipient-picker');
        if (picker.length && picker.hasClass('select2-hidden-accessible')) {
            picker.select2('destroy');
        }
        jQuery('.its4you-composer-overlay').remove();
    },

    /**
     * The user's own text in the editor, with a previous GENERATED draft
     * (marker div) not counting as their text — regenerate keeps working
     *
     * @param {boolean} withoutSignature the prefilled signature is not what
     *                                   the user wrote either (an "empty" check)
     * @return {string} editor HTML without the generated-draft marker
     */
    getOwnComposerText: function (withoutSignature) {
        if (!window.CKEDITOR || !CKEDITOR.instances.its4youComposerBody) {
            return '';
        }
        // a real parse — a regex stops at the first nested </div> of the draft
        let body = this.parseHtml(CKEDITOR.instances.its4youComposerBody.getData());
        jQuery(body).find('.its4you-ai-draft').remove();
        if (withoutSignature) {
            jQuery(body).find('.its4you-signature').remove();
        }

        return body.innerHTML;
    },

    /**
     * Swaps the pictures of editor HTML for text markers ([[IMG1]]…) — the
     * AI gets text only and would lose them (a pasted screenshot is a huge
     * data address besides)
     *
     * @param {string} html
     * @return {{html: string, images: string[]}} the HTML with markers and the pictures' outer HTML
     */
    maskImages: function (html) {
        let body = this.parseHtml(html),
            images = [];

        jQuery(body).find('img').each(function () {
            images.push(this.outerHTML);
            jQuery(this).replaceWith(document.createTextNode(' [[IMG' + images.length + ']] '));
        });

        return {html: body.innerHTML, images: images};
    },

    /**
     * Puts the pictures back where their markers stand; one the AI dropped
     * goes to the end — a picture is never lost
     *
     * @param {string} html the AI result
     * @param {string[]} images outer HTML from maskImages()
     * @return {string}
     */
    unmaskImages: function (html, images) {
        let missing = '';

        jQuery.each(images, function (index, image) {
            let marker = new RegExp('\\[\\[\\s*IMG' + (index + 1) + '\\s*\\]\\]', 'i');

            if (marker.test(html)) {
                html = html.replace(marker, function () {
                    return image;
                });
            } else {
                missing += '<br>' + image;
            }
        });

        return html + missing;
    },

    /**
     * Takes the signature block out of editor HTML
     *
     * @param {string} html
     * @return {{body: string, signature: string}} the text and the signature block (its outer HTML, '' when none)
     */
    splitSignature: function (html) {
        let body = this.parseHtml(html),
            block = jQuery(body).find('.its4you-signature').first(),
            signature = block.length ? block.prop('outerHTML') : '';

        block.remove();

        return {body: body.innerHTML, signature: signature};
    },

    /**
     * Parses HTML into an inert document — nothing runs and nothing loads
     * (a jQuery .html() on a live element would fetch images and fire their
     * onerror handlers of whatever the editor holds)
     *
     * @param {string} html
     * @return {HTMLElement} body of the parsed document
     */
    parseHtml: function (html) {
        return new DOMParser().parseFromString('<body>' + (html || '') + '</body>', 'text/html').body;
    },

    /**
     * @param {string} html
     * @return {string} trimmed visible text of the HTML
     */
    htmlToText: function (html) {
        return String(this.parseHtml(html).textContent || '').trim();
    },

    /**
     * Flips the AI button between "AI Draft" (empty editor → generate) and
     * "Improve my text" (written draft → correct/rewrite it); the "my style"
     * tone is selectable only when there is a style to keep
     */
    updateAiDraftMode: function () {
        let overlay = jQuery('.its4you-composer-overlay'),
            hasText = this.htmlToText(this.getOwnComposerText(true)) !== '',
            tone = overlay.find('.its4you-ai-tone');

        overlay.find('.its4you-ai-draft-label')
            .text(app.vtranslate(hasText ? 'JS_AI_IMPROVE' : 'JS_AI_DRAFT'));
        tone.find('.its4you-tone-mystyle').prop('disabled', !hasText);
        if (!hasText && tone.val() === 'mystyle') {
            tone.val('professional');
        }
    },

    /**
     * AI assist — an empty editor gets a GENERATED draft (marker div,
     * regenerate replaces it), a written draft gets IMPROVED in place with
     * the original kept for the undo link. Never sent automatically
     *
     * @param {jQuery} button the AI button inside the composer
     */
    generateAiDraft: function (button) {
        let self = this,
            composer = button.closest('.its4you-composer'),
            overlay = jQuery('.its4you-composer-overlay'),
            threadId = composer.data('thread');

        if (!window.CKEDITOR || !CKEDITOR.instances.its4youComposerBody) {
            return;
        }
        // the signature is neither improved nor counted as written text —
        // it is set aside and put back under the result
        // Pictures (a pasted screenshot) cannot go through the AI — it works
        // with text. Each one is swapped for a marker the model is told to
        // keep, and put back into the result
        let editor = CKEDITOR.instances.its4youComposerBody,
            parts = this.splitSignature(editor.getData()),
            masked = this.maskImages(parts.body),
            improve = this.htmlToText(this.getOwnComposerText(true)) !== '';

        button.prop('disabled', true).find('.fa-wand-magic-sparkles').addClass('fa-spin');
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProAiDraft',
            mailbox: self.mailboxOf(threadId),
            thread: threadId,
            tone: composer.find('.its4you-ai-tone').val(),
            language: composer.find('.its4you-ai-lang').val(),
            // the WHOLE editor content — an edited generated draft is the
            // user's text too once they typed into it
            current_html: improve ? masked.html : ''
        }}).then(function (err, data) {
            button.prop('disabled', false).find('.fa-wand-magic-sparkles').removeClass('fa-spin');
            overlay.find('.its4you-ai-generating').remove();
            if (err || !data || !data.success) {
                self.showComposerError((data && data.message) || app.vtranslate('JS_AI_FAILED'));
                return;
            }
            // "Auto" names the conversation's language once it is known
            if (data.language) {
                let autoOption = composer.find('.its4you-ai-lang option[value="auto"]');
                autoOption.text(autoOption.data('label') + ' – ' + data.language);
            }
            self.showDraftSources(composer, data.sources || []);
            self.showDraftWarnings(composer, data.unverified || [], !!data.suspicious, data.backup || '');
            if (data.improved) {
                // replace the text, keep the original for one-click undo
                self.aiDraftOriginal = editor.getData();
                editor.setData(self.unmaskImages(data.draft_html, masked.images) + parts.signature);
                overlay.find('.its4you-ai-undo').removeClass('hide');
            } else {
                // drop a previous AI draft so regenerate does not stack them
                // — what the user typed while it was generating stays under
                // it; with nothing typed only one of the empty lines left for
                // writing remains between the draft and the signature
                // (a picture alone is the user's content too)
                let own = self.getOwnComposerText(true),
                    typed = self.htmlToText(own) !== '' || jQuery(self.parseHtml(own)).find('img').length > 0;
                editor.setData('<div class="its4you-ai-draft">' + data.draft_html + '</div>'
                    + (typed
                        ? self.getOwnComposerText()
                        : (parts.signature ? '<br>' + parts.signature : '')));
            }
        });
    },

    /**
     * Names the knowledge the AI draft answered from — the user sees what
     * it relied on (his own notes marked) and can check it
     *
     * @param {jQuery} composer
     * @param {object[]} sources [{label, personal}]
     */
    showDraftSources: function (composer, sources) {
        let line = composer.find('.its4you-ai-sources').empty();

        if (!sources.length) {
            line.addClass('hide');
            return;
        }
        line.append(jQuery('<i class="fa-solid fa-book"></i>'))
            .append(document.createTextNode(' ' + app.vtranslate('JS_KNOWLEDGE_SOURCES') + ': '));
        jQuery.each(sources, function (index, source) {
            if (index) {
                line.append(document.createTextNode(', '));
            }
            line.append(jQuery('<span class="its4you-ai-source"></span>').text(source.label
                + (source.personal ? ' (' + app.vtranslate('JS_KNOWLEDGE_PERSONAL') + ')' : '')));
        });
        line.removeClass('hide');
    },

    /**
     * The checks of an AI draft (I16): the facts it states that are nowhere in the
     * conversation, the records or the knowledge (highlighted in the text too), and a
     * conversation whose mail tried to instruct the AI
     *
     * @param {jQuery} composer
     * @param {string[]} unverified
     * @param {boolean} suspicious
     * @param {string} backup the backup provider that wrote it (I17), '' = the main AI
     */
    showDraftWarnings: function (composer, unverified, suspicious, backup) {
        const line = composer.find('.its4you-ai-check').empty();

        if (backup) {
            line.append(jQuery('<div class="its4you-ai-backup-badge"></div>')
                .append(jQuery('<i class="fa-solid fa-life-ring me-1"></i>'))
                .append(document.createTextNode(app.vtranslate('JS_AI_MADE_BY_BACKUP').replace('%s', backup))));
        }

        if (suspicious) {
            line.append(jQuery('<div class="its4you-ai-check-suspicious"></div>')
                .append(jQuery('<i class="fa-solid fa-shield-halved me-1"></i>'))
                .append(document.createTextNode(app.vtranslate('JS_AI_SUSPICIOUS_DRAFT'))));
        }

        if (unverified.length) {
            line.append(jQuery('<div class="its4you-ai-check-unverified"></div>')
                .append(jQuery('<i class="fa-solid fa-triangle-exclamation me-1"></i>'))
                .append(document.createTextNode(app.vtranslate('JS_AI_UNVERIFIED') + ': ' + unverified.join(', '))));
        }

        line.toggleClass('hide', !suspicious && !unverified.length && !backup);
    },

    /**
     * Total size of the files a single mail may carry — the server's
     * $upload_maxsize, handed over by the composer template
     *
     * @param {jQuery} composer
     * @return {number} bytes
     */
    getUploadLimit: function (composer) {
        return parseInt(composer.data('upload-max'), 10) || 3145728;
    },

    /**
     * Renders the picked files as removable chips
     *
     * @param {jQuery} overlay composer popup wrapper
     */
    renderComposerFiles: function (overlay) {
        let list = overlay.find('.its4you-attach-list').empty(),
            limit = this.getUploadLimit(overlay.find('.its4you-composer')),
            total = 0;

        jQuery.each(this.composerFiles || [], function (index, file) {
            total += file.size;
            jQuery('<span class="its4you-attach-chip"></span>')
                .append(jQuery('<span></span>').text(file.name))
                .append(jQuery('<span class="its4you-attach-size"></span>')
                    .text(' (' + Math.max(Math.round(file.size / 1024), 1) + ' kB)'))
                .append(jQuery('<a href="javascript:void(0);" class="its4you-attach-remove">&times;</a>')
                    .attr('data-index', index))
                .appendTo(list);
        });
        list.toggleClass('its4you-attach-over', total > limit);
    },

    /**
     * CRM record ids behind the picked recipients — the sent mail's
     * ITS4YouEmails record is related to one of them
     *
     * @param {jQuery} composer
     * @return {string} comma separated crmids
     */
    getRecipientRecordIds: function (composer) {
        let picker = composer.find('.its4you-recipient-picker'),
            ids = [];

        if (picker.length && picker.hasClass('select2-hidden-accessible')) {
            jQuery.each(picker.select2('data') || [], function (index, item) {
                if (item && item.crmid) {
                    ids.push(item.crmid);
                }
            });
        }

        return ids.join(',');
    },

    /**
     * Collects the composer and sends the mail
     */
    /**
     * Fills an EMAIL Maker template into the composer: its text goes above the
     * signature (a reply keeps the quoted original below the editor), its subject
     * replaces the subject of a new mail only, its Documents join the attachments
     *
     * @param {jQuery} overlay
     * @param {string} templateId
     */
    applyEmailTemplate: function (overlay, templateId) {
        const self = this,
            composer = overlay.find('.its4you-composer'),
            editor = window.CKEDITOR && CKEDITOR.instances.its4youComposerBody;

        app.helper.showProgress();
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProFillTemplate',
            mailbox: this.mailboxOf(composer.data('thread')),
            thread: composer.data('thread'),
            template: templateId,
            to: this.getRecipients(composer)
        }}).then(function (err, data) {
            app.helper.hideProgress();

            if (err || !data || !data.success) {
                self.showComposerError((data && data.message) || app.vtranslate('JS_SAVE_FAILED'));

                return;
            }

            if (editor) {
                const parts = self.splitSignature(editor.getData()),
                    written = self.htmlToText(parts.body) !== '' ? parts.body : '';

                editor.setData(data.body + written + (parts.signature ? '<br>' + parts.signature : ''));
            }

            if (composer.data('mode') === 'new' && data.subject) {
                composer.find('input[name="subject"]').val(data.subject);
            }

            const list = overlay.find('.its4you-template-documents');

            jQuery.each(data.documents || [], function (index, documentInfo) {
                if (list.find('.its4you-template-document[value="' + documentInfo.id + '"]').length) {
                    return;
                }

                list.append(jQuery('<span class="its4you-composer-attachment"></span>')
                    .append(jQuery('<input type="hidden" class="its4you-template-document">').val(documentInfo.id))
                    .append('<i class="fa-solid fa-paperclip"></i>&nbsp;')
                    .append(document.createTextNode(documentInfo.name))
                    .append('&nbsp;<a href="javascript:void(0);" class="its4you-template-document-remove"><i class="fa-solid fa-times"></i></a>'));
            });
            list.toggleClass('hide', !list.find('.its4you-template-document').length);
        });
    },

    /**
     * "Undo" after Send: the composer hides, a card counts down and only then the
     * mail goes out; Undo brings the composer back as it was. Leaving the page sends
     * at once (fetch keepalive — the plain post has no files, mail with files is
     * never delayed)
     *
     * @param {number} seconds
     * @param {object} params the ProSendMail post
     * @param {function} post sends it the normal way, returns a promise of "sent"
     */
    delayComposerSend: function (seconds, params, post) {
        const self = this,
            overlay = jQuery('.its4you-composer-overlay'),
            card = jQuery('<div class="its4you-undo-item its4you-send-pending"></div>'),
            text = jQuery('<div class="its4you-undo-text"></div>'),
            finish = function () {
                clearTimeout(pending.timer);
                clearInterval(pending.ticker);
                window.removeEventListener('pagehide', pending.onLeave);
                card.remove();
                self.delayedSend = null;
            },
            pending = {
                left: seconds,
                go: function () {
                    finish();

                    return post();
                },
                onLeave: function () {
                    const body = new URLSearchParams(params);

                    if (typeof csrfMagicName !== 'undefined') {
                        body.append(csrfMagicName, csrfMagicToken);
                    }

                    finish();
                    fetch('index.php', {method: 'POST', body: body, keepalive: true, credentials: 'same-origin'});
                }
            },
            label = function () {
                text.text(app.vtranslate('JS_SENDING_IN').replace('%s', pending.left));
            };

        overlay.addClass('hide');
        label();
        card.append(text).append(jQuery('<a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm its4you-undo-link"></a>')
            .text(app.vtranslate('JS_UNDO'))
            .on('click', function () {
                finish();
                overlay.removeClass('hide');
            }));
        this.getUndoBar().append(card);
        pending.ticker = setInterval(function () {
            pending.left = Math.max(pending.left - 1, 0);
            label();
        }, 1000);
        pending.timer = setTimeout(pending.go, seconds * 1000);
        window.addEventListener('pagehide', pending.onLeave);
        this.delayedSend = pending;
    },

    /**
     * @param {boolean} markDone "Send and mark done"
     * @param {string} scheduledAt "Send later" — wall-clock time in the user's CRM time zone
     */
    sendComposer: function (markDone, scheduledAt) {
        let self = this,
            // the popup overlay is mounted on document.body
            composer = jQuery('.its4you-composer-overlay .its4you-composer'),
            body = (window.CKEDITOR && CKEDITOR.instances.its4youComposerBody)
                ? CKEDITOR.instances.its4youComposerBody.getData()
                : composer.find('#its4youComposerBody').val(),
            forwardAttachments = [];
        if (composer.hasClass('its4you-composer-as-comment')) {
            // a ticket comment carries no mail signature
            this.sendTicketComment(composer, this.splitSignature(body).body);
            return;
        }
        composer.find('.its4you-fwd-attachment:checked').each(function () {
            forwardAttachments.push(jQuery(this).val());
        });
        let params = {
                module: app.getModuleName(),
                action: 'ProSendMail',
                mailbox: this.mailboxOf(composer.data('thread')),
                compose_mode: composer.data('mode'),
                thread: composer.data('thread'),
                reply_message: composer.data('reply-message'),
                quote_message: composer.data('quote-message'),
                draft_message: composer.data('draft-message'),
                to: this.getRecipients(composer),
                to_records: this.getRecipientRecordIds(composer),
                cc: composer.find('input[name="cc"]').val().trim(),
                bcc: composer.find('input[name="bcc"]').val().trim(),
                subject: composer.find('input[name="subject"]').val().trim(),
                forward_attachments: forwardAttachments.join(','),
                document_attachments: composer.find('.its4you-template-document').map(function () {
                    return jQuery(this).val();
                }).get().join(','),
                mark_done: markDone ? '1' : '0',
                scheduled_at: scheduledAt || '',
                body: body
            };

        self.clearComposerError();

        if (!params.to || !params.subject || !self.htmlToText(body)) {
            self.showComposerError(app.vtranslate('JS_FILL_REQUIRED'), [
                params.to ? '' : '[name="to"]',
                params.subject ? '' : '[name="subject"]',
                self.htmlToText(body) ? '' : '.its4you-composer-body'
            ]);
            return;
        }
        // Enter / Space on the focused Send button while the first request runs
        if (self.composerSending) {
            return;
        }

        // Attachments travel as multipart — the plain params post cannot
        // carry files (core comment-save uses the same FormData shape)
        let files = this.composerFiles || [],
            request = {data: params},
            totalSize = 0;

        jQuery.each(files, function (index, file) {
            totalSize += file.size;
        });
        if (totalSize > this.getUploadLimit(composer)) {
            self.showComposerError(app.vtranslate('JS_ATTACHMENTS_TOO_LARGE'), ['.its4you-composer-files-row']);
            return;
        }
        if (files.length) {
            let formData = new FormData();
            jQuery.each(params, function (key, value) {
                formData.append(key, value);
            });
            jQuery.each(files, function (index, file) {
                formData.append('attachments[]', file, file.name);
            });
            request = {url: 'index.php', type: 'POST', data: formData, processData: false, contentType: false};
        }

        const threadId = parseInt(composer.data('thread'), 10) || 0,
            // the composer of THIS mail — a later one is never touched
            ownOverlay = composer.closest('.its4you-composer-overlay'),
            // a mail leaving with the page goes by fetch keepalive (64 KB at most) —
            // files and big bodies (pasted images) are never delayed
            delay = scheduledAt || files.length || new URLSearchParams(params).toString().length > 60000
                ? 0
                : parseInt(composer.data('send-delay'), 10) || 0,
            post = function () {
                const sent = jQuery.Deferred();

                self.composerSending = true;
                app.helper.showProgress();
                app.request.post(request).then(function (err, data) {
                    self.composerSending = false;
                    app.helper.hideProgress();
                    if (err || !data || !data.success) {
                        // a delayed mail that failed — its composer comes back
                        ownOverlay.removeClass('hide');
                        self.showComposerError((data && data.message) || app.vtranslate('JS_SEND_FAILED'));
                        sent.resolve(false);
                        return;
                    }
                    if (data.scheduled) {
                        app.helper.showSuccessNotification({message: app.vtranslate('JS_MAIL_SCHEDULED')});
                        self.updateScheduledCount();
                    } else {
                        app.helper.showSuccessNotification({
                            message: data.filed_on_records
                                ? app.vtranslate('JS_MAIL_SENT_AND_FILED')
                                : app.vtranslate('JS_MAIL_SENT')
                        });
                    }
                    self.destroyComposer();
                    if (threadId) {
                        self.loadConversationById(threadId);
                    }
                    self.loadThreads(1, false);
                    sent.resolve(true);
                });

                return sent.promise();
            };

        const dispatch = function () {
            if (delay) {
                self.delayComposerSend(delay, params, post);

                return;
            }

            post();
        };

        // the AI check of the reply (AI settings) — warnings only, it never stops the mail
        if (String(composer.data('draft-check')) === '1') {
            this.checkComposerDraft(composer, params, files.length > 0 || !!params.forward_attachments || !!params.document_attachments, dispatch);

            return;
        }

        dispatch();
    },

    /**
     * Asks the fast model about the reply; problems are shown with "Send anyway",
     * no answer (an error, a slow provider) sends the mail as if nothing was checked
     *
     * @param {jQuery} composer
     * @param {object} params the ProSendMail post
     * @param {boolean} hasAttachments
     * @param {function} send
     */
    checkComposerDraft: function (composer, params, hasAttachments, send) {
        const self = this;

        if (self.composerSending) {
            return;
        }

        self.composerSending = true;
        app.helper.showProgress(app.vtranslate('JS_DRAFT_CHECKING'));
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProCheckDraft',
            mailbox: params.mailbox,
            thread: params.thread,
            to: params.to,
            subject: params.subject,
            body: self.splitSignature(params.body).body,
            has_attachments: hasAttachments ? '1' : '0'
        }}).then(function (err, data) {
            const issues = (!err && data && data.issues) || [];

            self.composerSending = false;
            app.helper.hideProgress();

            if (!issues.length) {
                send();

                return;
            }

            const list = jQuery('<ul class="mb-0"></ul>');

            jQuery.each(issues, function (index, issue) {
                list.append(jQuery('<li></li>').text(issue.message));
            });
            app.helper.showConfirmationBox({
                message: jQuery('<div></div>')
                    .append(jQuery('<div class="mb-2"></div>').text(app.vtranslate('JS_DRAFT_CHECK_FOUND')))
                    .append(list)
                    .append(jQuery('<div class="mt-2"></div>').text(app.vtranslate('JS_DRAFT_SEND_ANYWAY')))
                    .html()
            }).then(send);
        });
    },

    /**
     * The sidebar "Scheduled" entry follows the user's waiting mail
     *
     * @param {number} [count] known count, omitted = ask the server
     */
    updateScheduledCount: function (count) {
        const item = this.getContainer().find('.its4you-scheduled-open'),
            show = function (value) {
                item.toggleClass('hide', !value).find('.its4you-scheduled-count').text(value);
            };

        if (typeof count !== 'undefined') {
            show(count);

            return;
        }

        app.request.post({data: {module: app.getModuleName(), action: 'ProScheduledMail', op: 'count'}}).then(function (err, data) {
            if (!err && data) {
                show(parseInt(data.count, 10) || 0);
            }
        });
    },

    /**
     * The list of the user's scheduled mail: another time, send now, cancel
     */
    openScheduledList: function () {
        const self = this;

        app.helper.showProgress();
        app.request.post({data: {module: app.getModuleName(), view: 'ProIndexAjax', mode: 'scheduled'}}).then(function (err, html) {
            app.helper.hideProgress();

            if (err) {
                return;
            }

            app.helper.showModal(html, {cb: function (modal) {
                modal.on('click', '.its4you-scheduled-op', function () {
                    const button = jQuery(this),
                        row = button.closest('.its4you-scheduled-row'),
                        op = button.data('op'),
                        run = function () {
                            app.helper.showProgress();
                            app.request.post({data: {
                                module: app.getModuleName(),
                                action: 'ProScheduledMail',
                                op: op,
                                scheduled: row.data('scheduled'),
                                scheduled_at: row.find('.its4you-scheduled-time').val()
                            }}).then(function (error, data) {
                                app.helper.hideProgress();

                                if (error || !data || !data.success) {
                                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_FAILED')});

                                    return;
                                }

                                self.updateScheduledCount(parseInt(data.count, 10) || 0);

                                if (op === 'reschedule') {
                                    app.helper.showSuccessNotification({message: app.vtranslate('JS_MAIL_SCHEDULED')});

                                    return;
                                }

                                row.remove();
                                app.helper.showSuccessNotification({
                                    message: app.vtranslate(op === 'send' ? 'JS_MAIL_SENT' : 'JS_SCHEDULE_CANCELLED')
                                });

                                if (op === 'send') {
                                    self.loadThreads(1, false);
                                }
                            });
                        };

                    if (op === 'cancel') {
                        app.helper.showConfirmationBox({message: app.vtranslate('JS_SCHEDULE_CANCEL_CONFIRM')}).then(run);

                        return;
                    }

                    run();
                });
            }});
        });
    },

    /**
     * Saves the composer content as a draft (server Drafts folder + cache);
     * a repeated save replaces the previous version
     */
    saveDraftComposer: function () {
        let self = this,
            composer = jQuery('.its4you-composer-overlay .its4you-composer'),
            body = (window.CKEDITOR && CKEDITOR.instances.its4youComposerBody)
                ? CKEDITOR.instances.its4youComposerBody.getData()
                : composer.find('#its4youComposerBody').val(),
            params = {
                module: app.getModuleName(),
                action: 'ProSaveDraft',
                mailbox: this.mailboxOf(composer.data('thread')),
                thread: composer.data('thread'),
                reply_message: composer.data('reply-message'),
                draft_message: composer.data('draft-message'),
                // the context a reopened draft restores (forward: which mail, which attachments)
                compose_mode: composer.data('mode'),
                quote_message: composer.data('quote-message'),
                forward_attachments: composer.find('.its4you-fwd-attachment:checked').map(function () {
                    return jQuery(this).val();
                }).get().join(','),
                to: this.getRecipients(composer),
                cc: composer.find('input[name="cc"]').val().trim(),
                bcc: composer.find('input[name="bcc"]').val().trim(),
                subject: composer.find('input[name="subject"]').val().trim(),
                body: body
            };

        self.clearComposerError();

        if (!params.subject && !self.htmlToText(body)) {
            self.showComposerError(app.vtranslate('JS_FILL_REQUIRED'), ['[name="subject"]', '.its4you-composer-body']);
            return;
        }

        let save = function () {
            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();
                if (err || !data || !data.success) {
                    self.showComposerError((data && data.message) || app.vtranslate('JS_SAVE_FAILED'));
                    return;
                }
                app.helper.showSuccessNotification({message: app.vtranslate('JS_DRAFT_SAVED')});
                let threadId = parseInt(composer.data('thread'), 10) || 0;
                self.destroyComposer();
                if (threadId && self.currentThreadId === threadId) {
                    self.loadConversationById(threadId);
                }
                self.loadThreads(1, false);
            });
        };

        // Drafts keep the forwarded attachments (by reference) but not the
        // picked files — those would be lost without a word, so the user decides first
        if ((this.composerFiles || []).length) {
            app.helper.showConfirmationBox({message: app.vtranslate('JS_DRAFT_WITHOUT_ATTACHMENTS')}).then(save);
            return;
        }
        save();
    },

    /**
     * Entry points of the composer — reply, compose, continue a draft
     *
     * @param {jQuery} container
     */
    registerComposerEvents: function (container) {
        let self = this;

        // the user's "Send later" mail
        container.on('click', '.its4you-scheduled-open', function () {
            self.openScheduledList();
        });

        // Continue writing a saved draft — the head click would toggle the
        // collapse, so the button stops the propagation
        container.on('click', '.its4you-edit-draft', function (event) {
            event.stopPropagation();
            let threadId = jQuery(this).closest('.its4you-conversation-content')
                .find('.its4you-conversation-head').data('threadid');
            self.openComposer('draft', threadId, jQuery(this).data('message'));
        });

        // Composer — reply bar, sidebar compose, send and cancel
        container.on('click', '.its4you-reply-btn', function () {
            let threadId = jQuery(this).closest('.its4you-conversation-content')
                .find('.its4you-conversation-head').data('threadid');
            self.openComposer(jQuery(this).data('composemode'), threadId);
        });
        container.on('click', '.its4you-compose', function () {
            self.openComposer('new', 0);
        });
    }
};
