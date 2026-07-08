/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger_Edit_Js("Accounts_Edit_Js", {}, {

    //This will store the editview form
    editViewForm: false,

    //Address field mapping within module
    addressFieldsMappingInModule: {
        'bill_street': 'ship_street',
        'bill_pobox': 'ship_pobox',
        'bill_city': 'ship_city',
        'bill_state': 'ship_state',
        'bill_code': 'ship_code',
        'bill_country_id': 'ship_country_id',
    },

    // mapping address fields of MemberOf field in the module
    memberOfAddressFieldsMapping: {
        'bill_street': 'bill_street',
        'bill_pobox': 'bill_pobox',
        'bill_city': 'bill_city',
        'bill_state': 'bill_state',
        'bill_code': 'bill_code',
        'bill_country_id': 'bill_country_id',
        'ship_street': 'ship_street',
        'ship_pobox': 'ship_pobox',
        'ship_city': 'ship_city',
        'ship_state': 'ship_state',
        'ship_code': 'ship_code',
        'ship_country_id': 'ship_country_id',
    },
    /**
     * Function to swap array
     * @param Array that need to be swapped
     */
    swapObject: function (objectToSwap) {
        var swappedArray = {};
        var newKey, newValue;
        for (var key in objectToSwap) {
            newKey = objectToSwap[key];
            newValue = key;
            swappedArray[newKey] = newValue;
        }
        return swappedArray;
    },

    /**
     * Function to copy address between fields
     * @param strings which accepts value as either odd or even
     */
    copyAddress: function (swapMode, container) {
        let thisInstance = this,
            addressMapping = this.addressFieldsMappingInModule;

        if (swapMode == "false") {
            for (let key in addressMapping) {
                let fromElement = container.find('[name="' + key + '"]'),
                    toElement = container.find('[name="' + addressMapping[key] + '"]');

                toElement.val(fromElement.val());
                toElement.trigger('change');
            }
        } else if (swapMode) {
            let swappedArray = thisInstance.swapObject(addressMapping);

            for (let key in swappedArray) {
                let fromElement = container.find('[name="' + key + '"]'),
                    toElement = container.find('[name="' + swappedArray[key] + '"]');

                toElement.val(fromElement.val());
                toElement.trigger('change');
            }
        }
    },

    /**
     * Function to register event for copying address between two fileds
     */
    registerEventForCopyingAddress: function (container) {
        let self = this,
            swapMode;

        jQuery('[name="copyAddress"]').on('click', function (e) {
            let element = jQuery(e.currentTarget),
                target = element.data('target');

            if (target === 'billing') {
                swapMode = 'false';
            } else if (target === 'shipping') {
                swapMode = 'true';
            }

            self.copyAddress(swapMode, container);
        })
    },

    /**
     * Function which will copy the address details - without Confirmation
     */
    copyAddressDetails: function (data, container) {
        var thisInstance = this;
        thisInstance.getRecordDetails(data).then(
            function (data) {
                var response = data['result'];
                thisInstance.mapAddressDetails(thisInstance.memberOfAddressFieldsMapping, response['data'], container);
            },
            function (error, err) {

            });
    },

    /**
     * Function which will map the address details of the selected record
     */
    mapAddressDetails: function (addressDetails, result, container) {
        for (var key in addressDetails) {
            // While Quick Creat we don't have address fields, we should  add
            if (container.find('[name="' + key + '"]').length == 0) {
                container.append("<input type='hidden' name='" + key + "'>");
            }
            container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
            container.find('[name="' + key + '"]').trigger('change');
            container.find('[name="' + addressDetails[key] + '"]').val(result[addressDetails[key]]);
            container.find('[name="' + addressDetails[key] + '"]').trigger('change');
        }
    },

    registerAccountNameSuggestionHint: function (container) {
        const self = this,
            accountNameField = container.find('[name="accountname"]').first();

        if (accountNameField.length === 0) {
            return;
        }

        accountNameField.off(
            'input.accountNameSuggestions change.accountNameSuggestions keydown.accountNameSuggestions ' +
            'focus.accountNameSuggestions blur.accountNameSuggestions'
        );
        accountNameField.on('input.accountNameSuggestions', function () {
            self.scheduleAccountNameSuggestionSearch(container, jQuery(this));
        });
        accountNameField.on('focus.accountNameSuggestions', function () {
            self.scheduleAccountNameSuggestionSearch(container, jQuery(this));
        });
        accountNameField.on('blur.accountNameSuggestions', function () {
            const field = jQuery(this);

            setTimeout(function () {
                self.hideAccountNameSuggestionPanel(field);
            }, 0);
        });
        accountNameField.on('change.accountNameSuggestions', function (e) {
            if (e.originalEvent) {
                return;
            }

            self.scheduleAccountNameSuggestionSearch(container, jQuery(this));
        });
        accountNameField.on('keydown.accountNameSuggestions', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                self.hideAccountNameSuggestionPanel(jQuery(this));
            }
        });

        this.registerAccountNameSuggestionCloseEvents();
    },

    scheduleAccountNameSuggestionSearch: function (container, accountNameField) {
        const self = this,
            accountName = String(accountNameField.val() || '').replace(/\s+/g, ' ').trim(),
            existingTimer = accountNameField.data('accountNameSuggestionTimer');

        if (existingTimer) {
            clearTimeout(existingTimer);
        }

        if (accountName.length < 3) {
            self.hideAccountNameSuggestionPanel(accountNameField);
            return;
        }

        accountNameField.data('accountNameSuggestionTimer', setTimeout(function () {
            self.loadAccountNameSuggestions(container, accountNameField, accountName);
        }, 300));
    },

    loadAccountNameSuggestions: function (container, accountNameField, accountName) {
        const self = this,
            requestId = (accountNameField.data('accountNameSuggestionRequestId') || 0) + 1,
            params = {
            module: 'Accounts',
            action: 'NameSuggestions',
            accountname: accountName,
            record: this.getAccountNameSuggestionRecordId(container),
        };

        accountNameField.data('accountNameSuggestionRequestId', requestId);

        app.request.get({data: params}).then(function (err, data) {
            if (accountNameField.data('accountNameSuggestionRequestId') !== requestId) {
                return;
            }

            if (err !== null || !data || !data.html) {
                self.hideAccountNameSuggestionPanel(accountNameField);
                return;
            }

            self.showAccountNameSuggestionPanel(accountNameField, data.html);
        });
    },

    getAccountNameSuggestionRecordId: function (container) {
        const recordField = container.find('[name="record"]').first();

        if (recordField.length > 0) {
            return recordField.val();
        }

        if (container.closest('#QuickCreate').length === 0 && typeof app.getRecordId === 'function') {
            return app.getRecordId();
        }

        return '';
    },

    showAccountNameSuggestionPanel: function (accountNameField, html) {
        const panel = this.getAccountNameSuggestionPanel(accountNameField);

        panel.html(html);

        this.positionAccountNameSuggestionPanel(accountNameField, panel);
        panel.show();
    },

    getAccountNameSuggestionPanel: function (accountNameField) {
        let panelHost = this.getAccountNameSuggestionPanelHost(accountNameField),
            panel = panelHost.children('.accountNameSuggestionPanel').first();

        if (panel.length === 0) {
            panel = jQuery(
                '<div class="accountNameSuggestionPanel list-group shadow-sm overflow-auto position-absolute"></div>'
            );
            panel.hide();
            panel.on('click.accountNameSuggestions mousedown.accountNameSuggestions', function (e) {
                e.stopPropagation();
            });
            panelHost.append(panel);
        }

        return panel;
    },

    getAccountNameSuggestionPanelHost: function (accountNameField) {
        const inputGroup = accountNameField.closest('.input-group'),
            anchor = inputGroup.length > 0 ? inputGroup : accountNameField,
            panelHost = anchor.parent();

        if (panelHost.css('position') === 'static') {
            panelHost.css('position', 'relative');
        }

        return panelHost;
    },

    positionAccountNameSuggestionPanel: function (accountNameField, panel) {
        const inputGroup = accountNameField.closest('.input-group'),
            anchor = inputGroup.length > 0 ? inputGroup : accountNameField,
            position = anchor.position();

        panel.css({
            'left': position.left + 'px',
            'top': (position.top + anchor.outerHeight() + 4) + 'px',
            'width': anchor.outerWidth() + 'px',
        });
    },

    hideAccountNameSuggestionPanel: function (accountNameField) {
        const existingTimer = accountNameField.data('accountNameSuggestionTimer');

        if (existingTimer) {
            clearTimeout(existingTimer);
        }

        accountNameField.data(
            'accountNameSuggestionRequestId',
            (accountNameField.data('accountNameSuggestionRequestId') || 0) + 1
        );
        this.getAccountNameSuggestionPanelHost(accountNameField).children('.accountNameSuggestionPanel').hide();
    },

    registerAccountNameSuggestionCloseEvents: function () {
        jQuery(document).off('click.accountNameSuggestions keydown.accountNameSuggestions');
        jQuery(document).on('click.accountNameSuggestions', function (e) {
            const target = jQuery(e.target);

            if (
                target.closest('.accountNameSuggestionPanel').length === 0
                && target.closest('[name="accountname"]').length === 0
            ) {
                jQuery('.accountNameSuggestionPanel').hide();
            }
        });
        jQuery(document).on('keydown.accountNameSuggestions', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                jQuery('.accountNameSuggestionPanel').hide();
            }

        });
    },

    /**
     * Function which will register basic events which will be used in quick create as well
     *
     */
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerEventForCopyingAddress(container);
        this.registerAccountNameSuggestionHint(container);
    }
});
