/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
Vtiger.Class('Vtiger_AssigneeSelect_Js', {
    getInitials(label) {
        const words = String(label || '').trim().split(/\s+/);

        return ((words[0]?.[0] || '?') + (words.length > 1 ? words[words.length - 1][0] : words[0]?.[1] || '')).toUpperCase();
    },

    register(container) {
        jQuery(container).find('.core-assignee-select').addBack('.core-assignee-select').each(function () {
            if (!this.assigneeController) {
                this.assigneeController = new Vtiger_AssigneeSelect_Js();
                this.assigneeController.registerEvents(this);
            }
        });
    }
}, {
    registerEvents(root) {
        this.root = root;
        this.select = root.querySelector('.core-assignee-value');
        this.rows = root.querySelector('.core-assignee-options');
        this.button = root.querySelector('.core-assignee-toggle');
        this.showOptions();
        this.showSummary();
        jQuery(root).on('show.bs.dropdown.coreAssignee', () => {
            this.loadDraft();
            root.querySelector('.core-assignee-search').value = '';
            this.filterOptions('');
        }).on('shown.bs.dropdown.coreAssignee', () => root.querySelector('.core-assignee-search').focus())
            .on('hidden.bs.dropdown.coreAssignee', () => this.loadDraft())
            .on('change.coreAssignee', '.core-assignee-value', () => this.showSummary())
            .on('change.coreAssignee', '.core-assignee-option', event => {
                if (!this.select.multiple && event.target.checked) {
                    for (const input of this.rows.querySelectorAll('input')) {
                        input.checked = input === event.target;
                    }
                }
            }).on('input.coreAssignee', '.core-assignee-search', event => this.filterOptions(event.target.value))
            .on('click.coreAssignee', '[data-assignee-action]', event => {
                event.preventDefault();
                event.stopPropagation();
                this.handleAction(event.currentTarget.dataset.assigneeAction);
            });
        this.loadDraft();
    },

    getValues() {
        return Array.from(this.select.selectedOptions, option => option.value);
    },

    setValues(values) {
        const selected = new Set(values.map(String));

        for (const option of this.select.options) {
            option.selected = selected.has(option.value);
        }

        this.loadDraft();
        this.showSummary();
    },

    showOptions() {
        this.rows.replaceChildren();

        for (const group of this.select.querySelectorAll('optgroup')) {
            if (!group.children.length) {
                continue;
            }

            const heading = document.createElement('div');
            heading.className = 'small text-secondary text-uppercase px-2 pt-2';
            heading.textContent = group.label;
            this.rows.appendChild(heading);

            for (const option of group.children) {
                const row = document.createElement('label'),
                    input = document.createElement('input'),
                    label = document.createElement('span');
                row.className = 'core-assignee-row d-flex align-items-center gap-2 px-2 py-1 m-0';
                input.type = 'checkbox';
                input.className = 'core-assignee-option flex-shrink-0';
                input.value = option.value;
                input.disabled = option.disabled || this.select.disabled;
                label.className = 'text-truncate';
                label.textContent = option.text;
                row.append(input, this.getAvatar(option), label);
                this.rows.appendChild(row);
            }
        }
    },

    getAvatar(option) {
        const avatar = document.createElement('span');
        avatar.className = 'core-assignee-avatar rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden';
        avatar.setAttribute('aria-hidden', 'true');

        if (option.dataset.image) {
            const image = document.createElement('img');
            image.src = option.dataset.image;
            image.alt = '';
            image.width = image.height = 24;
            avatar.appendChild(image);
        } else {
            avatar.textContent = Vtiger_AssigneeSelect_Js.getInitials(option.text);
        }

        return avatar;
    },

    loadDraft() {
        const selected = new Set(this.getValues());

        for (const input of this.rows.querySelectorAll('input')) {
            input.checked = selected.has(input.value);
        }
    },

    filterOptions(value) {
        const needle = value.trim().toLocaleLowerCase();

        for (const row of this.rows.querySelectorAll('label')) {
            row.classList.toggle('d-none', !row.textContent.toLocaleLowerCase().includes(needle));
        }
    },

    handleAction(action) {
        if (this.select.disabled) {
            return;
        }

        if (['all', 'mine', 'clear'].includes(action)) {
            for (const input of this.rows.querySelectorAll('input')) {
                input.checked = action === 'mine' && input.value === this.root.dataset.currentUser && !input.disabled;
            }
        }

        if (action !== 'close') {
            const values = Array.from(this.rows.querySelectorAll('input:checked'), input => input.value);
            this.setValues(values);
            this.select.dispatchEvent(new Event('change', {bubbles: true}));
        }

        bootstrap.Dropdown.getOrCreateInstance(this.button).hide();
    },

    showSummary() {
        const selected = Array.from(this.select.selectedOptions),
            summary = this.root.querySelector('.core-assignee-summary');
        summary.replaceChildren();
        this.button.disabled = this.select.disabled;

        if (selected.length) {
            summary.appendChild(this.getAvatar(selected[0]));
        }

        summary.appendChild(document.createTextNode(selected.length === 0 ? this.root.dataset.allLabel
            : selected.length === 1 ? selected[0].text : selected[0].text + ' + ' + (selected.length - 1)));
        this.button.title = selected.map(option => option.text).join(', ') || this.root.dataset.allLabel;
    }
});
jQuery(() => Vtiger_AssigneeSelect_Js.register(document));
