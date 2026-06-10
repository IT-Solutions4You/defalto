/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Enhances every phone (uitype 11) field with the intl-tel-input widget:
 *  - edit views (full edit, quick create/edit, inline edit) get a searchable
 *    country selector + flag + international formatting, and the value is stored
 *    as E.164 (e.g. +421905453992);
 *  - detail and list views render a flag + the internationally formatted number.
 *
 * The list of selectable countries + the initial country come from
 * #phone_field_config (Settings > Country), injected in Footer.tpl.
 */
const Vtiger_Phone_Js = {

    _config: null,
    _sharedIti: null,

    isReady: function () {
        return typeof window.intlTelInput !== 'undefined';
    },

    getConfig: function () {
        if (this._config) {
            return this._config;
        }

        let cfg = {};
        try {
            const raw = jQuery('#phone_field_config').text();
            cfg = raw ? JSON.parse(raw) : {};
        } catch (e) {
            cfg = {};
        }

        this._config = {
            countries: (cfg.countries && cfg.countries.length) ? cfg.countries : [],
            default: cfg.default || ''
        };

        return this._config;
    },

    getEditOptions: function () {
        const cfg = this.getConfig();
        const options = {
            nationalMode: false,
            separateDialCode: false,
            countrySearch: true,
            formatOnDisplay: true,
            autoPlaceholder: 'aggressive',
            dropdownContainer: document.body
        };

        if (cfg.countries.length) {
            options.onlyCountries = cfg.countries;
        }
        if (cfg.default) {
            options.initialCountry = cfg.default;
        }

        return options;
    },

    /* -------------------------------------------------------------- edit -- */

    initEdit: function (container) {
        if (!this.isReady()) {
            return;
        }

        const self = this,
            $c = container ? jQuery(container) : jQuery(document);

        let inputs = $c.find('input.js-iti-phone');

        if ($c.is('input.js-iti-phone')) {
            inputs = inputs.add($c);
        }

        inputs.each(function () {
            self.initInput(this);
        });
    },

    initInput: function (input) {
        const $input = jQuery(input);

        if ($input.data('itiInitialized')) {
            return;
        }

        const el = $input.get(0),
            iti = window.intlTelInput(el, this.getEditOptions());

        el.itiInstance = iti;
        $input.data('itiInitialized', true).addClass('js-iti-ready');

        const markValidity = function () {
            if (!$input.val().trim()) {
                $input.removeClass('js-iti-valid js-iti-invalid');
                return;
            }
            if (iti.isValidNumber()) {
                $input.addClass('js-iti-valid').removeClass('js-iti-invalid');
            } else {
                $input.addClass('js-iti-invalid').removeClass('js-iti-valid');
            }
        };

        el.addEventListener('blur', markValidity);
        el.addEventListener('countrychange', markValidity);
    },

    /** Rewrite a single enhanced input to hold its E.164 value. */
    toE164: function (input) {
        const el = jQuery(input).get(0);

        if (el && el.itiInstance) {
            const number = el.itiInstance.getNumber();
            if (number) {
                el.value = number;
            }
        }
    },

    /** Rewrite all enhanced inputs to E.164 (called right before a form saves). */
    syncAllToE164: function (container) {
        const self = this,
            $c = container ? jQuery(container) : jQuery(document);

        $c.find('input.js-iti-ready').each(function () {
            self.toE164(this);
        });
    },

    /* ----------------------------------------------------------- display -- */

    getSharedIti: function () {
        if (this._sharedIti) {
            return this._sharedIti;
        }
        if (!this.isReady()) {
            return null;
        }

        const holder = document.createElement('div'),
            input = document.createElement('input');

        holder.className = 'js-iti-shared-holder';
        holder.style.position = 'absolute';
        holder.style.left = '-9999px';
        holder.style.top = '0';
        input.type = 'text';
        holder.appendChild(input);
        document.body.appendChild(holder);

        this._sharedIti = window.intlTelInput(input, {
            initialCountry: this.getConfig().default || 'us',
            separateDialCode: false
        });

        return this._sharedIti;
    },

    /** @return {{iso2: string, text: string}|null} */
    formatNumber: function (raw) {
        const iti = this.getSharedIti();

        if (!iti || !raw || !String(raw).trim()) {
            return null;
        }

        try {
            iti.setNumber(String(raw));

            const data = iti.getSelectedCountryData();
            let text;

            if (window.intlTelInput.utils && window.intlTelInput.utils.numberFormat) {
                text = iti.getNumber(window.intlTelInput.utils.numberFormat.INTERNATIONAL);
            } else {
                text = iti.getNumber();
            }

            return {
                iso2: (data && data.iso2) ? data.iso2 : '',
                text: text || String(raw)
            };
        } catch (e) {
            return null;
        }
    },

    buildFlag: function (iso2) {
        const flag = document.createElement('span');
        flag.className = 'iti__flag iti__' + iso2 + ' iti-display-flag';
        return flag;
    },

    formatDisplay: function (container) {
        if (!this.isReady()) {
            return;
        }

        const self = this,
            $c = container ? jQuery(container) : jQuery(document);

        $c.find('td[data-field-type="phone"]').each(function () {
            self.formatCell(this);
        });

        $c.find('span.js-iti-display').each(function () {
            self.formatSpan(this);
        });
    },

    formatCell: function (td) {
        const $td = jQuery(td),
            $value = $td.find('.fieldValue .value').first();

        if (!$value.length || $value.children('.iti-display').length) {
            return;
        }

        // Prefer the currently shown text (kept fresh after inline edit), fall
        // back to the stored raw value.
        const raw = jQuery.trim($value.text()) || $td.attr('data-rawvalue'),
            info = this.formatNumber(raw);

        if (!info) {
            return;
        }

        $value.empty().append(this.buildDisplayNode(info));
    },

    formatSpan: function (span) {
        const $span = jQuery(span);

        if ($span.hasClass('js-iti-done')) {
            return;
        }
        $span.addClass('js-iti-done');

        const raw = $span.attr('data-iti-number'),
            info = this.formatNumber(raw);

        if (!info) {
            return;
        }

        if (info.iso2 && !$span.children('.iti-display-flag').length) {
            $span.prepend(this.buildFlag(info.iso2));
        }

        const $number = $span.find('.iti-display-number').first(),
            $anchor = $number.find('a').first();

        if ($anchor.length) {
            $anchor.text(info.text);
        } else if ($number.length) {
            $number.text(info.text);
        }
    },

    /** Wrap a freshly-saved value (list / detail inline edit) for formatting. */
    buildDisplayNode: function (info) {
        const wrap = document.createElement('span');
        wrap.className = 'iti-display';

        if (info.iso2) {
            wrap.appendChild(this.buildFlag(info.iso2));
        }

        const number = document.createElement('span');
        number.className = 'iti-display-number';
        number.textContent = info.text;
        wrap.appendChild(number);

        return wrap;
    },

    /** Re-render a detail field value after it was inline-edited. */
    refreshDetailValue: function (fieldBasicData) {
        const $fb = jQuery(fieldBasicData);

        if ($fb.data('type') !== 'phone') {
            return;
        }

        let $wrapper = $fb.closest('td');
        if (!$wrapper.length) {
            $wrapper = $fb.closest('.td');
        }
        if (!$wrapper.length) {
            $wrapper = $fb.closest('.fieldValue');
        }

        const $value = $wrapper.find('.value').first(),
            info = this.formatNumber($fb.data('value'));

        if (!$value.length || !info) {
            return;
        }

        $value.empty().append(this.buildDisplayNode(info));
    },

    /* ------------------------------------------------------------ wiring -- */

    register: function () {
        const self = this;

        // Edit forms (full edit, quick create, quick edit, overlays).
        app.event.on('post.editView.load', function (e, container) {
            self.initEdit(container || jQuery(document));
        });

        // Normalise the visible number to E.164 before any form is serialized
        // or submitted. Capture phase runs before jQuery validation handlers,
        // covering full edit, quick create (own submitHandler), mass edit, etc.
        document.addEventListener('submit', function (e) {
            if (e.target) {
                self.syncAllToE164(jQuery(e.target));
            }
        }, true);

        // Belt-and-braces for save paths that fire the presave event directly.
        app.event.on(
            (typeof Vtiger_Edit_Js !== 'undefined' && Vtiger_Edit_Js.recordPresaveEvent) || 'Pre.Record.Save',
            function () {
                self.syncAllToE164(jQuery(document));
            }
        );

        // Detail inline edit replaces the value with the plain server value.
        if (typeof Vtiger_Detail_Js !== 'undefined') {
            app.event.on(Vtiger_Detail_Js.PostAjaxSaveEvent, function (e, fieldBasicData) {
                self.refreshDetailValue(fieldBasicData);
            });
        }

        // Detail inline save reads the named input directly (no presave event),
        // so normalise to E.164 in the capture phase, before Detail.js reads it.
        document.addEventListener('click', function (e) {
            const target = e.target,
                button = target && target.closest ? target.closest('.inlineAjaxSave') : null;

            if (!button) {
                return;
            }

            const scope = button.closest('td') || button.closest('.fieldValue') || document;
            jQuery(scope).find('input.js-iti-ready').each(function () {
                self.toE164(this);
            });
        }, true);

        // Content loaded over AJAX (list pagination, related lists, modals…).
        let pending = null;
        jQuery(document).ajaxComplete(function () {
            if (pending) {
                return;
            }
            pending = window.setTimeout(function () {
                pending = null;
                self.initEdit(jQuery(document));
                self.formatDisplay(jQuery(document));
            }, 50);
        });

        // Initial pass for whatever is already on the page.
        self.initEdit(jQuery(document));
        self.formatDisplay(jQuery(document));
    }
};

jQuery(function () {
    if (typeof app !== 'undefined' && app.event) {
        Vtiger_Phone_Js.register();
    }
});
