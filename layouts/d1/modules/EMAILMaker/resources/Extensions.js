/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Vtiger_Index_Js("EMAILMaker_Extensions_Js", {}, {
    registerActions: function () {
        let aDeferred = jQuery.Deferred();

        jQuery('#install_ITS4YouStyles_btn').click(function (e) {
            window.location.href = jQuery(e.currentTarget).data('url');
        });
        jQuery('#install_Workflow_btn').click(function (e) {
            let extname = jQuery(e.currentTarget).data('extname');
            let params = {
                'module': 'EMAILMaker',
                'action': 'IndexAjax',
                'mode': 'installExtension',
                'extname': extname
            };

            app.request.post({'data': params}).then(function (error, response) {
                if (!error) {
                    if (response['success']) {
                        jQuery(e.currentTarget).hide();
                        jQuery('#install_' + extname + '_info').html(response['message']);
                        jQuery('#install_' + extname + '_info').removeClass('hide');

                        app.helper.showSuccessNotification({message: response['message']});
                    } else {
                        let isModal = jQuery(response['message']).find('div');

                        if (isModal.length > 0) {
                            app.helper.showModal(response['message']);
                        } else {
                            app.helper.showErrorNotification({message: response['message']})
                        }
                    }
                }
            });
        });

        jQuery('#showUnsubscribeEmailInstructions').click(function (e) {
            jQuery('#showUnsubscribeEmailInstructions').addClass('hide');
            jQuery('#UnsubscribeEmailInstructionsDiv').removeClass('hide');
        });

        jQuery('#hideUnsubscribeEmailInstructions').click(function (e) {
            jQuery('#UnsubscribeEmailInstructionsDiv').addClass('hide');
            jQuery('#showUnsubscribeEmailInstructions').removeClass('hide');
        });
    },
    registerEvents: function () {
        this._super();
        this.registerActions();
    },
    showMessage: function (customParams) {
        let params = {};
        params.animation = "show";
        params.type = 'info';
        params.title = app.vtranslate('JS_MESSAGE');
        if (typeof customParams != 'undefined') {
            let params = jQuery.extend(params, customParams);
        }
        Vtiger_Helper_Js.showPnotify(params);
    }
});
