/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class("Settings_Vtiger_CompanyDetails_Js", {}, {

    init: function () {
        this.addComponents();
    },

    addComponents: function () {
        this.addModuleSpecificComponent('Index', app.module, app.getParentModuleName());
    },

    registerUpdateDetailsClickEvent: function () {
        jQuery('#updateCompanyDetails').on('click', function (e) {
            jQuery('#CompanyDetailsContainer').addClass('hide');
            jQuery('#updateCompanyDetailsForm').removeClass('hide');
            jQuery('#updateCompanyDetails').addClass('hide');
        });
    },

    registerSaveCompanyDetailsEvent: function () {
        const thisInstance = this,
            form = jQuery('#updateCompanyDetailsForm'),
            params = {
                submitHandler: function (domForm) {
                    if (!thisInstance.checkValidation()) {
                        return false;
                    }

                    const formData = new FormData(domForm),
                        saveButton = form.find('.saveButton');

                    saveButton.prop('disabled', true);
                    app.helper.showProgress();

                    app.request.post({
                        data: formData,
                        processData: false,
                        contentType: false
                    }).then(function (error, data) {
                        app.helper.hideProgress();

                        if (error) {
                            saveButton.prop('disabled', false);
                            app.helper.showErrorNotification({
                                message: error.message || app.vtranslate('JS_ERROR')
                            });

                            return;
                        }

                        window.location.href = data.reloadUrl;
                    });

                    return false;
                }
            };

        form.vtValidate(params);
    },

    registerCancelClickEvent: function () {
        const form = jQuery('#updateCompanyDetailsForm'),
            logoPreview = form.find('.companyLogoPreview'),
            removalMessage = form.find('.companyLogoRemovalMessage');

        jQuery('.cancelLink').on('click', function () {
            jQuery('#CompanyDetailsContainer').removeClass('hide');
            form.addClass('hide');
            jQuery('#updateCompanyDetails').removeClass('hide');
            form.find('#removeCompanyLogo').val('0');
            form.find('#logoFile').val('');
            logoPreview.removeClass('hide');
            removalMessage.addClass('hide');
        });
    },

    checkValidation: function () {
        const imageObj = jQuery('#logoFile'),
            imageName = imageObj.val();

        if (imageName !== '') {
            const imageParts = imageName.split('.'),
                extensionIndex = imageParts.length - 1;

            if (extensionIndex < 0) {
                app.helper.showErrorNotification({'message': app.vtranslate('LBL_WRONG_IMAGE_TYPE')});
                imageObj.val('');

                return false;
            }

            const imageExtensions = JSON.parse(jQuery('#supportedImageFormats').val()),
                imageExtension = imageParts[extensionIndex].toLowerCase();

            if (imageExtensions.indexOf(imageExtension) !== -1) {
                const size = imageObj[0].files[0].size,
                    maximumSize = Number(jQuery('#maximumLogoFileSize').val());

                if (size <= maximumSize) {
                    return true;
                } else {
                    const maximumSizeInMegabytes = jQuery('#maximumLogoFileSize').data('size-megabytes'),
                        errorMessage = app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS').replace('%s', maximumSizeInMegabytes);

                    app.helper.showErrorNotification({'message': errorMessage});

                    return false;
                }
            } else {
                app.helper.showErrorNotification({'message': app.vtranslate('LBL_WRONG_IMAGE_TYPE')});
                imageObj.val('');

                return false;
            }
        }

        return true;
    },

    registerCompanyLogoEvent: function () {
        const updateCompanyDetailsForm = jQuery('form#updateCompanyDetailsForm'),
            logoFile = updateCompanyDetailsForm.find('#logoFile'),
            removeLogo = updateCompanyDetailsForm.find('#removeCompanyLogo'),
            logoPreview = updateCompanyDetailsForm.find('.companyLogoPreview'),
            removalMessage = updateCompanyDetailsForm.find('.companyLogoRemovalMessage');

        logoFile.on('change', function () {
            if (this.files.length) {
                removeLogo.val('0');
                removalMessage.addClass('hide');
            }
        });

        updateCompanyDetailsForm.find('.removeCompanyLogo').on('click', function () {
            removeLogo.val('1');
            logoFile.val('');
            logoPreview.addClass('hide');
            removalMessage.removeClass('hide');
        });
    },

    registerEvents: function () {
        this.registerUpdateDetailsClickEvent();
        this.registerSaveCompanyDetailsEvent();
        this.registerCancelClickEvent();
        this.registerCompanyLogoEvent();
    }

});
