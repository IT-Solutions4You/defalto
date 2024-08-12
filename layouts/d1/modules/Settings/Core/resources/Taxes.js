/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var Settings_Vtiger_Taxes_Js */
Vtiger.Class('Settings_Vtiger_Taxes_Js', {
    instance: false,
    getInstance() {
        if (!this.instance) {
            this.instance = new Settings_Vtiger_Taxes_Js()
        }

        return this.instance;
    },
    editTax(recordId) {
        Settings_Vtiger_Taxes_Js.getInstance().editTax(recordId);
    },
    editRegion(recordId) {
        Settings_Vtiger_Taxes_Js.getInstance().editRegion(recordId);
    },
}, {
    editTax(recordId) {
        const self = this,
            params = {
                module: app.getModuleName(),
                view: 'Taxes',
                mode: 'editTax',
                parent: 'Settings',
            }

        if (recordId) {
            params['record'] = recordId;
        }

        self.editRecord(params);
    },
    editRegion(recordId) {
        const self = this,
            params = {
                module: app.getModuleName(),
                view: 'Taxes',
                mode: 'editRegion',
                parent: 'Settings',
            }

        if (recordId) {
            params['record'] = recordId;
        }

        self.editRecord(params);
    },
    editRecord(params) {
        const self = this;

        app.request.post({data: params}).then(function (error, data) {
            if (!error) {
                app.helper.showModal(data, {
                    cb(data) {
                        self.registerEventsForEdit(data)
                    }
                })
            }
        })
    },
    deleteTax(recordId) {
        const self = this,
            params = {
                module: app.getModuleName(),
                action: 'Taxes',
                mode: 'delete',
                parent: 'Settings',
                record: recordId,
            };

        self.deleteRecord(params);
    },
    deleteRegion(recordId) {
        const self = this,
            params = {
                module: app.getModuleName(),
                action: 'Taxes',
                mode: 'deleteRegion',
                parent: 'Settings',
                record: recordId,
            };

        self.deleteRecord(params);
    },
    deleteRecord(params) {
        app.request.post({data: params}).then(function (error, data) {
            if (!error) {
                app.helper.showSuccessNotification({message: data['message']});
            }
        })
    },
    changeStatus(recordId, status) {
        const self = this,
            params = {
                module: app.getModuleName(),
                action: 'Taxes',
                mode: 'status',
                parent: 'Settings',
                record: recordId,
                value: status,
            }

        self.deleteRecord(params);
    },
    registerMethodClick(data) {
        data.on('click', '[name="method"]', function () {
            let method = $(this).val(),
                compoundOnContainer = $('.compoundOnContainer', data),
                deductedContainer = $('.deductedContainer', data);

            compoundOnContainer.addClass('hide');
            deductedContainer.addClass('hide');

            if ('Compound' === method) {
                compoundOnContainer.removeClass('hide');
            } else if ('Deducted' === method) {
                deductedContainer.removeClass('hide');
            }
        })
    },
    registerFormSubmit(data) {
        let self = this,
            form = data.find('form');

        form.vtValidate({
            submitHandler(form) {
                form = jQuery(form);
                let params = form.serializeFormData();

                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        if (data['success']) {
                            app.helper.showSuccessNotification({message: data['message']});
                            app.helper.hideModal();
                            self.createListEntry(params, data['info']);
                        } else {
                            app.helper.showErrorNotification({message: data['message']});
                        }
                    }
                })
            }
        });
    },
    registerUpdateRecords: function () {
        const self = this;

        self.getContainer().on('click', '.taxUpdate', function () {
            let element = $(this),
                recordId = self.getTaxId(element);

            self.updateRecords(recordId);
        });
    },
    updateRecords(recordId) {
        app.helper.showConfirmationBox({message: app.vtranslate('JS_UPDATE_RECORD_TAXES_QUESTION')}).then(function () {
            let params = {
                module: 'Core',
                action: 'Taxes',
                parent: 'Settings',
                mode: 'updateTaxes',
                record: recordId
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification({message: data['message']});
                }
            });
        });
    },
    createListEntry: function (params, info) {
        if ('saveRegion' === params['mode']) {
            this.createRegionListEntry(info);
        } else {
            this.createTaxListEntry(info);
        }
    },
    createTaxListEntry: function (info) {
        const self = this,
            container = self.getContainer();

        if (!info['id']) {
            return;
        }

        let listEntry = container.find('.taxContainer[data-tax_id="' + info['id'] + '"]');

        if (!listEntry.length) {
            listEntry = container.find('.taxContainer.taxClone').clone()
            listEntry.removeClass('taxClone')
            listEntry.removeClass('hide')

            container.find('.taxesTable').append(listEntry);
        }

        listEntry.attr('data-tax_id', info['id']);

        let status = listEntry.find('.taxStatus')

        if (1 === parseInt(info['active'])) {
            status.attr('checked', 'checked');
            status.prop('checked', 'checked');
        } else {
            status.removeAttr('checked', 'checked');
            status.removeProp('checked', 'checked');
        }

        listEntry.find('.taxLabel').text(info['label']);
        listEntry.find('.taxPercentage').text(info['value']);
        listEntry.find('.taxMethod').text(info['method']);
        listEntry.find('.taxUpdate').trigger('click');
    },
    createRegionListEntry: function (info) {
        const self = this,
            container = self.getRegionContainer();

        if (!info['id']) {
            return;
        }

        let listEntry = container.find('.regionContainer[data-region_id="' + info['id'] + '"]');

        if (!listEntry.length) {
            listEntry = container.find('.regionContainer.regionClone').clone()
            listEntry.removeClass('regionClone')
            listEntry.removeClass('hide')
            listEntry.attr('data-region_id', info['id']);

            container.find('.regionsTable').append(listEntry);
        }

        listEntry.find('.regionName').text(info['name'])
    },
    registerEventsForEdit(data) {
        this.registerMethodClick(data);
        this.registerFormSubmit(data);
        this.registerRegionsTax(data);
    },
    registerRegionsTax(data) {
        app.showSelect2ElementView(data.find('.regions:visible'));

        data.on('click', '.addRegionsTax', function () {
            let regionClone = data.find('.regionsContainerClone').clone();
            let regionsKey = data.find('.regionsKey'),
                regionsKeyValue = regionsKey.val();

            regionClone.removeClass('hide')
            regionClone.removeClass('regionsContainerClone')

            let regions = regionClone.find('.regions');

            regions.attr('name', 'regions[' + regionsKeyValue + '][region_id]')

            app.showSelect2ElementView(regions);

            regionClone.find('.regionsPercentage').attr('name', 'regions[' + regionsKeyValue + '][value]')

            data.find('.regionsTable').append(regionClone);

            regionsKey.val(regionsKeyValue + 1)
        });

        data.on('click', '.deleteRegionsTax', function () {
            $(this).parents('tr').remove();
        });
    },
    registerEvents() {
        this.registerDeleteClick();
        this.registerRegionDeleteClick();
        this.registerEditClick();
        this.registerRegionEditClick();
        this.registerStatusClick();
        this.registerUpdateRecords();
    },
    registerStatusClick: function () {
        const self = this;

        self.getContainer().on('click', '.taxStatus', function () {
            let element = $(this),
                recordId = self.getTaxId(element)

            self.changeStatus(recordId, element.is(':checked') ? 1 : 0);
        });
    },
    registerDeleteClick() {
        const self = this;

        self.getContainer().on('click', '.taxDelete', function () {
            let element = $(this),
                recordId = self.getTaxId(element)

            self.deleteTax(recordId);
            element.parents('tr').remove();
        })
    },
    registerRegionDeleteClick() {
        const self = this;

        self.getRegionContainer().on('click', '.regionDelete', function () {
            let element = $(this),
                recordId = self.getRegionId(element)

            self.deleteRegion(recordId);
            element.parents('tr').remove();
        })
    },
    getContainer() {
        return $('#TaxesContainer');
    },
    getRegionContainer() {
        return $('#RegionsContainer');
    },
    getTaxId(element) {
        let taxContainer = element.parents('[data-tax_id]')

        return taxContainer.data('tax_id');
    },
    getRegionId(element) {
        let taxContainer = element.parents('[data-region_id]')

        return taxContainer.data('region_id');
    },
    registerEditClick() {
        const self = this;

        self.getContainer().on('click', '.taxEdit', function () {
            let recordId = self.getTaxId($(this))

            self.editTax(recordId);
        });
    },
    registerRegionEditClick() {
        const self = this;

        self.getRegionContainer().on('click', '.regionEdit', function () {
            let recordId = self.getRegionId($(this))

            self.editRegion(recordId);
        });
    },
})