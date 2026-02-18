/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Installer_PremiumModal_Js */
jQuery.Class('Installer_PremiumModal_Js', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new Installer_PremiumModal_Js();
        }

        return this.instance;
    }
}, {
    premiumModalHtml: null,

    getModuleName: function () {
        if (typeof app !== 'undefined' && typeof app.getModuleName === 'function') {
            return app.getModuleName();
        }

        return '';
    },

    getAlertFlagId: function () {
        const moduleName = this.getModuleName();

        return moduleName ? ('show' + moduleName + 'LicenseAlert') : 'showProformaLicenseAlert';
    },

    getShownFlagKey: function () {
        const moduleName = this.getModuleName();

        return moduleName ? (moduleName + 'LicenseAlertShown') : 'proformaLicenseAlertShown';
    },

    registerEvents: function () {
        this.tryShowLicenseAlert(0);
    },

    fetchModalHtml: function (cb) {
        if (this.premiumModalHtml) {
            cb(this.premiumModalHtml);

            return;
        }

        app.request.post({
            url: 'index.php?module=Installer&for_module=' + this.getModuleName() + '&view=PremiumModal'
        }).then(function (error, data) {
            if (error) {
                return;
            }

            if (data) {
                this.premiumModalHtml = data;
                cb(data);
            }
        }.bind(this));
    },

    attachPremiumModalHandlers: function (container) {
        if (!container || !container.length) {
            return;
        }

        const $otherModals = jQuery('.modal.show').not(container);

        if ($otherModals.length) {
            const otherStates = [];
            $otherModals.each(function () {
                const $modal = jQuery(this);
                otherStates.push({
                    id: this.id,
                    zIndex: $modal.css('z-index')
                });
                $modal.addClass('df-modal-underlay').css('z-index', 1040);
            });
            container.data('dfUnderlyingModals', otherStates);
            container.off('hidden.bs.modal.dfPremiumReopen').one('hidden.bs.modal.dfPremiumReopen', function () {
                const stored = container.data('dfUnderlyingModals') || [];
                container.removeData('dfUnderlyingModals');
                stored.forEach(function (state) {

                    if (!state || !state.id) {
                        return;
                    }

                    const $modal = jQuery('#' + state.id);

                    if ($modal.length) {
                        $modal.css('z-index', state.zIndex || '').removeClass('df-modal-underlay');
                    }
                });
            });
            setTimeout(function () {
                const $backdrops = jQuery('.modal-backdrop');
                $backdrops.last().css('z-index', 1050);
                container.css('z-index', 1060);
            }, 0);
        }

        container.attr('data-bs-backdrop', 'static');
        container.attr('data-bs-keyboard', 'false');
        const instance = container.data('bs.modal');

        if (instance && instance._config) {
            instance._config.backdrop = 'static';
            instance._config.keyboard = false;
        }

        container.data('dfAllowClose', false);

        if (!container.data('dfPremiumHandlersBound')) {
            container.data('dfPremiumHandlersBound', true);
            const element = container.get(0);

            if (element) {
                element.addEventListener('hide.bs.modal', function (e) {

                    if (!container.data('dfAllowClose')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        return false;
                    }

                    container.data('dfAllowClose', false);
                });
            }
        }

        container.off('mousedown.dfPremium').on('mousedown.dfPremium', '[data-bs-dismiss="modal"], [data-dismiss="modal"], [data-df-continue]', function () {
            container.data('dfAllowClose', true);
        });
        container.off('click.dfPremiumBuy').on('click.dfPremiumBuy', '[data-df-buy]', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            const href = jQuery(this).attr('href');

            if (href) {
                const win = window.open(href, '_blank');

                if (win) {
                    try {
                        win.opener = null;
                    } catch (err) {
                        // no-op
                    }
                }
            }

            container.data('dfAllowClose', true);
            container.modal('hide');

            return false;
        });
        container.off('click.dfPremium').on('click.dfPremium', '[data-bs-dismiss="modal"], [data-dismiss="modal"], [data-df-continue]', function () {
            container.data('dfAllowClose', true);
        });
    },

    openModal: function () {
        this.fetchModalHtml(function (html) {
            const container = app.helper.showModal(html, {
                modalName: 'dfPremiumModal',
                backdrop: 'static',
                keyboard: false
            });

            this.attachPremiumModalHandlers(container);
        }.bind(this));
    },

    registerGlobalModal: function () {
        const self = this;
        window.DefaltoPremiumModal = {
            open: function () {
                self.openModal();
            }
        };
    },

    showLicenseAlert: function () {
        const $alertFlag = jQuery('#' + this.getAlertFlagId());

        if ($alertFlag.length && parseInt($alertFlag.val()) !== 1) {
            return;
        }

        const shownFlag = this.getShownFlagKey();

        if (window[shownFlag]) {
            return;
        }

        window[shownFlag] = true;
        this.openModal();
    },

    tryShowLicenseAlert: function (attempt) {
        const shownFlag = this.getShownFlagKey();

        if (window[shownFlag]) {
            return;
        }

        if (typeof app !== 'undefined' && app.request && typeof app.request.post === 'function' && app.helper && typeof app.helper.showModal === 'function') {
            this.registerGlobalModal();
            this.showLicenseAlert();
            return;
        }

        if (attempt < 20) {
            setTimeout(function () {
                this.tryShowLicenseAlert(attempt + 1);
            }.bind(this), 100);
        }
    }
});

$(document).ready(function () {
    Installer_PremiumModal_Js.getInstance().registerEvents();
});