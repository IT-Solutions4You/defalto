/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger.Class('Settings_LayoutEditor_LinkSettings_Js', {}, {
    init: function () {
        this.addComponent('Vtiger_Index_Js');
    },

    getContainer: function () {
        return jQuery('#linkSettingsEditor');
    },

    getSelectedModuleName: function () {
        return this.getContainer().data('source-module');
    },

    registerModuleChange: function () {
        const self = this,
            root = this.getContainer(),
            select = root.find('[name="sourceModule"]');

        vtUtils.showSelect2ElementView(select);
        select.off('change.linkSettings').on('change.linkSettings', function () {
            const sourceModule = select.val(),
                previousModule = self.getSelectedModuleName(),
                contents = root.find('.linkSettingsContents');

            select.prop('disabled', true);
            contents.attr('aria-busy', 'true').find('.deleteModuleLink').prop('disabled', true);
            app.helper.showProgress();
            app.request.get({data: {
                module: 'LayoutEditor', parent: 'Settings', view: 'LinkSettings',
                sourceModule: sourceModule, contentsOnly: true
            }}).then(function (error, data) {
                select.prop('disabled', false);
                contents.removeAttr('aria-busy');
                app.helper.hideProgress();

                if (error) {
                    select.val(previousModule).trigger('change.select2');
                    contents.find('.deleteModuleLink').prop('disabled', false);
                    app.helper.showErrorNotification({message: error.message || error});
                    return;
                }

                contents.html(data);
                root.data('source-module', sourceModule).attr('data-source-module', sourceModule);
                window.history.replaceState(null, '', 'index.php?module=LayoutEditor&parent=Settings&view=LinkSettings&sourceModule=' + encodeURIComponent(sourceModule));
            });
        });
    },

    registerDeleteEvents: function () {
        const self = this,
            root = this.getContainer();

        root.off('click.deleteModuleLink', '.deleteModuleLink').on('click.deleteModuleLink', '.deleteModuleLink', function (event) {
            const button = jQuery(event.currentTarget),
                row = button.closest('.moduleLinkItem'),
                settings = button.closest('.linkSettings'),
                sourceModule = self.getSelectedModuleName();

            if (button.prop('disabled')) {
                return;
            }

            button.prop('disabled', true);
            app.helper.showConfirmationBox({message: settings.data('delete-confirmation')}).then(function () {
                app.request.post({data: {
                    module: 'LayoutEditor', parent: 'Settings', action: 'DeleteLink',
                    sourceModule: sourceModule, linkId: row.data('link-id')
                }}).then(function (error, data) {
                    button.prop('disabled', false);

                    if (error) {
                        app.helper.showErrorNotification({message: error.message || error});
                        return;
                    }

                    if (data.link) {
                        row.removeAttr('data-link-id').removeData('link-id');
                        row.find('.dropdown').remove();
                        const details = row.find('dl').empty();

                        data.link.details.forEach(function (detail) {
                            const value = jQuery('<dd>');

                            if (detail.icon_class) {
                                value.append(jQuery('<i>', {class: detail.icon_class, 'aria-hidden': 'true'}), jQuery('<span>', {class: 'visually-hidden', text: detail.name}));
                            } else {
                                value.text(detail.value);
                            }

                            details.append(jQuery('<dt>').text(detail.name), value);
                        });
                    } else {
                        self.removeLinkSettingsItem(row);
                    }

                    app.helper.showSuccessNotification({message: data.message});
                });
            }, function () {
                button.prop('disabled', false);
            });
        });
    },

    removeLinkSettingsItem: function (row) {
        const settings = row.closest('.linkSettings'),
            group = row.closest('.linkSettingsGroup');

        row.remove();
        group.find('.linkSettingsEmpty').toggleClass('d-none', group.find('.moduleLinkItem').length > 0);
        settings.find('.linkSettingsAllEmpty').toggleClass('d-none', settings.find('.moduleLinkItem').length > 0);
    },

    registerEvents: function () {
        const settings = new Settings_Vtiger_Index_Js();

        settings.registerBasicSettingsEvents();
        this.registerModuleChange();
        this.registerDeleteEvents();
    }
});