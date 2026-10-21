/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * CRM panel beside the conversation — record links, create-from-mail through
 * the core quick-create, comments and activities, the AI suggestion card
 *
 * Mixin of MailManager_ProIndex_Js — the methods run on the controller
 * instance (shared state, cross-calls through this/self); loaded before ProIndex.js
 */
/** @var MailManager_ProCrmPanel_Mixin */
window.MailManager_ProCrmPanel_Mixin = {

    // Modules where the mail's attachments belong to the created record
    ATTACHMENT_CREATE_MODULES: ['HelpDesk', 'Potentials', 'Project', 'ProjectTask'],

    // The Defalto calendar module (replaces vtiger Calendar / Events, N5)
    APPOINTMENT_MODULE: 'Appointments',

    // Ids of the rich-text editors loaded into the open quick-create
    quickCreateEditors: [],

    // Selection event of the core record popup opened by "Link Existing"
    PICK_EVENT: 'MailManager.ProRecordPicker.Selected',

    /**
     * Loads the CRM side panel (linked records + suggestions) for a thread
     *
     * @param {number} threadId
     */
    loadCrmPanel: function (threadId) {
        const self = this,
            container = this.getContainer(),
            rail = container.find('.its4you-crm-rail'),
            params = {
                module: app.getModuleName(),
                view: 'ProIndexAjax',
                mode: 'crmPanel',
                mailbox: this.mailboxOf(threadId),
                thread: threadId
            };

        app.request.post({data: params}).then(function (err, html) {
            // the panel of a thread the user already left must not replace
            // the panel of the open one
            if (err || parseInt(threadId, 10) !== parseInt(self.currentThreadId, 10)) {
                return;
            }
            rail.removeClass('hide');
            rail.find('.its4you-crm-rail-body').html(html);
            self.placeAiBanner();
        });
    },

    /**
     * Mirrors the AI suggestion card from the CRM rail (where it stays
     * hidden as the source of truth) into the full-width slot under the AI
     * summary. Cloning keeps the sync idempotent — the rail render and the
     * conversation render arrive in any order and the later call re-syncs
     */
    placeAiBanner: function () {
        const container = this.getContainer(),
            slot = container.find('.its4you-ai-suggestion-slot'),
            railInner = container.find('.its4you-crm-rail-body .its4you-crm-rail-inner');

        if (!slot.length || !railInner.length
            || parseInt(railInner.data('threadid'), 10) !== parseInt(this.currentThreadId, 10)) {
            return;
        }
        slot.empty();
        const card = railInner.find('.its4you-ai-suggestion-section');
        if (card.length) {
            slot.append(card.clone());
        }
    },

    /**
     * Relates a just quick-created Appointment to the driving record + the
     * linked contact — server-side field update (account_id/parent_id/
     * contact_id), because the quick-create layout may carry none of them
     *
     * @param {object} data quick-create SaveAjax result
     */
    relateCreatedActivity: function (data) {
        const self = this,
            target = this.pendingActivityTarget,
            recordId = data && (data._recordId || data.record || data.id);

        this.pendingActivityTarget = null;
        if (!target || !recordId) {
            return;
        }
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProRelateActivity',
            mailbox: this.mailboxOf(target.threadId),
            thread: target.threadId,
            // 0 = contact-only conversation, the server relates the contact
            crmid: target.crmid || 0,
            event: recordId
        }}).then(function (err, result) {
            if (err || !result || !result.success) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                return;
            }
            // the appointment now shows among the conversation's records
            self.loadCrmPanel(target.threadId);
        });
    },

    /**
     * Restores the "Create New" label on the dropdown button after the
     * project-picker sub-step closes (picked item was shown while open)
     *
     * @param {jQuery} box the project picker box
     */
    resetCreateNewButton: function (box) {
        const toggle = box.closest('.its4you-crm-section').find('.its4you-crm-create-group .dropdown-toggle');
        if (toggle.data('defaultHtml') !== undefined) {
            toggle.html(toggle.data('defaultHtml'));
        }
    },

    /**
     * Opens the CORE quick-create for a module with the mail prefill (form
     * load, validation, AJAX submit stay native); the save callback links
     * the new record to the conversation
     *
     * @param {string} createModule
     * @param {Object} prefill field => value
     * @param {Object} labels field => translated label (extra editable rows)
     * @param {number} threadId
     */
    openQuickCreate: function (createModule, prefill, labels, threadId) {
        const self = this,
            data = jQuery.extend({}, prefill);

        // a rich-text field holds HTML — the mail's plain text goes in as such
        jQuery.each(labels || {}, function (key, meta) {
            if (meta && meta.richtext && data[key]) {
                data[key] = self.textToHtml(data[key]);
            }
        });

        self.pendingCreateThread = threadId;
        self.pendingCreateModule = createModule;
        self.pendingCreatePrefill = data;
        self.pendingCreateLabels = labels;
        self.openCoreQuickCreate(createModule, data, function (result) {
            self.linkCreatedRecord(threadId, result);
        });
    },

    /**
     * Opens the Defalto quick-create of a module through the Vtiger_Index_Js
     * API (getQuickCreateForm + quickCreateSave) — the same flow as the header
     * quick-create menu, without depending on the module being in that menu
     * (N13). Prefill goes through params.data (merged into the form request).
     * When the quick-create form cannot be loaded, the full edit view opens,
     * POSTed so the mail text stays out of the url and the access logs.
     *
     * @param {string} moduleName
     * @param {Object} data field => value prefill
     * @param {Function} callback called with the SaveAjax result
     */
    openCoreQuickCreate: function (moduleName, data, callback) {
        const self = this,
            indexInstance = Vtiger_Index_Js.getInstance(),
            params = {data: data || {}, callbackFunction: callback},
            url = 'index.php?module=' + encodeURIComponent(moduleName) + '&view=QuickCreateAjax';

        app.helper.showProgress();
        indexInstance.getQuickCreateForm(url, moduleName, params).then(function (html) {
            app.helper.hideProgress();

            if (!html || String(html).indexOf('name="QuickCreate"') === -1) {
                self.openFullCreate(moduleName, params.data);

                return;
            }

            app.helper.showModal(html, {
                cb: function (container) {
                    const modalForm = container.find('form[name="QuickCreate"]');

                    indexInstance.registerPostReferenceEvent(container);
                    app.event.trigger('post.QuickCreateForm.show', modalForm);
                    app.helper.registerLeavePageWithoutSubmit(modalForm);
                    app.helper.registerModalDismissWithoutSubmit(modalForm);
                },
                backdrop: 'static',
                keyboard: false
            });

            const form = jQuery('form[name="QuickCreate"]'),
                formModule = form.find('[name="module"]').val() || moduleName,
                moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(formModule);
            let saveInstance = indexInstance;

            app.helper.showVerticalScroll(form.find('.modal-body'), {
                scrollInertia: 200,
                autoHideScrollbar: true,
                setHeight: '80vh'
            });

            if (moduleInstance && typeof moduleInstance.quickCreateSave === 'function') {
                saveInstance = moduleInstance;
                saveInstance.registerBasicEvents(form);
            }

            vtUtils.applyFieldElementsView(form);
            saveInstance.quickCreateSave(form, params);
        });
    },

    /**
     * Full create view fallback — POSTed like the core does (Reports print):
     * the mail text would overflow the url and land in the server access logs
     *
     * @param {string} moduleName
     * @param {Object} data field => value prefill
     */
    openFullCreate: function (moduleName, data) {
        const form = jQuery('<form method="POST" action="index.php" class="hide"></form>')
            .append(jQuery('<input type="hidden">').attr('name', csrfMagicName).val(csrfMagicToken))
            .append(jQuery('<input type="hidden" name="module">').val(moduleName))
            .append(jQuery('<input type="hidden" name="view" value="Edit">'));

        jQuery.each(data || {}, function (fieldName, value) {
            form.append(jQuery('<input type="hidden">').attr('name', fieldName).val(value));
        });
        form.appendTo(document.body).trigger('submit');
    },

    /**
     * @param {string} text plain text
     * @returns {string} the same as HTML — escaped, line breaks kept
     */
    textToHtml: function (text) {
        return jQuery('<div></div>').text(String(text)).html().replace(/\r?\n/g, '<br>');
    },

    /**
     * Turns the quick-create text areas of rich-text fields into the editor
     * (fields the installation edits as HTML, see CreatePrefill::isRichTextField)
     *
     * @param {jQuery} form the quick-create form
     * @param {Object} labels field => meta
     */
    loadQuickCreateEditors: function (form, labels) {
        const self = this;

        self.destroyQuickCreateEditors();
        if (!window.CKEDITOR || typeof Vtiger_CkEditor_Js === 'undefined') {
            return;
        }
        jQuery.each(labels, function (key, meta) {
            const textarea = form.find('textarea[name="' + key + '"]').first();
            if (!meta || !meta.richtext || !textarea.length) {
                return;
            }
            // the editor is addressed by the element id — one of our own, the
            // page behind the modal may hold a field of the same name
            const id = 'mmProQuickCreate_' + key;
            textarea.attr('id', id);
            new Vtiger_CkEditor_Js().loadCkEditor(textarea, {height: '260px'});

            const editor = CKEDITOR.instances[id];
            if (editor) {
                // the core serializes the form itself — the text area must
                // hold the editor's content at any moment, not on submit only
                editor.on('change', function () {
                    editor.updateElement();
                });
                self.quickCreateEditors.push(id);
            }
        });
    },

    /**
     * Stretches the quick-create of a record made from a mail to the height
     * of the window — the height the core gives it leaves a few lines of the
     * mail's text visible
     *
     * @param {jQuery} form the quick-create form
     */
    fitQuickCreateHeight: function (form) {
        const body = form.find('.modal-body').first(),
            dialog = form.closest('.modal-dialog'),
            // what the window keeps besides the body: header, footer, margins
            chrome = (form.find('.modal-header').outerHeight() || 0)
                + (form.find('.modal-footer').outerHeight() || 0)
                + 2 * (parseFloat(dialog.css('margin-top')) || 30),
            height = Math.max(320, jQuery(window).height() - chrome - 20);

        body.css({height: height + 'px', 'max-height': 'none'});
        // the scroll plugin copied the body's former max-height onto its
        // inner box when it started — that copy would keep clipping the form
        body.children('.mCustomScrollBox').css('max-height', 'none');
        if (jQuery.fn.mCustomScrollbar && body.hasClass('mCustomScrollbar')) {
            body.mCustomScrollbar('update');
        }
    },

    /**
     * Removes the editors of a closed quick-create — a left-over instance
     * would block the editor of the next one (same element id)
     */
    destroyQuickCreateEditors: function () {
        jQuery.each(this.quickCreateEditors, function (index, id) {
            const editor = window.CKEDITOR && CKEDITOR.instances[id];
            if (editor) {
                try {
                    editor.destroy(true);
                } catch (e) {
                    CKEDITOR.remove(editor);
                }
            }
        });
        this.quickCreateEditors = [];
    },

    /**
     * Forgets the pending mail -> quick-create context — after a save, and
     * when the modal closes without one (a cancelled create must not leak its
     * mail prefill and thread link into the next, unrelated quick-create)
     */
    clearPendingCreate: function () {
        this.pendingCreateThread = 0;
        this.pendingCreateModule = '';
        this.pendingCreatePrefill = null;
        this.pendingCreateLabels = null;
        this.pendingActivityTarget = null;
        this.pendingCreateShown = false;
    },

    /**
     * Links a just quick-created record to the conversation. Core fires the
     * save event AND the callback for one save — only the first call acts
     *
     * @param {number} threadId
     * @param {object} data quick-create SaveAjax result
     */
    linkCreatedRecord: function (threadId, data) {
        const self = this,
            recordId = data && (data._recordId || data.record || data.id),
            createModule = this.pendingCreateModule,
            createPrefill = this.pendingCreatePrefill || {};

        if (!this.pendingCreateThread) {
            return;
        }
        this.clearPendingCreate();
        if (!recordId) {
            this.loadCrmPanel(threadId);
            return;
        }
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProRelateThread',
            op: 'link',
            mailbox: this.mailboxOf(threadId),
            thread: threadId,
            crmid: recordId,
            // a project task is a by-product like an event — it shows in the
            // rail but must not label the thread or mute the AI chain
            curates: createModule === 'ProjectTask' ? '0' : '1',
            // relation fields the QuickCreate layout may have dropped
            enrich: JSON.stringify(createPrefill)
        }}).then(function () {
            self.loadCrmPanel(threadId);
            // a created case record drives the head (status chip, owner)
            // and the list row — refresh both right away
            self.refreshConversationHead(threadId);
            // a linked case record may label the conversation
            self.refreshSidebarCounts();
            self.loadThreads(1, false);
            // Attach the mail's files to the new Ticket / Opportunity as Documents
            if (self.ATTACHMENT_CREATE_MODULES.indexOf(createModule) !== -1) {
                app.request.post({data: {
                    module: app.getModuleName(),
                    action: 'ProSaveAttachments',
                    mailbox: self.mailboxOf(threadId),
                    thread: threadId,
                    crmid: recordId,
                    // a task takes the files of the mail it was made from only
                    latest: createModule === 'ProjectTask' ? '1' : '0'
                }}).then(function (err, res) {
                    if (res && res.success && res.saved > 0) {
                        app.helper.showSuccessNotification({
                            message: app.vtranslate('JS_ATTACHMENTS_SAVED').replace('%s', res.saved)
                        });
                    }
                });
            }
        });
    },

    /**
     * Links / unlinks a record or dismisses a suggestion, then refreshes what
     * the record may drive (the rail, the conversation head, the list row)
     *
     * @param {number} threadId
     * @param {number} crmid
     * @param {string} op link|unlink|dismiss
     */
    relateRecord: function (threadId, crmid, op) {
        const self = this,
            params = {
                module: app.getModuleName(),
                action: 'ProRelateThread',
                op: op,
                mailbox: self.mailboxOf(threadId),
                thread: threadId,
                crmid: crmid
            };

        app.request.post({data: params}).then(function (err, data) {
            if (err || !data || !data.success) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                return;
            }
            self.loadCrmPanel(threadId);
            // the linked/unlinked record may drive the head and the row
            self.refreshConversationHead(threadId);
            self.refreshSidebarCounts();
            self.loadThreads(1, false);
        });
    },

    /**
     * Opens the CORE record popup of a module (search, paging and the list
     * stay native); the picked record gets linked to the conversation
     *
     * @param {string} pickModule
     * @param {number} parentId record whose related records are listed, 0 = all
     * @param {number} threadId
     * @param {string} parentModule module of that record (default: the organization)
     */
    openRecordPicker: function (pickModule, parentId, threadId, parentModule) {
        const self = this,
            params = {module: pickModule, view: 'Popup'};

        if (parentId) {
            params.related_parent_module = parentModule || 'Accounts';
            params.related_parent_id = parentId;
        }
        app.event.off(self.PICK_EVENT);
        app.event.one(self.PICK_EVENT, function (e, data) {
            const picked = JSON.parse(data),
                crmid = parseInt(Object.keys(picked)[0], 10);

            if (crmid) {
                self.relateRecord(threadId, crmid, 'link');
            }
        });
        Vtiger_Popup_Js.getInstance().showPopup(params, self.PICK_EVENT);
    },

    /**
     * CRM panel — create from mail (core quick-create), comments, activities,
     * AI suggestion card, link / unlink
     *
     * @param {jQuery} container
     */
    registerCrmPanelEvents: function (container) {
        const self = this;

        // The Create / Link menus open over the page like the record actions of Defalto —
        // the panel scrolls and would cut a positioned menu off, a fixed one is not clipped.
        // The instance is made before the Bootstrap data API (document) handles the click
        container.on('click', '.its4you-crm-menu > .dropdown-toggle', function () {
            if (window.bootstrap) {
                bootstrap.Dropdown.getOrCreateInstance(this, {
                    popperConfig: function (config) {
                        return jQuery.extend({}, config, {strategy: 'fixed'});
                    }
                });
            }
        });

        // CRM panel — create a new record from the mail. Reuses the CORE header
        // quick-create flow (form load, validation, AJAX submit) by triggering
        // its menu item with the prefill appended to the url; the save callback
        // auto-links the new record to the conversation
        container.on('click', '.its4you-crm-create', function () {
            const button = jQuery(this),
                // The triage banner button carries its own thread id; the CRM
                // panel items resolve it from the rail wrapper
                threadId = button.data('thread') || button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId;

            self.openQuickCreate(button.data('module'), button.data('prefill') || {},
                button.data('prefillLabels') || {}, threadId);
        });

        // Project task — the item opens the project picker first; the create
        // button then runs the standard quick-create with projectid prefilled.
        // While the picker is open the dropdown button shows the CHOSEN item
        // instead of "Create New"
        container.on('click', '.its4you-crm-create-task', function () {
            const button = jQuery(this),
                section = button.closest('.its4you-crm-section'),
                toggle = section.find('.its4you-crm-create-group .dropdown-toggle'),
                box = section.find('.its4you-crm-project-box'),
                select = box.find('.its4you-crm-project-select');

            if (toggle.data('defaultHtml') === undefined) {
                toggle.data('defaultHtml', toggle.html());
            }
            toggle.html(button.find('.its4you-crm-create-icon').prop('outerHTML')
                + jQuery('<span></span>').text(button.text().trim()).html());

            select.empty();
            jQuery.each(button.data('projects') || [], function (index, project) {
                select.append(jQuery('<option></option>').attr('value', project.crmid).text(project.label));
            });
            box.data('module', button.data('module'));
            box.data('prefill', button.data('prefill') || {});
            box.data('prefillLabels', button.data('prefillLabels') || {});
            box.removeClass('hide');
        });

        container.on('click', '.its4you-crm-project-cancel', function () {
            const box = jQuery(this).closest('.its4you-crm-project-box');
            self.resetCreateNewButton(box);
            box.addClass('hide');
        });

        container.on('click', '.its4you-crm-project-create', function () {
            const box = jQuery(this).closest('.its4you-crm-project-box'),
                projectId = parseInt(box.find('.its4you-crm-project-select').val(), 10),
                prefill = jQuery.extend({}, box.data('prefill')),
                threadId = box.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId;

            if (!projectId) {
                return;
            }
            prefill.projectid = projectId;
            self.resetCreateNewButton(box);
            box.addClass('hide');
            self.openQuickCreate(box.data('module'), prefill, box.data('prefillLabels') || {}, threadId);
        });

        // Safety net — some quick-create code paths skip the callback; the
        // core event fires on every successful quick-create save. The saved
        // form's module must match what the mail started: a quick-create of
        // anything else never gets linked to the thread
        app.event.on('post.QuickCreateForm.save', function (event, data, formData) {
            const savedModule = formData && formData.module;
            if (self.pendingCreateThread
                && (!savedModule || savedModule === self.pendingCreateModule)) {
                self.linkCreatedRecord(self.pendingCreateThread, data);
            }
            if (self.pendingActivityTarget
                && (!savedModule || savedModule === self.APPOINTMENT_MODULE)) {
                self.relateCreatedActivity(data);
            }
        });

        // Core hides the modal right after the save event, so any pending
        // context still set when the quick-create closes belongs to a
        // cancelled create. Only a SHOWN quick-create counts — showModal()
        // first hides whatever modal was open, that must not clear anything
        app.event.on('post.QuickCreateForm.show', function () {
            self.pendingCreateShown = true;
            // "Go to full form" leaves the page: the record is then saved by
            // the core edit view and never gets linked to the conversation
            // (nor related, for an event) — not offered for a record made
            // from a mail
            if (self.pendingCreateThread || self.pendingActivityTarget) {
                jQuery('form[name="QuickCreate"]').find('#goToFullForm').addClass('hide');
            }
        });
        jQuery(document).on('hidden.bs.modal', '.myModal', function () {
            self.destroyQuickCreateEditors();
            if (self.pendingCreateShown) {
                self.clearPendingCreate();
            }
        });

        // Prefill fields the QC layout does not render (address, phone… from
        // the AI signature extraction) appear as EDITABLE rows — the user
        // reviews/corrects them BEFORE the save; the core Save action
        // persists every posted module field, layout or not
        app.event.on('post.QuickCreateForm.show', function () {
            if (!self.pendingCreatePrefill) {
                return;
            }
            // the event's form argument may be undefined (core defines it
            // after showModal on transition-less modals) — resolve directly
            const form = jQuery('form[name="QuickCreate"]'),
                labels = self.pendingCreateLabels || {},
                extra = jQuery('<div class="its4you-qc-extra"></div>');

            if (!form.length) {
                return;
            }
            // a record made from a mail carries the mail's text — the core
            // window is too narrow to read and edit it
            form.closest('.modal-dialog').addClass('its4you-qc-wide');
            // (the style sheet hides "Go to full form" in this window too)
            form.find('#goToFullForm').addClass('hide');
            jQuery.each(self.pendingCreatePrefill, function (key, value) {
                const meta = labels[key];
                if (meta === undefined || form.find('[name="' + key + '"]').length) {
                    return;
                }
                const row = jQuery('<div class="its4you-qc-extra-row"></div>');
                jQuery('<label></label>').text(meta.label || meta).appendTo(row);
                // a relation — the record's NAME is shown, its id travels in a
                // hidden field (the save sets the relation the layout lacks)
                if (meta.reference) {
                    if (!meta.display) {
                        return;
                    }
                    jQuery('<div class="its4you-qc-extra-static"></div>').text(meta.display).appendTo(row);
                    jQuery('<input type="hidden">').attr('name', key).val(value).appendTo(row);
                    extra.append(row);
                    return;
                }
                // text-area fields get a real textarea — a single-line input
                // silently drops the mail's line breaks
                (meta.textarea
                    ? jQuery('<textarea class="inputElement" rows="6"></textarea>')
                    : jQuery('<input type="text" class="inputElement">')
                ).attr('name', key).val(value).appendTo(row);
                extra.append(row);
            });
            if (extra.children().length) {
                extra.prepend(jQuery('<div class="its4you-qc-extra-title"></div>')
                    .text(app.vtranslate('JS_FROM_SIGNATURE')));
                // the core wraps .modal-body into a custom scrollbar — the
                // section must land INSIDE the scroll container, appending
                // to .modal-body directly would stick out of the modal
                const body = form.find('.modal-body'),
                    target = body.find('.mCSB_container').first();
                (target.length ? target : body).append(extra);
                if (jQuery.fn.mCustomScrollbar && target.length) {
                    body.mCustomScrollbar('update');
                }
            }
            // after the extra rows — a rich-text field may be one of them
            self.loadQuickCreateEditors(form, labels);
            self.fitQuickCreateHeight(form);
        });

        // Driving-record quick action — logs the mail content on the record as
        // a comment through the core ModComments action (same contract as the
        // detail-view widget); the box opens prefilled with the newest mail
        // the comment opens in a window of its own — the panel keeps the
        // template (prefilled with the mail), a cancelled one starts over
        container.on('click', '.its4you-crm-comment-toggle', function () {
            const template = jQuery(this).closest('.its4you-crm-section').find('.its4you-crm-comment-modal'),
                crmid = template.data('crmid');

            app.helper.showModal(template.html(), {cb: function (modal) {
                const textarea = modal.find('.its4you-crm-comment-text'),
                    saveButton = modal.find('[name="saveButton"]');

                textarea.focus();
                saveButton.on('click', function () {
                    const text = textarea.val().trim();

                    if (!text) {
                        return;
                    }
                    saveButton.prop('disabled', true);
                    app.request.post({data: {
                        module: 'ModComments',
                        action: 'SaveAjax',
                        commentcontent: text,
                        related_to: crmid
                    }}).then(function (err, data) {
                        if (err || !data) {
                            saveButton.prop('disabled', false);
                            app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                            return;
                        }
                        app.helper.hideModal();
                        app.helper.showSuccessNotification({message: app.vtranslate('JS_COMMENT_POSTED_SUCCESSFULLY')});
                    });
                });
            }});
        });

        // Driving-record quick action — an appointment on the record through
        // the CORE quick-create; the relation is set AFTER the save
        // (appointments hold relations in account_id/parent_id/contact_id
        // fields, the quick-create layout may lack them)
        container.on('click', '.its4you-crm-activity', function () {
            const button = jQuery(this),
                threadId = button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId,
                subject = container.find('.its4you-conversation-subject').text().trim();

            self.pendingActivityTarget = {
                threadId: threadId,
                crmid: button.data('crmid')
            };
            self.openCoreQuickCreate(self.APPOINTMENT_MODULE, subject ? {subject: subject} : {}, function (data) {
                self.relateCreatedActivity(data);
            });
        });

        // AI suggestion card — cleanup suggestions reuse the toolbar flows
        container.on('click', '.its4you-triage-action', function () {
            const button = jQuery(this),
                threadId = button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId;

            self.undoableThreadAction(threadId, button.data('action') === 'delete' ? 'delete' : 'spam');
        });

        // AI suggestion card — the customer confirmed the fix: one click
        // closes the linked ticket, the AI writes the Solution summary
        container.on('click', '.its4you-triage-close', function () {
            const button = jQuery(this),
                threadId = button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId;

            button.prop('disabled', true).find('.fa-check').removeClass('fa-check').addClass('fa-spinner fa-spin');
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProCloseTicket',
                mailbox: self.mailboxOf(threadId),
                thread: threadId
            }}).then(function (err, data) {
                if (err || !data || !data.success) {
                    button.prop('disabled', false).find('.fa-spinner').removeClass('fa-spinner fa-spin').addClass('fa-check');
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_AI_FAILED')});
                    return;
                }
                app.helper.showSuccessNotification({
                    message: app.vtranslate('JS_TICKET_CLOSED').replace('%s', app.htmlEncode(data.record_label))
                });
                self.refreshConversationHead(threadId);
                self.loadCrmPanel(threadId);
                self.loadThreads(1, false);
            });
        });

        // AI suggestion card — dismiss hides the suggestion permanently
        container.on('click', '.its4you-triage-dismiss', function () {
            const link = jQuery(this),
                step = link.data('step') || '',
                threadId = link.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId,
                params = {
                    module: app.getModuleName(),
                    action: 'ProUpdateThread',
                    mailbox: self.mailboxOf(threadId),
                    thread: threadId,
                    triage_dismiss: '1',
                    skip_step: step
                };
            app.request.post({data: params}).then(function (err, data) {
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                    return;
                }
                if (step) {
                    // a skipped creation step — the panel re-renders the card
                    // with the next step of the chain (or none)
                    self.loadCrmPanel(threadId);
                    return;
                }
                // both copies: the visible banner clone AND the hidden rail
                // original (a later re-sync would resurrect the banner)
                container.find('.its4you-ai-suggestion-section').remove();
            });
        });

        // CRM panel — link / unlink a record to the open conversation
        container.on('click', '.its4you-crm-link, .its4you-crm-unlink, .its4you-crm-dismiss', function () {
            const button = jQuery(this),
                threadId = button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId,
                op = button.hasClass('its4you-crm-unlink')
                    ? 'unlink'
                    : (button.hasClass('its4you-crm-dismiss') ? 'dismiss' : 'link');

            self.relateRecord(threadId, button.data('crmid'), op);
        });

        // CRM panel — link an EXISTING record picked in the core record popup
        // (the rail menu and the suggestion card share the organization filter)
        container.on('click', '.its4you-crm-pick', function () {
            const button = jQuery(this),
                threadId = button.closest('.its4you-crm-rail-inner').data('threadid') || self.currentThreadId,
                filter = container.find('.its4you-crm-rail .its4you-crm-pick-filter').first(),
                parentId = String(button.data('filtered')) === '1' && filter.is(':checked') ? filter.data('parent') : 0;

            // an item with a parent of its own (tasks of the linked project)
            // lists that parent's records whatever the organization filter says
            if (button.data('parentId')) {
                self.openRecordPicker(button.data('module'), button.data('parentId'), threadId,
                    button.data('parentModule'));
                return;
            }
            self.openRecordPicker(button.data('module'), parentId, threadId);
        });

        // ticking the filter must not close the menu
        container.on('click', '.its4you-crm-pick-scope', function (e) {
            e.stopPropagation();
        });
    }
};
