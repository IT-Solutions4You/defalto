/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var EMAILMaker_ProductBlocks_Js*/
Vtiger.Class('EMAILMaker_ProductBlocks_Js', {
    getInstance: function () {
        return new EMAILMaker_ProductBlocks_Js();
    }
}, {
    saveProductBlock: function (form) {
        let data = form.serializeFormData();

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
        let thisInstance = this,
            form = jQuery('#EditView'),
            //register validation engine
            params = app.validationEngineOptions;

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
        let container = jQuery('#ProductBlocksContainer');

        container.on('click', '.ProductBlockBtn', function (e) {
            let editButton = jQuery(e.currentTarget);
            window.location.href = editButton.data('url');
        });
    },
    registerValidation: function () {
        let editViewForm = this.getForm();

        if (!editViewForm.length) {
            return;
        }

        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {
                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled', true);
                return true;
            }
        });
    },
    registerCKEditor: function () {
        let ckeditorBody = $('#body');

        if (ckeditorBody.length) {
            let ckEditorInstance = new Vtiger_CkEditor_Js();
            ckEditorInstance.loadCkEditor(ckeditorBody, {height: '65vh'});
        }
    },
    registerEvents: function () {
        this.registerCKEditor();
        this.registerActions();
        this.registerValidation();
    }
});

EMAILMaker_ProductBlocks_Js('EMAILMaker_EditProductBlock_Js', {
    InsertIntoTemplate: function (elementType) {
        let insert_value = "",
            selectField = document.getElementById(elementType).value;

        if ('articelvar' === elementType || 'LISTVIEWBLOCK_START' === selectField || 'LISTVIEWBLOCK_END' === selectField) {
            insert_value = '#' + selectField + '#';
        } else if ('relatedmodulefields' === elementType) {
            insert_value = '$R_' + selectField + '$';
        } else if ('productbloctpl' === elementType || 'productbloctpl2' === elementType) {
            insert_value = selectField;
        } else if ('global_lang' === elementType) {
            insert_value = '%G_' + selectField + '%';
        } else if ('custom_lang' === elementType) {
            insert_value = '%' + selectField + '%';
        } else {
            insert_value = '$' + selectField + '$';
        }

        CKEDITOR.instances.body.insertHtml(insert_value);
    }
}, {});