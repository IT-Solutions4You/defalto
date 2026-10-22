/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var Settings_InventoryItem_Index_Js **/
Settings_Vtiger_Index_Js("Settings_InventoryItem_Index_Js", {}, {
    registerEvents: function () {
        this._super();
        this.makeColumnListSortable();
        this.registerModuleChange();
        this.registerConversionsSave();
        this.registerConversionMapping();
    },

    makeColumnListSortable: function () {
        const inventoryItemFieldsForm = jQuery('#inventoryItemFields'),
            selectElement = $('#selectedFields'),
            valueElement = $('input[name="columnslist"]', inventoryItemFieldsForm);

        vtUtils.makeSelect2ElementSortable(
            selectElement,
            valueElement,
            function (valueElement) {
                return JSON.parse(valueElement.val());
            },
            function (valueElement, selectedValues) {
                valueElement.val(JSON.stringify(selectedValues));
            }
        );
    },

    registerModuleChange: function () {
        const moduleElement = $('#selectedModule');

        moduleElement.on('change', function () {
            window.location.href = 'index.php?module=InventoryItem&parent=Settings&view=Index&selectedModule=' + moduleElement.val();
        });
    },

    registerConversionMapping: function () {
        const self = this;

        jQuery('#conversionsForm').on('click', '.editConversionMapping', function (e) {
            const params = {
                module: 'InventoryItem',
                parent: 'Settings',
                view: 'ConversionMapping',
                conversion: jQuery(e.currentTarget).data('conversionId')
            };

            app.helper.showProgress();
            app.request.post({data: params}).then(function (error, data) {
                app.helper.hideProgress();

                if (error) {
                    app.helper.showErrorNotification({message: error.message});

                    return;
                }

                app.helper.showModal(data, {
                    cb: function (modal) {
                        self.registerConversionMappingEvents(modal);
                    }
                });
            });
        });
    },

    /**
     * Offers only the target fields which are compatible with the source field of the row.
     *
     * @param {jQuery} row
     * @param {Object} compatibleTargets [sourceField => [targetField, ...]]
     */
    filterConversionMappingTargets: function (row, compatibleTargets) {
        const source = jQuery('.conversionMappingSource', row).val(),
            targetSelect = jQuery('.conversionMappingTarget', row);

        targetSelect.find('option[value!=""]').each(function (index, option) {
            const allowed = !source || (compatibleTargets[source] || []).indexOf(option.value) !== -1;

            jQuery(option).prop('disabled', !allowed).toggleClass('d-none', !allowed);
        });

        if (targetSelect.find('option:selected').prop('disabled')) {
            targetSelect.val('').trigger('change');
        }
    },

    /**
     * Shows the select boxes of a mapping row as select2. Target options which are not compatible with the source field are hidden.
     *
     * @param {jQuery} row
     */
    registerConversionMappingSelects: function (row) {
        vtUtils.showSelect2ElementView(jQuery('select', row), {
            width: '100%',
            templateResult: function (option) {
                return option.disabled ? null : option.text;
            }
        });
    },

    registerConversionMappingEvents: function (modal) {
        const self = this,
            form = jQuery('#conversionMappingForm', modal),
            rows = jQuery('.conversionMappingRows', form),
            compatibleTargets = JSON.parse(form.attr('data-compatible-targets') || '{}');

        form.on('click', '.addConversionMappingRow', function () {
            rows.append(jQuery('.conversionMappingRowTemplate', form).html());
            self.registerConversionMappingSelects(rows.children().last());
        });

        form.on('change', '.conversionMappingSource', function (e) {
            self.filterConversionMappingTargets(jQuery(e.currentTarget).closest('tr'), compatibleTargets);
        });

        jQuery('.conversionMappingRow', rows).each(function (index, row) {
            self.filterConversionMappingTargets(jQuery(row), compatibleTargets);
            self.registerConversionMappingSelects(jQuery(row));
        });

        form.on('click', '.removeConversionMappingRow', function (e) {
            jQuery(e.currentTarget).closest('tr').remove();
        });

        form.on('submit', function (e) {
            e.preventDefault();

            const data = {
                module: 'InventoryItem',
                parent: 'Settings',
                action: 'ConversionMappingSave',
                conversion: jQuery('input[name="conversion"]', form).val(),
                rows: []
            };

            jQuery('.conversionMappingRow', rows).each(function (index, row) {
                const target = jQuery('.conversionMappingTarget', row).val();

                if (target) {
                    data.rows.push({target: target, source: jQuery('.conversionMappingSource', row).val()});
                }
            });

            app.helper.showProgress();
            app.request.post({data: data}).then(function (error) {
                app.helper.hideProgress();

                if (error) {
                    app.helper.showErrorNotification({message: error.message});

                    return;
                }

                app.helper.hideModal();
                app.helper.showSuccessNotification({message: app.vtranslate('JS_CONVERSIONS_SAVED')});
            });
        });
    },

    registerConversionsSave: function () {
        const form = jQuery('#conversionsForm');

        form.on('submit', function (e) {
            e.preventDefault();
            app.helper.showProgress();
            app.request.post({data: form.serializeFormData()}).then(function (error) {
                app.helper.hideProgress();

                if (error) {
                    app.helper.showErrorNotification({message: error.message});

                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_CONVERSIONS_SAVED')});
            });
        });
    },
});