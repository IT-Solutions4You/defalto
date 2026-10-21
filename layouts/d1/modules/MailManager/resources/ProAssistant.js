/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The AI assistant of the open conversation — a tab of the right column next
 * to the CRM panel. Questions and answers per conversation, proposed actions
 * the user carries out with a click (nothing happens on its own).
 * Mixin of MailManager_ProIndex_Js — loaded before ProIndex.js
 */
/** @var MailManager_ProAssistant_Mixin */
window.MailManager_ProAssistant_Mixin = {

    // conversation whose chat the tab shows
    assistantThreadId: 0,

    // the tab is open — a newly opened conversation loads its chat at once
    assistantOpen: false,

    /**
     * @return {jQuery}
     */
    getAssistant: function () {
        return this.getContainer().find('.its4you-assistant');
    },

    /**
     * Switches the right column between the CRM panel and the assistant
     *
     * @param {string} tab crm | assistant
     */
    showRailTab: function (tab) {
        const container = this.getContainer(),
            assistant = this.getAssistant();

        if (!assistant.length) {
            return;
        }
        this.assistantOpen = tab === 'assistant';
        container.find('.its4you-rail-tab').removeClass('active')
            .filter('[data-tab="' + tab + '"]').addClass('active');
        container.find('.its4you-crm-rail-body').toggleClass('d-none', this.assistantOpen);
        assistant.toggleClass('d-none', !this.assistantOpen);
        if (this.assistantOpen) {
            this.loadAssistant(this.currentThreadId);
            assistant.find('.its4you-assistant-question').focus();
        }
    },

    /**
     * Shows the chat of a conversation (the user's own, kept on the server)
     *
     * @param {number} threadId
     * @param {boolean} force reload even when it is shown already
     */
    loadAssistant: function (threadId, force) {
        const self = this,
            assistant = this.getAssistant(),
            messages = assistant.find('.its4you-assistant-messages');

        threadId = parseInt(threadId, 10) || 0;
        if (!threadId || (threadId === this.assistantThreadId && !force)) {
            return;
        }
        this.assistantThreadId = threadId;
        messages.empty();
        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProAssistant',
            mode: 'history',
            mailbox: self.mailboxOf(threadId),
            thread: threadId
        }}).then(function (err, data) {
            if (err || !data || !data.success || threadId !== self.assistantThreadId) {
                return;
            }
            jQuery.each(data.history || [], function (index, message) {
                self.renderAssistantMessage(message.role, message.text, message.actions || []);
            });
            self.scrollAssistant();
        });
    },

    /**
     * @param {string} role user | assistant
     * @param {string} text
     * @param {object[]} actions proposed actions of an answer
     * @return {jQuery} the message
     */
    renderAssistantMessage: function (role, text, actions) {
        const self = this,
            assistant = this.getAssistant(),
            message = jQuery('<div class="its4you-assistant-message"></div>')
                .addClass(role === 'user' ? 'its4you-assistant-user' : 'its4you-assistant-answer');

        if (text) {
            message.append(jQuery('<div class="its4you-assistant-text"></div>')
                .html(app.htmlEncode(text).replace(/\n/g, '<br>')));
        }
        jQuery.each(actions || [], function (index, action) {
            const label = assistant.data('action' + action.type.charAt(0).toUpperCase() + action.type.slice(1));
            jQuery('<button type="button" class="btn btn-outline-secondary btn-sm its4you-assistant-action"></button>')
                .append(jQuery('<i></i>').addClass(self.ASSISTANT_ACTION_ICONS[action.type] || 'fa-solid fa-check'))
                .append(document.createTextNode(' ' + (label || action.type)))
                .data('action', action)
                .appendTo(message);
        });
        assistant.find('.its4you-assistant-messages').append(message);

        return message;
    },

    ASSISTANT_ACTION_ICONS: {
        draft: 'fa-solid fa-reply', task: 'fa-solid fa-list-check', ticket: 'fa-solid fa-life-ring', done: 'fa-solid fa-check'
    },

    scrollAssistant: function () {
        const messages = this.getAssistant().find('.its4you-assistant-messages');
        messages.scrollTop(messages.prop('scrollHeight'));
    },

    /**
     * @param {string} question
     * @param {string} mode ask | summary
     */
    askAssistant: function (question, mode) {
        const self = this,
            threadId = this.assistantThreadId,
            assistant = this.getAssistant();

        question = jQuery.trim(question || '');
        if (!question || !threadId || assistant.data('busy')) {
            return;
        }
        assistant.data('busy', true);
        this.renderAssistantMessage('user', question, []);
        const thinking = this.renderAssistantMessage('assistant', '', [])
            .addClass('its4you-assistant-thinking')
            .append('<i class="fa-solid fa-circle-notch fa-spin"></i>');
        this.scrollAssistant();

        app.request.post({data: {
            module: app.getModuleName(),
            action: 'ProAssistant',
            mode: mode || 'ask',
            mailbox: self.mailboxOf(threadId),
            thread: threadId,
            question: question
        }}).then(function (err, data) {
            assistant.data('busy', false);
            thinking.remove();
            if (threadId !== self.assistantThreadId) {
                return;
            }
            if (err || !data || !data.success) {
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_AI_FAILED')});
                return;
            }
            const answer = self.renderAssistantMessage('assistant', data.answer, data.actions || []);

            // made by the backup provider while the main AI is down (I17)
            if (data.backup) {
                answer.append(jQuery('<div class="its4you-ai-backup-badge"></div>')
                    .append(jQuery('<i class="fa-solid fa-life-ring me-1"></i>'))
                    .append(document.createTextNode(app.vtranslate('JS_AI_MADE_BY_BACKUP').replace('%s', data.backup))));
            }
            self.scrollAssistant();
        });
    },

    /**
     * A proposed action — the user's own click carries it out the usual way
     *
     * @param {object} action
     */
    runAssistantAction: function (action) {
        const threadId = this.assistantThreadId;

        if (action.type === 'draft') {
            // the composer puts the text in once its editor is ready (Composer.js)
            this.pendingComposerDraft = action.html;
            this.openComposer('reply', threadId);
        } else if (action.type === 'task' || action.type === 'ticket') {
            this.openQuickCreate(action.module, action.params || {}, action.labels || {}, threadId);
        } else if (action.type === 'done') {
            this.updateThreadState([threadId], {state: 'done'});
        }
    },

    /**
     * @param {jQuery} container
     */
    registerAssistantEvents: function (container) {
        const self = this;

        container.on('click', '.its4you-rail-tab', function () {
            self.showRailTab(jQuery(this).data('tab'));
        });
        container.on('click', '.its4you-ai-assistant-btn', function () {
            self.showRailTab('assistant');
        });
        container.on('click', '.its4you-assistant-chip', function () {
            const chip = jQuery(this);
            self.askAssistant(chip.text(), chip.data('mode'));
        });
        container.on('click', '.its4you-assistant-send', function () {
            const input = self.getAssistant().find('.its4you-assistant-question');
            self.askAssistant(input.val(), 'ask');
            input.val('');
        });
        // Enter sends, Shift+Enter is a new line
        container.on('keydown', '.its4you-assistant-question', function (event) {
            if (event.keyCode === 13 && !event.shiftKey) {
                event.preventDefault();
                self.askAssistant(jQuery(this).val(), 'ask');
                jQuery(this).val('');
            }
        });
        container.on('click', '.its4you-assistant-action', function () {
            self.runAssistantAction(jQuery(this).data('action'));
        });
        container.on('click', '.its4you-assistant-clear', function () {
            const threadId = self.assistantThreadId;
            app.request.post({data: {
                module: app.getModuleName(),
                action: 'ProAssistant',
                mode: 'clear',
                mailbox: self.mailboxOf(threadId),
                thread: threadId
            }}).then(function () {
                self.loadAssistant(threadId, true);
            });
        });
    }
};
