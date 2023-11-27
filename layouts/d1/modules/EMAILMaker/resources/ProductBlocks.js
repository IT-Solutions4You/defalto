/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger.Class('EMAILMaker_ProductBlocks_Js', {
    getInstance: function () {
        return new EMAILMaker_ProductBlocks_Js();
    }
}, {
    saveProductBlock: function (form) {
        var data = form.serializeFormData();
        if (typeof data == 'undefined') {
            data = {};
        }
        data.module = app.getModuleName();
        data.action = 'IndexAjax';
        data.mode = 'SaveProductBlock';

    },
    formElement: false,

    getForm: function () {
        if (this.formElement === false) {
            this.formElement = jQuery('#EditView');
        }
        return this.formElement;
    },
    registerEditViewEvents: function () {
        var thisInstance = this;
        var form = jQuery('#EditView');

        //register validation engine
        var params = app.validationEngineOptions;
        params.onValidationComplete = function (form, valid) {

            if (valid) {
                return valid;
            }
        }
        form.validationEngine(params);
        form.submit(function (e) {
        })
    },
    registerActions: function () {
        var thisInstance = this;
        var container = jQuery('#ProductBlocksContainer');
        container.on('click', '.ProductBlockBtn', function (e) {
            var editButton = jQuery(e.currentTarget);
            window.location.href = editButton.data('url');
        });
    },
    registerValidation: function () {
        var editViewForm = this.getForm();
        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {
                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled', true);
                return true;
            }
        });
    },
    registerEvents: function () {
        this.registerActions();
        this.registerValidation();
    }
});