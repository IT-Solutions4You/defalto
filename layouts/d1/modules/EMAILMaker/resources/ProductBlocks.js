/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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