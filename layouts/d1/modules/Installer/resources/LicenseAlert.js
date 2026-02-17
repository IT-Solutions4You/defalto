/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

(function () {
    const moduleName = (typeof app !== 'undefined' && typeof app.getModuleName === 'function') ? app.getModuleName() : '';
    const alertFlagId = moduleName ? 'show' + moduleName + 'LicenseAlert' : 'showProformaLicenseAlert';
    const shownFlag = moduleName ? (moduleName + 'LicenseAlertShown') : 'proformaLicenseAlertShown';

    if (window[shownFlag]) {
        return;
    }

    let premiumModalHtml = null;

    const fetchModalHtml = function (cb) {
        if (premiumModalHtml) {
            cb(premiumModalHtml);
            return;
        }

        app.request.post({
            url: 'index.php?module=Installer&for_module=' + app.getModuleName() + '&view=PremiumModal'
        }).then(function (error, data) {
            if (error) {
                return;
            }

            if (data) {
                premiumModalHtml = data;
                cb(data);
            }
        });
    };

    const attachPremiumModalHandlers = function (container) {
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
    };

    const openModal = function () {
        fetchModalHtml(function (html) {
            const container = app.helper.showModal(html, {
                modalName: 'dfPremiumModal',
                backdrop: 'static',
                keyboard: false
            });

            attachPremiumModalHandlers(container);
        });
    };

    window.DefaltoPremiumModal = {
        open: openModal
    };

    const showLicenseAlert = function () {
        const $alertFlag = jQuery('#' + alertFlagId);

        if ($alertFlag.length && parseInt($alertFlag.val()) !== 1) {
            return;
        }

        if (window[shownFlag]) {
            return;
        }

        window[shownFlag] = true;
        openModal();
    };

    const tryShowLicenseAlert = function (attempt) {
        if (window[shownFlag]) {
            return;
        }

        if (typeof app !== 'undefined' && app.request && typeof app.request.post === 'function' && app.helper && typeof app.helper.showModal === 'function') {
            showLicenseAlert();
            return;
        }

        if (attempt < 20) {
            setTimeout(function () {
                tryShowLicenseAlert(attempt + 1);
            }, 100);
        }
    };

    jQuery(window).on('load', function () {
        tryShowLicenseAlert(0);
    });
})();