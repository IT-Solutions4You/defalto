/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_GlobalSearch_List_Js */
Settings_Vtiger_Index_Js('Settings_GlobalSearch_List_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerFieldSelectors();
        this.registerFilter();
        this.registerSave();
    },

    registerFieldSelectors: function () {
        vtUtils.showSelect2ElementView(jQuery('.globalSearchFields'));
    },

    registerFilter: function () {
        jQuery('.globalSearchFilter').on('input', function () {
            const searchValue = jQuery(this).val().toLowerCase().trim();

            jQuery('.globalSearchModuleRow').each(function () {
                const row = jQuery(this),
                    searchableValues = String(row.data('searchValue') || '').toLocaleLowerCase(),
                    isVisible = searchableValues.indexOf(searchValue) !== -1;

                row.toggleClass('d-none', !isVisible);
            });
        });
    },

    registerSave: function () {
        jQuery('.saveGlobalSearchSettings').on('click', function () {
            let button = jQuery(this),
                configuration = [],
                isValid = true;

            jQuery('.globalSearchModuleRow').each(function () {
                const row = jQuery(this),
                    isActive = row.find('.globalSearchActive').is(':checked'),
                    fieldIds = row.find('.globalSearchFields').val() || [];

                if (isActive && fieldIds.length === 0) {
                    isValid = false;
                    row.find('.globalSearchFields').select2('open');
                    return false;
                }

                configuration.push({
                    tab_id: row.data('tabId'),
                    is_active: isActive ? 1 : 0,
                    field_ids: fieldIds,
                });
            });

            if (!isValid) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_GLOBAL_SEARCH_FIELDS_REQUIRED')});
                return;
            }

            button.prop('disabled', true);
            app.helper.showProgress();
            app.request.post({
                data: {
                    module: app.getModuleName(),
                    parent: 'Settings',
                    action: 'Save',
                    configuration: JSON.stringify(configuration),
                },
            }).then(function (error, data) {
                app.helper.hideProgress();
                button.prop('disabled', false);

                if (error) {
                    app.helper.showErrorNotification({message: error.message || error});
                    return;
                }

                app.helper.showSuccessNotification({message: data.message});
            });
        });
    },
});
