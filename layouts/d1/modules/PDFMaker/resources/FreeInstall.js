/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (typeof(PDFMaker_FreeInstall_Js) == 'undefined') {

    var PDFMaker_FreeInstall_Js = {

        initialize: function() {
        },


        registerActions : function() {

            var thisInstance = this;
            var container = jQuery('#LicenseContainer');
/*
            jQuery('#activate_license_btn').click(function(e) {
                thisInstance.editLicense('activate');
            });

            jQuery('#reactivate_license_btn').click(function(e) {
                thisInstance.editLicense('reactivate');
            });

            jQuery('#deactivate_license_btn').click(function(e) {
                thisInstance.deactivateLicense();
            });
*/
        },

        registerEvents: function() {
            this.registerActions();
        },

        registerInstallEvents: function() {
            var thisInstance = this;

            this.registerInstallActions();
        },

        registerInstallActions : function() {

            var thisInstance = this;

            jQuery('#start_button').click(function(e) {
                jQuery('#step1').hide();
                jQuery('#step2').show();

                jQuery('#steplabel1').removeClass("active");
                jQuery('#steplabel2').addClass("active");
            });

            jQuery('#download_button').click(function(e) {
                thisInstance.downloadMPDF();
            });

            jQuery('#next_button').click(function(e) {
                window.location.href = "index.php?module=PDFMaker&view=List";
            });

        },

        downloadMPDF : function() {

            app.helper.showProgress();

            var params = {
                module : 'PDFMaker',
                action : 'IndexAjax',
                mode : 'downloadMPDF'
            }
            app.request.post({'data' : params}).then(function(err,response) {
                app.helper.hideProgress();

                var result = response['success'];

                if(result == true) {

                    jQuery('#step2').hide();
                    jQuery('#step3').show();

                    jQuery('#steplabel2').removeClass("active");
                    jQuery('#steplabel3').addClass("active");

                } else {
                    alert(response['message']);
                    var params = {
                        text: app.vtranslate(response['message'])
                    };
                    Vtiger_Helper_Js.showPnotify(params);
                }
            });
        },

        showMessage : function(customParams){
            var params = {};
            params.animation = "show";
            params.type = 'info';
            params.title = app.vtranslate('JS_MESSAGE');

            if(typeof customParams != 'undefined') {
                var params = jQuery.extend(params,customParams);
            }
            Vtiger_Helper_Js.showPnotify(params);
        }
    }

}

jQuery(document).ready(function() {
    PDFMaker_FreeInstall_Js.registerInstallEvents();
});