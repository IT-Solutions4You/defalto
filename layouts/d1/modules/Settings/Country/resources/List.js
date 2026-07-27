/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_Country_List_Js */
Settings_Vtiger_Index_Js("Settings_Country_List_Js", {}, {
    registerEvents: function () {
        this._super();
        this.registerModuleSearch();
        this.registerModuleStatus();
        this.registerButtons();
        this.registerPostalUpdate();
        this.registerAddressMap();
    },
    registerAddressMap: function () {
        const moduleSelect = $('.addressMapModule'),
            body = $('.addressMapBody'),
            groupsContainer = $('.addressMapGroups'),
            template = $('.addressGroupTemplate');

        // Address fields of the currently selected module: [{id, name, label}, ...].
        let currentFields = [];

        vtUtils.showSelect2ElementView(moduleSelect);

        // Fill a field <select> with the module's address fields, preselecting `selected`.
        function buildOptions(select, selected) {
            currentFields.forEach(function (field) {
                const option = $('<option></option>')
                    .val(field.id)
                    .text(field.label + ' (' + field.name + ')');

                if (selected && String(selected) === String(field.id)) {
                    option.prop('selected', true);
                }

                select.append(option);
            });
        }

        // Render one address group row from a saved group (or {} for a blank one).
        function renderGroup(group) {
            group = group || {};

            const node = template.children().first().clone();

            node.find('.agLabel').val(group.label || '');
            buildOptions(node.find('.agZip'), group.zip);
            buildOptions(node.find('.agCity'), group.city);
            buildOptions(node.find('.agState'), group.state);
            buildOptions(node.find('.agCountry'), group.country);

            groupsContainer.append(node);

            // Init select2 only once the row is in the (visible) DOM.
            vtUtils.showSelect2ElementView(node.find('.agZip, .agCity, .agState, .agCountry'));

            node.find('.removeAddressGroup').on('click', function () {
                node.remove();
            });
        }

        moduleSelect.on('change', function () {
            const moduleName = $(this).val();

            groupsContainer.empty();

            if (!moduleName) {
                body.addClass('d-none');
                return;
            }

            const progress = jQuery.progressIndicator({
                message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
                position: 'html',
                blockInfo: {enabled: true},
            });

            app.request.post({
                data: {
                    module: app.getModuleName(),
                    parent: 'Settings',
                    action: 'AddressMap',
                    mode: 'getModuleConfig',
                    target_module: moduleName,
                },
            }).then(function (error, data) {
                progress.progressIndicator({mode: 'hide'});

                if (error) {
                    app.helper.showErrorNotification({message: (error && error.message) ? error.message : error});
                    return;
                }

                currentFields = data.fields || [];
                body.removeClass('d-none');

                const groups = data.groups || [];

                if (groups.length) {
                    groups.forEach(renderGroup);
                } else {
                    // Start the admin off with one empty group.
                    renderGroup({});
                }
            });
        });

        $('.addAddressGroup').on('click', function () {
            if (!moduleSelect.val()) {
                return;
            }

            renderGroup({});
        });

        $('.saveAddressMap').on('click', function () {
            const button = $(this),
                moduleName = moduleSelect.val();

            if (!moduleName) {
                return;
            }

            const groups = [];
            let valid = true;

            groupsContainer.find('.addressGroup').each(function () {
                const row = $(this),
                    zip = row.find('.agZip').val(),
                    city = row.find('.agCity').val();

                // zip + city are mandatory; flag incomplete rows.
                if (!zip || !city) {
                    valid = false;
                    return;
                }

                groups.push({
                    label: row.find('.agLabel').val(),
                    zip: zip,
                    city: city,
                    state: row.find('.agState').val() || null,
                    country: row.find('.agCountry').val() || null,
                });
            });

            if (!valid) {
                app.helper.showErrorNotification({message: button.data('requiredMsg')});
                return;
            }

            const progress = jQuery.progressIndicator({
                message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
                position: 'html',
                blockInfo: {enabled: true},
            });

            button.prop('disabled', true);

            app.request.post({
                data: {
                    module: app.getModuleName(),
                    parent: 'Settings',
                    action: 'AddressMap',
                    mode: 'save',
                    target_module: moduleName,
                    groups: JSON.stringify(groups),
                },
            }).then(function (error, data) {
                progress.progressIndicator({mode: 'hide'});
                button.prop('disabled', false);

                if (error) {
                    app.helper.showErrorNotification({message: (error && error.message) ? error.message : error});
                    return;
                }

                app.helper.showSuccessNotification({message: data.message});
            });
        });
    },
    registerPostalUpdate: function () {
        const select = $('.postalCountryCode'),
            button = $('.updatePostalCodes');

        vtUtils.showSelect2ElementView(select);

        // Prevent triggering a full worldwide import: the button only works once a
        // specific country is chosen.
        select.on('change', function () {
            button.prop('disabled', !$(this).val());
        });

        button.on('click', function () {
            const code = select.val();

            if (!code) {
                return;
            }

            const progress = jQuery.progressIndicator({
                message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
                position: 'html',
                blockInfo: {enabled: true},
            });

            button.prop('disabled', true);

            app.request.post({
                data: {
                    module: app.getModuleName(),
                    action: 'Import',
                    parent: 'Settings',
                    mode: 'run',
                    country_code: code,
                },
            }).then(function (error, data) {
                progress.progressIndicator({mode: 'hide'});
                button.prop('disabled', false);

                if (error) {
                    app.helper.showErrorNotification({message: (error && error.message) ? error.message : error});
                    return;
                }

                app.helper.showSuccessNotification({message: data.message});
                window.location.reload();
            });
        });
    },
    registerButtons() {
        let params = {
            module: app.getModuleName(),
            action: 'SaveAjax',
            parent: 'Settings',
        };

        $('.activateAll').on('click', function () {
            params['mode'] = 'activateAll';

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                    $('.updateValue').attr('checked', 'checked');
                }
            });
        });
        $('.deactivateAll').on('click', function () {
            params['mode'] = 'deactivateAll';

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                    $('.updateValue').removeAttr('checked');
                }
            });
        });
    },
    registerModuleSearch: function () {
        const container = $('.searchContainer'),
            values = container.find('[data-search-value]');

        container.on('keyup', '.searchValues', function () {
            let value = $(this).val().trim();

            if (value) {
                values.addClass('hide');
                values.filter('[data-search-value*="' + value.toLowerCase() + '"]').removeClass('hide');
            } else {
                values.removeClass('hide');
            }
        });
    },
    registerModuleStatus: function () {
        $(document).on('click', '.updateValue', function () {
            const field = $(this),
                params = {
                    module: app.getModuleName(),
                    action: 'SaveAjax',
                    parent: 'Settings',
                    mode: 'update',
                    value: field.attr('data-value'),
                    is_active: field.prop('checked'),
                };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                }
            });
        });
    },
});
