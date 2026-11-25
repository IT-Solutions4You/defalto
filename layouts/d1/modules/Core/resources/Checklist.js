/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
jQuery.Class('Vtiger_Checklist_Js', {
    instance: null,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new Vtiger_Checklist_Js();
        }

        return this.instance;
    }
}, {
    initEdit: function () {
        this.registerEvents();
    },

    registerEvents: function () {
        const self = this,
            documentRoot = jQuery(document);

        if (documentRoot.data('checklistEvents')) {
            return;
        }

        documentRoot.data('checklistEvents', true);
        documentRoot.on('mouseenter.checklist focusin.checklist', '.js-checklist-item', function () {
            jQuery(this).addClass('bg-body-tertiary').find('.js-checklist-item-delete').removeClass('invisible');
        });
        documentRoot.on('mouseleave.checklist focusout.checklist', '.js-checklist-item', function (event) {
            if (!event.relatedTarget || !jQuery.contains(this, event.relatedTarget)) {
                jQuery(this).removeClass('bg-body-tertiary').find('.js-checklist-item-delete').addClass('invisible');
            }
        });
        documentRoot.on('pointerdown.checklist mousedown.checklist', '.js-checklist-item-delete', function (event) {
            // Deletion cancels the same row's unfinished text edit before blur.
            const editor = jQuery(this).closest('.js-checklist-item').find('.js-checklist-editor');

            if (editor.length) {
                event.preventDefault();
                self.clearEditorTimer(editor);
            }
        });
        documentRoot.on('click.checklist', '.js-checklists button', function (event) {
            event.preventDefault();
            event.stopPropagation();
            self.handleButton(jQuery(this));
        });
        documentRoot.on('click.checklist', '.js-checklist-item', function (event) {
            if (jQuery(event.target).closest('input, button, label, .js-checklist-editor').length) {
                return;
            }

            const row = jQuery(this),
                editor = row.find('.js-checklist-editor input'),
                button = row.find('.js-checklist-edit');

            if (editor.length) {
                editor.trigger('focus');
            } else if (button.length) {
                event.stopPropagation();
                self.handleButton(button);
            }
        });
        documentRoot.on('input.checklist', '.js-checklist-editor input', function () {
            const editor = jQuery(this).closest('.js-checklist-editor');
            self.clearEditorTimer(editor);
            editor.data('saveTimer', setTimeout(function () {
                self.confirmEditor(editor, true);
            }, 2000));
        });
        documentRoot.on('focusout.checklist', '.js-checklist-editor', function (event) {
            if (event.relatedTarget && jQuery.contains(this, event.relatedTarget)) {
                return;
            }

            self.confirmEditor(jQuery(this), false);
        });
        documentRoot.on('input.checklist', '.js-checklists .js-checklist-item-text', function () {
            jQuery(this).removeData('pastedText');
        });
        documentRoot.on('paste.checklist', '.js-checklists .js-checklist-item-text', function (event) {
            const clipboard = event.originalEvent && event.originalEvent.clipboardData,
                pastedText = clipboard ? clipboard.getData('text/plain') : '';

            if (!/[\r\n]/.test(pastedText)) {
                return;
            }

            event.preventDefault();
            const input = jQuery(this),
                text = input.val().slice(0, this.selectionStart) + pastedText + input.val().slice(this.selectionEnd);
            // Text inputs strip line breaks; retain the original batch for save/retry.
            input.val(text.replace(/\r\n|\r|\n/g, ' ')).data('pastedText', text);
            self.handleButton(input.parent().find('.js-checklist-item-create'));
        });
        documentRoot.on('keydown.checklist', '.js-checklists input[type="text"]', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                self.clearEditor(jQuery(this).closest('.js-checklist-editor').parent());
            }

            if (event.key !== 'Enter' || event.isComposing || event.originalEvent && event.originalEvent.isComposing) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (event.repeat) {
                return;
            }

            const input = jQuery(this);

            if (input.hasClass('js-checklist-item-text')) {
                self.handleButton(input.parent().find('.js-checklist-item-create'));
            } else {
                self.confirmEditor(input.closest('.js-checklist-editor'), true);
            }
        });
        documentRoot.on('change.checklist', '.js-checklist-check', function () {
            const checkbox = jQuery(this),
                root = checkbox.closest('.js-checklists');

            if (root.data('saving')) {
                checkbox.prop('checked', !checkbox.prop('checked'));
                return;
            }

            self.send(root, 'checkItem', checkbox.closest('.js-checklist-card'), checkbox.closest('.js-checklist-item'), null, checkbox.prop('checked') ? 1 : 0);
        });
    },

    handleButton: function (button) {
        const root = button.closest('.js-checklists'),
            card = button.closest('.js-checklist-card'),
            item = button.closest('.js-checklist-item');

        if (root.data('saving')) {
            if (root.data('savingOperation') === 'renameItem') {
                root.data('pendingButton', button);
            }

            return;
        }

        if (button.hasClass('js-checklist-hide')) {
            const hidden = !card.data('hideCompleted');
            card.data('hideCompleted', hidden);
            button.text(button.attr(hidden ? 'data-show' : 'data-hide'));
            this.updateCompletedVisibility(card);
        } else if (button.hasClass('js-checklist-edit')) {
            const activeEditor = root.find('.js-checklist-editor');

            if (activeEditor.length) {
                this.confirmEditor(activeEditor, false);

                if (root.data('saving')) {
                    root.data('pendingButton', button);
                    return;
                }
            }

            this.registerFormSaveGuard(root);
            const wrapper = button.parent(),
                editor = jQuery(card.find('.js-checklist-editor-template').html());
            editor.find('input').attr('maxlength', 1000).val(wrapper.children('.js-checklist-text').text());
            wrapper.children('.js-checklist-text, .js-checklist-edit').addClass('d-none');
            wrapper.append(editor);
            editor.find('input').trigger('focus');
        } else if (button.hasClass('js-checklist-item-create')) {
            const input = card.children('.js-checklist-add').find('.js-checklist-item-text');

            if (this.getInputText(input).trim()) {
                this.send(root, 'createItem', card, item, input);
            }
        } else if (button.hasClass('js-checklist-item-delete')) {
            this.clearEditor(item.find('.js-checklist-item-editor'));
            this.send(root, 'deleteItem', card, item);
        }
    },

    clearEditorTimer: function (editor) {
        clearTimeout(editor.data('saveTimer'));
        editor.removeData('saveTimer');
    },

    confirmEditor: function (editor, focusAddItem) {
        const root = editor.closest('.js-checklists'),
            input = editor.find('input');

        if (!editor[0].isConnected || root.data('closingEditor') || root.data('saving')) {
            return;
        }

        this.clearEditorTimer(editor);

        if (!input.val().trim() || input.val().trim() === editor.parent().children('.js-checklist-text').text()) {
            this.clearEditor(editor.parent());
            return;
        }

        this.send(root, 'renameItem', editor.closest('.js-checklist-card'), editor.closest('.js-checklist-item'), input, undefined, focusAddItem);
    },

    clearEditor: function (wrapper) {
        const root = wrapper.closest('.js-checklists'),
            editor = wrapper.children('.js-checklist-editor');
        // Removing a focused node can blur after jQuery has cleared its data.
        root.data('closingEditor', true);
        this.clearEditorTimer(editor);
        editor.remove();
        root.removeData('closingEditor');
        wrapper.children('.js-checklist-text, .js-checklist-edit').removeClass('d-none');
    },

    send: function (root, operation, card, item, input, checked, focusAddItem = true) {
        if (!Number(root.attr('data-record'))) {
            this.updateDraft(root, operation, card, item, input, focusAddItem);
            return;
        }

        this.registerFormSaveGuard(root);
        const self = this,
            data = {
                module: 'Core', action: 'Checklist', operation: operation,
                record: root.attr('data-record'), field: root.attr('data-field'),
                revision: root.attr('data-revision'), item: item.attr('data-id') || 0,
                text: input ? this.getInputText(input) : '', checked: checked
            },
            controls = input ? input.filter(':enabled') : item.find('.js-checklist-check:enabled');
        root.data('saving', true).data('savingOperation', operation).attr('aria-busy', 'true');
        controls.prop('disabled', true);
        app.request.post({data: data}).then(function (error, result) {
            root.data('saving', false).removeData('savingOperation').removeAttr('aria-busy');
            controls.prop('disabled', false);

            if (error) {
                root.removeData('pendingButton');
                self.showError(error, operation, item, input);
                return;
            }

            self.updateCard(root, card, result);
            const pendingButton = root.data('pendingButton');
            root.removeData('pendingButton');

            if (input) {
                if (operation === 'renameItem') {
                    self.clearEditor(input.closest('.js-checklist-editor').parent());
                }

                if (focusAddItem && !pendingButton) {
                    self.setAddItemFocus(card, operation === 'createItem');
                }
            }

            if (pendingButton && pendingButton[0].isConnected) {
                self.handleButton(pendingButton);
            }

            app.helper.showSuccessNotification({message: root.attr('data-saved')});
        }, function (error) {
            root.data('saving', false).removeData('savingOperation').removeAttr('aria-busy');
            controls.prop('disabled', false);
            root.removeData('pendingButton');
            self.showError(error, operation, item, input);
        });
    },

    updateDraft: function (root, operation, card, item, input, focusAddItem = true) {
        const items = card.find('.js-checklist-items > .js-checklist-item').map(function () {
                return {text: jQuery(this).find('.js-checklist-text').text(), checked: false};
            }).get(),
            index = Number(item.attr('data-id')) - 1;

        if (operation === 'createItem') {
            const lines = this.getItemTexts(this.getInputText(input));

            if (lines.some(function (text) { return Array.from(text).length > 1000; })) {
                this.showError({message: root.attr('data-invalid-text')}, operation, item, input);
                return;
            }

            lines.forEach(function (text) { items.push({text: text, checked: false}); });
        } else if (operation === 'renameItem' && items[index]) {
            items[index].text = input.val().trim();
        } else if (operation === 'deleteItem' && items[index]) {
            items.splice(index, 1);
        } else {
            return;
        }

        this.updateCard(root, card, {
            id: 0, revision: '',
            items: items.map(function (entry, position) { entry.id = position + 1; return entry; })
        });

        if (operation === 'renameItem') {
            this.clearEditor(input.closest('.js-checklist-editor').parent());
        }

        if (input && focusAddItem) {
            this.setAddItemFocus(card, operation === 'createItem');
        }
    },

    getInputText: function (input) {
        return input.data('pastedText') === undefined ? input.val() : input.data('pastedText');
    },

    getItemTexts: function (text) {
        return text.split(/\r\n|\r|\n/).map(function (line) { return line.trim(); }).filter(function (line) { return line.length > 0; });
    },

    setAddItemFocus: function (card, clearValue) {
        const input = card.children('.js-checklist-add').find('.js-checklist-item-text');

        if (!input.length || !input[0].isConnected || input.prop('disabled')) {
            return;
        }

        if (clearValue) {
            input.val('').removeData('pastedText');
        }

        input[0].focus();
        input[0].setSelectionRange(input.val().length, input.val().length);
    },

    showError: function (error, operation, item, input) {
        if (operation === 'checkItem') {
            const checkbox = item.find('.js-checklist-check');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }

        if (input) {
            input.trigger('focus');
        }

        app.helper.showErrorNotification({message: error.message || error.statusText || app.vtranslate('JS_ERROR')});
    },

    updateCard: function (root, card, data) {
        root.attr('data-revision', data.revision);
        root.find('.js-checklist-field-value').val(data.items.map(function (entry) { return entry.text; }).join('\n'));
        card.attr('data-id', data.id);
        const itemContainer = card.children('.js-checklist-items'),
            ids = data.items.map(function (entry) { return String(entry.id); });

        itemContainer.children('.js-checklist-item').each(function () {
            if (ids.indexOf(String(jQuery(this).attr('data-id'))) === -1) {
                jQuery(this).remove();
            }
        });

        data.items.forEach(function (entry) {
            let row = itemContainer.children('.js-checklist-item').filter(function () {
                return String(jQuery(this).attr('data-id')) === String(entry.id);
            });

            if (!row.length) {
                row = jQuery(root.find('.js-checklist-item-template').html()).attr('data-id', entry.id);
                itemContainer.append(row);
            }

            row.find('.js-checklist-text').text(entry.text).toggleClass('text-decoration-line-through text-secondary', entry.checked);
            row.find('.js-checklist-edit').toggleClass('text-decoration-none text-body', !entry.checked);
            row.find('.js-checklist-check').prop('checked', entry.checked).attr('aria-label', entry.text);
        });

        this.updateCompletedVisibility(card);
    },

    registerFormSaveGuard: function (root) {
        const form = root.closest('form');

        if (!form.length || form.data('checklistSaveGuard')) {
            return;
        }

        form.data('checklistSaveGuard', true);
        form[0].addEventListener('submit', function (event) {
            const pending = form.find('.js-checklists').filter(function () {
                return !!jQuery(this).data('saving') || !!jQuery(this).find('.js-checklist-editor').length;
            });

            if (pending.length) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    },

    updateCompletedVisibility: function (card) {
        const rows = card.find('.js-checklist-items > .js-checklist-item'),
            completed = rows.filter(function () { return jQuery(this).find('.js-checklist-check').prop('checked'); }).length;
        card.find('.js-checklist-hide').toggleClass('d-none', !completed);
        rows.each(function () {
            jQuery(this).toggleClass('d-none', !!card.data('hideCompleted') && jQuery(this).find('.js-checklist-check').prop('checked'));
        });
    }
});

vtUtils.registerFieldWidget(Vtiger_Checklist_Js);
