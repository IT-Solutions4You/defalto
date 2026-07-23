/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Address block widget. Registered as a vtUtils field widget, so:
 *   - initEdit(container) runs for every freshly rendered edit subtree (full
 *     edit, quick create, quick/inline edit, ajax-loaded content) via
 *     vtUtils.applyFieldElementsView — Part E attaches the bidirectional PSČ/city
 *     autocomplete here;
 *   - registerEvents() runs once with document-level handlers — Part F wires the
 *     detail-view block-level inline edit here.
 *
 * Self-gates on the DOM: does nothing unless the subtree/page actually contains
 * an address block. Loaded only on the address-bearing modules (Accounts,
 * Contacts, Leads, Vendors, Invoice, SalesOrder, PurchaseOrder, Quotes) via each
 * module's Detail/Edit/QuickCreateAjax view getHeaderScripts.
 */
jQuery.Class("Core_AddressBlock_Js", {

    _instance: null,

    /**
     * Singleton accessor — the widget binds document-level handlers, so there
     * must only ever be one instance.
     */
    getInstance: function () {
        if (!Core_AddressBlock_Js._instance) {
            Core_AddressBlock_Js._instance = new Core_AddressBlock_Js();
        }

        return Core_AddressBlock_Js._instance;
    }
}, {

    _eventsRegistered: false,

    /** Minimum characters typed before a lookup fires. */
    MIN_CHARS: 2,

    /** Debounce window for the lookup request (ms). */
    DEBOUNCE_MS: 250,

    /** Max suggestions requested per lookup. */
    LOOKUP_LIMIT: 15,

    /**
     * Attach the autocomplete to any address edit blocks in a freshly rendered
     * subtree. Called by vtUtils.applyFieldElementsView for every edit form.
     *
     * @param {jQuery|HTMLElement} [container]
     */
    initEdit: function (container) {
        const self = this;
        const $c = container ? jQuery(container) : jQuery(document);

        let editBlocks = $c.find('.addressEditBlock');

        if ($c.is('.addressEditBlock')) {
            editBlocks = editBlocks.add($c);
        }

        if (!editBlocks.length) {
            // No address block in this subtree — nothing to do.
            return;
        }

        editBlocks.each(function () {
            self._initBlock(jQuery(this));
        });
    },

    /**
     * Wire a single .addressEditBlock: read its data-address-map (JSON groups of
     * resolved field names) and attach the autocomplete to each group. Guarded so
     * a block is only wired once even when initEdit runs on overlapping containers.
     *
     * @param {jQuery} $block
     */
    _initBlock: function ($block) {
        if ($block.data('addressBlockInit')) {
            return;
        }

        $block.data('addressBlockInit', true);

        const groups = this._parseGroups($block.attr('data-address-map'));

        if (!groups.length) {
            return;
        }

        const moduleName = $block.attr('data-address-module')
            || (typeof app !== 'undefined' && app.getModuleName ? app.getModuleName() : '');

        const self = this;

        groups.forEach(function (group) {
            self._wireGroup($block, group, moduleName);
        });
    },

    /**
     * Decode the data-address-map attribute into an array of groups. Tolerates a
     * missing / malformed value (returns []).
     *
     * @param {string} raw
     * @returns {Array}
     */
    _parseGroups: function (raw) {
        if (!raw) {
            return [];
        }

        let groups;

        try {
            groups = JSON.parse(raw);
        } catch (e) {
            return [];
        }

        return Array.isArray(groups) ? groups : [];
    },

    /**
     * Attach the two-way autocomplete to one address group. Both the zip and the
     * city input become lookup sources; picking a suggestion fills the whole group
     * (zip, city, and — when mapped — state and country).
     *
     * @param {jQuery} $block
     * @param {Object} group  {label, zip, city, state, country} field names
     * @param {string} moduleName
     */
    _wireGroup: function ($block, group, moduleName) {
        const inputs = {
            zip: this._findInput($block, group.zip),
            city: this._findInput($block, group.city),
            state: this._findInput($block, group.state),
            country: this._findInput($block, group.country)
        };

        // zip + city are mandatory for a usable group; without both there is nothing to wire.
        if (!inputs.zip.length || !inputs.city.length) {
            return;
        }

        this._attachAutocomplete(inputs.zip, 'zip', inputs, moduleName);
        this._attachAutocomplete(inputs.city, 'city', inputs, moduleName);
    },

    /**
     * Locate a group field's input inside the block by its field name. Returns an
     * empty jQuery set for an unmapped (null) field.
     *
     * @param {jQuery} $block
     * @param {?string} name
     * @returns {jQuery}
     */
    _findInput: function ($block, name) {
        if (!name) {
            return jQuery();
        }

        return $block.find('[name="' + name + '"]').first();
    },

    /**
     * Attach a debounced suggestion dropdown to one input. `by` selects the search
     * direction ('zip' or 'city'); a pick fills the whole group via _applySuggestion.
     *
     * @param {jQuery} $input
     * @param {string} by       'zip' | 'city'
     * @param {Object} inputs   the group's {zip, city, state, country} jQuery inputs
     * @param {string} moduleName
     */
    _attachAutocomplete: function ($input, by, inputs, moduleName) {
        if (!$input.length || $input.data('addressAutocomplete')) {
            return;
        }

        $input.data('addressAutocomplete', true);
        $input.attr('autocomplete', 'off');

        const self = this;
        const state = {timer: null, seq: 0, menu: null, rows: [], active: -1};

        function close() {
            if (state.menu) {
                state.menu.remove();
                state.menu = null;
            }

            state.rows = [];
            state.active = -1;
        }

        function highlight(index) {
            if (!state.menu) {
                return;
            }

            const items = state.menu.children('.dropdown-item');
            items.removeClass('active');

            if (index >= 0 && index < items.length) {
                jQuery(items[index]).addClass('active');
                state.active = index;
            } else {
                state.active = -1;
            }
        }

        function pick(index) {
            if (index < 0 || index >= state.rows.length) {
                return;
            }

            self._applySuggestion(inputs, state.rows[index]);
            close();
            $input.trigger('focus');
        }

        function render(rows) {
            close();

            if (!rows.length) {
                return;
            }

            state.rows = rows;

            const $menu = jQuery('<div class="dropdown-menu show addressSuggestions p-1"></div>');

            rows.forEach(function (row, index) {
                const $item = jQuery('<button type="button" class="dropdown-item text-truncate"></button>');
                $item.text(self._suggestionLabel(row));
                // mousedown fires before the input's blur, so the pick is not lost to close().
                $item.on('mousedown', function (e) {
                    e.preventDefault();
                    pick(index);
                });
                $item.on('mouseenter', function () {
                    highlight(index);
                });
                $menu.append($item);
            });

            self._positionMenu($menu, $input);
            jQuery('body').append($menu);
            state.menu = $menu;
        }

        function lookup(term) {
            const seq = ++state.seq;
            const country = inputs.country.length ? jQuery.trim(String(inputs.country.val() || '')) : '';

            self._fetch(moduleName, term, by, country).then(function (rows) {
                // Drop out-of-order / stale responses.
                if (seq !== state.seq || jQuery.trim($input.val()).length < self.MIN_CHARS) {
                    return;
                }

                render(rows);
            });
        }

        $input.on('input', function () {
            const term = jQuery.trim($input.val());

            clearTimeout(state.timer);

            if (term.length < self.MIN_CHARS) {
                close();

                return;
            }

            state.timer = setTimeout(function () {
                lookup(term);
            }, self.DEBOUNCE_MS);
        });

        $input.on('keydown', function (e) {
            if (!state.menu) {
                return;
            }

            switch (e.which) {
                case 40: // down
                    e.preventDefault();
                    highlight(Math.min(state.active + 1, state.rows.length - 1));
                    break;
                case 38: // up
                    e.preventDefault();
                    highlight(Math.max(state.active - 1, 0));
                    break;
                case 13: // enter
                    if (state.active >= 0) {
                        e.preventDefault();
                        pick(state.active);
                    }
                    break;
                case 27: // escape
                    close();
                    break;
            }
        });

        // Delay close so a click on a suggestion (mousedown) still registers.
        $input.on('blur', function () {
            setTimeout(close, 150);
        });
    },

    /**
     * Human-readable label for a suggestion row: "12345 — Placename, Region".
     *
     * @param {Object} row
     * @returns {string}
     */
    _suggestionLabel: function (row) {
        let label = (row.postal_code || '') + ' — ' + (row.place_name || '');

        if (row.admin_name1) {
            label += ', ' + row.admin_name1;
        }

        return label;
    },

    /**
     * Fill a group's inputs from a chosen suggestion. City + zip always; state and
     * country only when the group maps them. The country field is the Country
     * uitype select whose option values are ISO2 codes — exactly country_code — so
     * setting the value + change updates its select2.
     *
     * @param {Object} inputs
     * @param {Object} row
     */
    _applySuggestion: function (inputs, row) {
        this._setValue(inputs.zip, row.postal_code);
        this._setValue(inputs.city, row.place_name);

        if (inputs.state.length && row.admin_name1) {
            this._setValue(inputs.state, row.admin_name1);
        }

        if (inputs.country.length && row.country_code) {
            this._setValue(inputs.country, row.country_code);
        }
    },

    /**
     * Set an input/select value and fire change (keeps select2, validation and the
     * dirty-form tracker in sync).
     *
     * @param {jQuery} $el
     * @param {string} value
     */
    _setValue: function ($el, value) {
        if (!$el || !$el.length) {
            return;
        }

        $el.val(value).trigger('change');
    },

    /**
     * Query the postal-code endpoint. Resolves to the rows array (never rejects —
     * a failed request just yields []).
     *
     * @param {string} moduleName
     * @param {string} term
     * @param {string} by       'zip' | 'city'
     * @param {string} country  ISO2 filter, or '' for all countries
     * @returns {Promise<Array>}
     */
    _fetch: function (moduleName, term, by, country) {
        const self = this;

        return new Promise(function (resolve) {
            app.request.get({
                data: {
                    module: moduleName || 'Vtiger',
                    action: 'PostalLookup',
                    term: term,
                    by: by,
                    country: country,
                    limit: self.LOOKUP_LIMIT
                }
            }).then(function (err, data) {
                if (err !== null || !data || !Array.isArray(data.records)) {
                    resolve([]);

                    return;
                }

                resolve(data.records);
            });
        });
    },

    /**
     * Position a suggestion menu directly under its input. Appended to <body> so it
     * escapes any overflow-clipping form container; page coordinates keep it aligned.
     *
     * @param {jQuery} $menu
     * @param {jQuery} $input
     */
    _positionMenu: function ($menu, $input) {
        const rect = $input[0].getBoundingClientRect();

        $menu.css({
            position: 'absolute',
            zIndex: 1080,
            top: (rect.bottom + window.scrollY) + 'px',
            left: (rect.left + window.scrollX) + 'px',
            minWidth: rect.width + 'px',
            maxHeight: '260px',
            overflowY: 'auto'
        });
    },

    /**
     * One-time, document-level wiring. Idempotent — safe to call more than once
     * (another field widget's ready handler may loop over every widget).
     */
    registerEvents: function () {
        if (this._eventsRegistered) {
            return;
        }

        this._eventsRegistered = true;

        const self = this;
        const $doc = jQuery(document);

        // Part F — detail-view block-level inline edit. Delegated so it also covers
        // ajax-rendered detail (overlay / quick preview). The standard per-field
        // inline-edit handler also fires for these ✎ clicks but safely no-ops
        // (an address group row has no `.edit`/`.fieldBasicData` child).
        $doc.on('click', '.editAddressGroup', function (e) {
            e.preventDefault();
            self._openGroupEdit(jQuery(this).closest('.addressGroup'));
        });

        $doc.on('click', '.cancelAddressGroup', function (e) {
            e.preventDefault();
            self._closeGroupEdit(jQuery(this).closest('.addressGroup'), true);
        });

        $doc.on('click', '.saveAddressGroup', function (e) {
            e.preventDefault();
            self._saveGroupEdit(jQuery(this).closest('.addressGroup'));
        });

        // Initial pass over whatever is already on the page.
        this.initEdit($doc);
    },

    /**
     * Reveal a group's inline edit form. On first open, initialise its field
     * widgets (select2, date pickers AND the Part E autocomplete) and snapshot the
     * current values so Cancel can restore them.
     *
     * @param {jQuery} $row  the .addressGroup row
     */
    _openGroupEdit: function ($row) {
        const $form = $row.find('.addressGroupEditForm').first();

        if (!$form.length || !$form.hasClass('hide')) {
            return;
        }

        if (!$form.data('addressFieldsReady')) {
            $form.data('addressFieldsReady', true);

            this._applyPlaceholders($form);

            if (typeof vtUtils !== 'undefined' && vtUtils.applyFieldElementsView) {
                vtUtils.applyFieldElementsView($form);
            }
        }

        this._snapshot($form);

        $row.find('.addressFormatted').addClass('hide');
        $row.find('.action').addClass('hide');
        $form.removeClass('hide');
    },

    /**
     * Compact inline editor: the fields carry their label in
     * data-address-placeholder instead of rendering a separate label line, so we
     * push it into the widget's placeholder here. A <select> cannot show a
     * placeholder (it always renders its selected option), so it gets a title
     * tooltip instead.
     *
     * @param {jQuery} $form
     */
    _applyPlaceholders: function ($form) {
        $form.find('[data-address-placeholder]').each(function () {
            const $wrapper = jQuery(this);
            const label = $wrapper.attr('data-address-placeholder');

            if (!label) {
                return;
            }

            $wrapper.find('input, textarea').attr('placeholder', label);
            $wrapper.find('select').attr('title', label);
        });
    },

    /**
     * Hide a group's edit form and show the formatted line again.
     *
     * @param {jQuery} $row
     * @param {boolean} [restore]  restore inputs to the pre-edit snapshot (Cancel)
     */
    _closeGroupEdit: function ($row, restore) {
        const $form = $row.find('.addressGroupEditForm').first();

        if (!$form.length) {
            return;
        }

        if (restore) {
            this._restore($form);
        }

        $form.addClass('hide');
        $row.find('.addressFormatted').removeClass('hide');
        $row.find('.action').removeClass('hide');
    },

    /**
     * Persist a group's fields with a single SaveAjax (it accepts many field/value
     * pairs at once), then rebuild the formatted line from the response and close.
     *
     * @param {jQuery} $row
     */
    _saveGroupEdit: function ($row) {
        const self = this;
        const $form = $row.find('.addressGroupEditForm').first();

        if (!$form.length) {
            return;
        }

        const moduleName = $form.attr('data-address-module');
        const data = {
            module: moduleName,
            action: 'SaveAjax',
            record: (typeof app !== 'undefined' && app.getRecordId) ? app.getRecordId() : $row.closest('[data-record-id]').data('record-id')
        };

        $form.find('[name]').each(function () {
            const $f = jQuery(this);
            data[$f.attr('name')] = $f.val();
        });

        const $save = $form.find('.saveAddressGroup').prop('disabled', true);

        app.request.post({data: data}).then(function (err, resp) {
            $save.prop('disabled', false);

            if (err !== null) {
                app.helper.showErrorNotification({message: err});

                return;
            }

            app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
            self._renderGroupLine($row, $form, resp);
            self._snapshot($form);
            self._closeGroupEdit($row, false);
        });
    },

    /**
     * Rebuild a group's formatted block after a save, in the same shape as the PHP
     * template — street(s), "zip city", state, country, each on its own line, empty
     * parts skipped — using the saved display values returned by SaveAjax (country
     * resolves to its name).
     *
     * @param {jQuery} $row
     * @param {jQuery} $form
     * @param {Object} resp  SaveAjax result map: fieldName -> {value, display_value}
     */
    _renderGroupLine: function ($row, $form, resp) {
        const self = this;
        const fields = this._parseGroups($form.attr('data-address-fields'));

        function display(name) {
            if (resp && resp[name] && typeof resp[name].display_value !== 'undefined') {
                // SaveAjax returns HTML-entity-encoded display values (e.g. "Pre&scaron;ov");
                // decode so the rebuilt line matches the server-rendered one.
                return self._decodeEntities(String(resp[name].display_value));
            }

            return String($form.find('[name="' + name + '"]').val() || '');
        }

        const street = [];
        let zip = '', city = '', state = '', country = '';

        fields.forEach(function (f) {
            const value = jQuery.trim(display(f.name));

            switch (f.role) {
                case 'street':
                    if (value) {
                        street.push(value);
                    }
                    break;
                case 'zip':
                    zip = value;
                    break;
                case 'city':
                    city = value;
                    break;
                case 'state':
                    state = value;
                    break;
                case 'country':
                    country = value;
                    break;
            }
        });

        const parts = [street.join(', '), jQuery.trim(zip + ' ' + city), state, country].filter(function (p) {
            return jQuery.trim(p) !== '';
        });

        // Render each part on its own line to match the server-rendered detail block.
        const $formatted = $row.find('.addressFormatted').empty();

        parts.forEach(function (part, index) {
            if (index > 0) {
                $formatted.append(document.createElement('br'));
            }

            $formatted.append(document.createTextNode(part));
        });
    },

    /**
     * Decode HTML entities in a string (e.g. "Pre&scaron;ov" -> "Prešov") via a
     * detached textarea. Safe: the result is only ever written with .text().
     *
     * @param {string} str
     * @returns {string}
     */
    _decodeEntities: function (str) {
        if (!str || str.indexOf('&') === -1) {
            return str;
        }

        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;

        return textarea.value;
    },

    /**
     * Snapshot a form's current input values (for Cancel / after Save).
     *
     * @param {jQuery} $form
     */
    _snapshot: function ($form) {
        const snapshot = {};

        $form.find('[name]').each(function () {
            snapshot[jQuery(this).attr('name')] = jQuery(this).val();
        });

        $form.data('addressSnapshot', snapshot);
    },

    /**
     * Restore a form's inputs to the last snapshot.
     *
     * @param {jQuery} $form
     */
    _restore: function ($form) {
        const snapshot = $form.data('addressSnapshot') || {};

        $form.find('[name]').each(function () {
            const $f = jQuery(this);
            const name = $f.attr('name');

            if (Object.prototype.hasOwnProperty.call(snapshot, name)) {
                $f.val(snapshot[name]).trigger('change');
            }
        });
    }
});

if (typeof vtUtils !== 'undefined') {
    vtUtils.registerFieldWidget(Core_AddressBlock_Js);
}

jQuery(function () {
    if (typeof vtUtils === 'undefined') {
        return;
    }

    // Trigger only this widget (registerEvents is idempotent); other widgets own
    // their own ready handler, so we must not re-run theirs.
    Core_AddressBlock_Js.getInstance().registerEvents();
});
