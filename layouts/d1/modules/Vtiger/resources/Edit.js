/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_Edit_Js */
Vtiger_Index_Js("Vtiger_Edit_Js", {

    file: false,

    editInstance: false,

    recordPresaveEvent: "Pre.Record.Save",

    preReferencePopUpOpenEvent: "Vtiger.Referece.Popup.Pre",

    postReferencePopUpOpenEvent: "Vtiger.Referece.Popup.Post",

    referenceSelectionEvent: "Vtiger.Reference.Selection",

    postReferenceSelectionEvent: "Vtiger.PostReference.Selection",

    postReferenceQuickCreateSave: "Vtiger.PostReference.QuickCreateSave",

    refrenceMultiSelectionEvent: "Vtiger.MultiReference.Selection",

    postReferenceQuickCreateSaveEvent: "Vtiger.PostReferenceQuickCreate.Save",

    popupSelectionEvent: "Vtiger.Reference.Popup.Selection",

    referenceDeSelectionEvent: "Vtiger.Reference.Deselection",

    /**
     * Function to get Instance by name
     * @params moduleName:-- Name of the module to create instance
     */
    getInstanceByModuleName: function (moduleName) {
        if (typeof moduleName == "undefined") {
            moduleName = app.getModuleName();
        }
        var parentModule = app.getParentModuleName();
        if (parentModule == 'Settings') {
            var moduleClassName = parentModule + "_" + moduleName + "_Edit_Js";
            if (typeof window[moduleClassName] == 'undefined') {
                moduleClassName = moduleName + "_Edit_Js";
            }
            var fallbackClassName = parentModule + "_Vtiger_Edit_Js";
            if (typeof window[fallbackClassName] == 'undefined') {
                fallbackClassName = "Vtiger_Edit_Js";
            }
        } else {
            moduleClassName = moduleName + "_Edit_Js";
            fallbackClassName = "Vtiger_Edit_Js";
        }
        if (typeof window[moduleClassName] != 'undefined') {
            var instance = new window[moduleClassName]();
        } else {
            var instance = new window[fallbackClassName]();
        }
        return instance;
    },

    getInstance: function () {
        if (Vtiger_Edit_Js.editInstance == false) {
            var instance = Vtiger_Edit_Js.getInstanceByModuleName();
            Vtiger_Edit_Js.editInstance = instance;
            return instance;
        }
        return Vtiger_Edit_Js.editInstance;
    }
}, {

    editViewContainer: false,
    formValidatorInstance: false,

    getEditViewContainer: function () {
        if (this.editViewContainer === false) {
            this.editViewContainer = jQuery('.editViewPageDiv');
        }
        return this.editViewContainer;
    },
    setEditViewContainer: function (container) {
        this.editViewContainer = container;
    },

    formElement: false,

    getForm: function () {
        if (this.formElement === false) {
            this.formElement = jQuery('#EditView');
        }
        return this.formElement;
    },
    _moduleName: false,

    getModuleName: function () {
        if (this._moduleName != false) {
            return this._moduleName;
        }
        return app.module();
    },

    setModuleName: function (module) {
        this._moduleName = module;
        return this;
    },

    /**
     * Function which will give you all details of the selected record
     * @params - an Array of values like {'record' : recordId, 'source_module' : searchModule, 'selectedName' : selectedRecordName}
     */
    getRecordDetails: function (params) {
        var aDeferred = jQuery.Deferred();
        var url = "index.php?module=" + app.getModuleName() + "&action=GetData&record=" + params['record'] + "&source_module=" + params['source_module'];
        app.request.get({'url': url}).then(
            function (error, data) {
                if (error == null) {
                    aDeferred.resolve(data);
                } else {
                    //aDeferred.reject(data['message']);
                }
            },
            function (error) {
                aDeferred.reject();
            }
        )
        return aDeferred.promise();
    },

    /**
     * Function to Validate and Save Event
     * @returns {undefined}
     */
    registerValidation: function () {
        var editViewForm = this.getForm();
        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {
                var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                app.event.trigger(e);
                if (e.isDefaultPrevented()) {
                    return false;
                }
                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled', true);
                return true;
            }
        });
    },

    /**
     * Function which will register event to prevent form submission on pressing on enter
     * @params - container <jQuery> - element in which auto complete fields needs to be searched
     */
    registerPreventingEnterSubmitEvent: function (container) {
        container.on('keypress', function (e) {
            //Stop the submit when enter is pressed in the form
            var currentElement = jQuery(e.target);
            if (e.which == 13 && (!currentElement.is('textarea'))) {
                e.preventDefault();
            }
        });
    },

    /**
     * Function to register event for setting up picklistdependency
     * for a module if exist on change of picklist value
     */
    registerEventForPicklistDependencySetup: function (container) {
        var picklistDependcyElemnt = jQuery('[name="picklistDependency"]', container);
        if (picklistDependcyElemnt.length <= 0) {
            return;
        }
        var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

        var sourcePicklists = Object.keys(picklistDependencyMapping);
        if (sourcePicklists.length <= 0) {
            return;
        }

        var sourcePickListNames = "";
        for (var i = 0; i < sourcePicklists.length; i++) {
            if (i != sourcePicklists.length - 1)
                sourcePickListNames += '[name="' + sourcePicklists[i] + '"],';
            else
                sourcePickListNames += '[name="' + sourcePicklists[i] + '"]';
        }
        var sourcePickListElements = container.find(sourcePickListNames);

        sourcePickListElements.on('change', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var sourcePicklistname = currentElement.attr('name');
            var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
            var selectedValue = currentElement.val();
            var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
            var picklistmap = configuredDependencyObject["__DEFAULT__"];

            if (typeof targetObjectForSelectedSourceValue == 'undefined') {
                targetObjectForSelectedSourceValue = picklistmap;
            }

            jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
                var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
                if (typeof targetPickListMap == "undefined") {
                    targetPickListMap = targetPickListValues;
                }
                var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
                if (targetPickList.length <= 0) {
                    return;
                }

                var listOfAvailableOptions = targetPickList.data('availableOptions');
                if (typeof listOfAvailableOptions == "undefined") {
                    listOfAvailableOptions = jQuery('option', targetPickList);
                    targetPickList.data('available-options', listOfAvailableOptions);
                }

                var targetOptions = new jQuery();
                var optionSelector = [];
                optionSelector.push('');
                for (var i = 0; i < targetPickListMap.length; i++) {
                    optionSelector.push(targetPickListMap[i]);
                }
                jQuery.each(listOfAvailableOptions, function (i, e) {
                    var picklistValue = jQuery(e).val();
                    if (jQuery.inArray(picklistValue, optionSelector) != -1) {
                        targetOptions = targetOptions.add(jQuery(e));
                    }
                })
                var targetPickListSelectedValue = '';
                var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
                if (targetPickListMap.length == 1) {
                    var targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present.
                }
                if ((targetPickListName == 'group_id' || targetPickListName == 'assigned_user_id') && jQuery("[name=" + sourcePicklistname + "]").val() == '') {
                    return false;
                }
                targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");

            })
        });

        //To Trigger the change on load
        sourcePickListElements.trigger('change');
    },

    registerImageChangeEvent: function () {
        var formElement = this.getForm();
        formElement.find('input[name="imagename[]"]').on('change', function () {
            var deleteImageElement = jQuery(this).closest('td.fieldValue').find('.imageDelete');
            if (deleteImageElement.length) deleteImageElement.trigger('click');
        });
    },

    /**
     * Function to register event for image delete
     */
    registerEventForImageDelete: function () {
        let formElement = this.getForm();

        formElement.find('.imageDelete').on('click', function (e) {
            let element = jQuery(e.currentTarget),
                imageContainer = element.closest('.existingImageContainer'),
                imageId = imageContainer.find('img').data('imageId'),
                imageUploadElement = element.closest('.fieldValue').find('[name="imagename[]"]');

            imageContainer.remove();

            if (formElement.find('[name=imageid]').length !== 0) {
                let imageIdValue = JSON.parse(formElement.find('[name=imageid]').val());
                imageIdValue.push(imageId);
                formElement.find('[name=imageid]').val(JSON.stringify(imageIdValue));
            } else {
                let imageIdJson = [];
                imageIdJson.push(imageId);
                formElement.append('<input type="hidden" name="imgDeleted" value="true" />');
                formElement.append('<input type="hidden" name="imageid" value="' + JSON.stringify(imageIdJson) + '" />');
            }

            if (formElement.find('.imageDelete').length === 0 && imageUploadElement.attr('data-rule-required') == 'true') {
                imageUploadElement.removeClass('ignore-validation')
            }
        });
    },

    registerFileElementChangeEvent: function (container) {
        var thisInstance = this;
        container.on('change', 'input[name="imagename[]"],input[name="sentdocument"]', function (e) {
            if (e.target.type == "text") return false;
            var moduleName = jQuery('[name="module"]').val();
            if (moduleName == "Products") return false;
            Vtiger_Edit_Js.file = e.target.files[0];
            var element = container.find('[name="imagename[]"],input[name="sentdocument"]');
            //ignore all other types than file
            if (element.attr('type') != 'file') {
                return;
            }
            var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
            var fileSize = e.target.files[0].size;
            var fileName = e.target.files[0].name;
            var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
            if (fileSize > maxFileSize) {
                alert(app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE'));
                element.val('');
                uploadFileSizeHolder.text('');
            } else {
                if (container.length > 1) {
                    jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width', '80px');
                    jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width', '80px');
                } else {
                    container.find('input[name="filename"]').css('width', '80px');
                }
                uploadFileSizeHolder.text(fileName + ' ' + thisInstance.convertFileSizeInToDisplayFormat(fileSize));
            }

            jQuery(e.currentTarget).addClass('ignore-validation');
        });
    },

    /**
     * Function to register Basic Events
     * @returns {undefined}
     */
    registerBasicEvents: function (form) {
        app.event.on('post.editView.load', function (event, container) {
        });
        this.registerEventForPicklistDependencySetup(form);
        this.registerFileElementChangeEvent(form);
        this.registerAutoCompleteFields(form);
        this.registerClearReferenceSelectionEvent(form);
        this.registerReferenceCreate(form);
        this.referenceModulePopupRegisterEvent(form);
        this.registerPostReferenceEvent(this.getEditViewContainer());
        this.registerMultiReferenceFields(form);
        this.registerCKEditor(form);
        vtUtils.registerNumberFormating(form);
    },
    /**
     * Registers the search, popup selection and value sync of multi reference fields (uitype 34).
     * @param {jQuery} form
     */
    registerMultiReferenceFields: function (form) {
        const self = this;

        form.find('.js-multireference-field').each(function (index, element) {
            const wrapper = jQuery(element),
                sourceField = wrapper.find('input.sourceField'),
                selectElement = wrapper.find('.js-multireference-select');

            if (selectElement.data('select2')) {
                return;
            }

            selectElement.select2(self.getMultiReferenceSelectConfig(form, wrapper, selectElement));
            selectElement.on('change', function () {
                self.updateMultiReferenceValue(sourceField, selectElement);
            });
            // The popup sends the chosen records only in this event ({id: {name}}); its multi selection event is empty
            sourceField.on(Vtiger_Edit_Js.postReferenceSelectionEvent, function (event, result) {
                self.addMultiReferenceOptions(selectElement, self.getMultiReferencePopupRecords(result.data));
            });
        });
    },

    /**
     * Returns the select2 options of a multi reference field, searching in the module chosen next to the field.
     * @param {jQuery} form
     * @param {jQuery} wrapper
     * @param {jQuery} selectElement
     * @returns {Object}
     */
    getMultiReferenceSelectConfig: function (form, wrapper, selectElement) {
        const self = this;

        return {
            multiple: true,
            theme: 'bootstrap-5',
            width: '100%',
            minimumInputLength: 2,
            maximumSelectionLength: parseInt(selectElement.data('maximumRecords')) || 0,
            language: selectElement.data('maximumRecordsMessage') ? {
                maximumSelected: function () {
                    return selectElement.data('maximumRecordsMessage');
                }
            } : undefined,
            placeholder: selectElement.data('placeholder'),
            ajax: {
                delay: 250,
                transport: function (params, success, failure) {
                    const searchParams = self.getReferenceSearchParams(wrapper.find('input[name="popupReferenceModule"]'));

                    searchParams.module = form.find('[name="module"]').val() || app.getModuleName();
                    searchParams.search_value = params.data.term;
                    self.searchModuleNames(searchParams).then(success, failure);
                },
                processResults: function (data) {
                    return {
                        results: jQuery.map(data || [], function (item) {
                            return {id: item.id, text: item.label};
                        })
                    };
                }
            }
        };
    },

    /**
     * Converts the popup selection ({id: {name}} or a single {id, name}) into a list of records.
     * @param {Object} data
     * @returns {Array} [{id, name}]
     */
    getMultiReferencePopupRecords: function (data) {
        if (!data) {
            return [];
        }

        if (typeof data.id !== 'undefined') {
            return [{id: data.id, name: data.name || data.label}];
        }

        return jQuery.map(data, function (record, recordId) {
            return {id: recordId, name: record.name};
        });
    },

    /**
     * Adds records chosen in the popup to a multi reference field.
     * @param {jQuery} selectElement
     * @param {Array} records [{id, name}]
     */
    addMultiReferenceOptions: function (selectElement, records) {
        const maximumRecords = parseInt(selectElement.data('maximumRecords')) || 0;
        let isLimitReached = false;

        jQuery.each(records || [], function (index, record) {
            if (selectElement.find('option[value="' + record.id + '"]').length) {
                return;
            }

            if (maximumRecords && selectElement.find('option:selected').length >= maximumRecords) {
                isLimitReached = true;

                return;
            }

            selectElement.append(new Option(record.name, record.id, true, true));
        });

        selectElement.trigger('change');

        if (isLimitReached && selectElement.data('maximumRecordsMessage')) {
            app.helper.showErrorNotification({message: selectElement.data('maximumRecordsMessage')});
        }
    },

    /**
     * Writes the selected record ids into the hidden field value (12;15).
     * @param {jQuery} sourceField
     * @param {jQuery} selectElement
     */
    updateMultiReferenceValue: function (sourceField, selectElement) {
        const recordIds = jQuery.grep(selectElement.val() || [], function (recordId) {
            return jQuery.isNumeric(recordId);
        });

        sourceField.val(recordIds.join(';')).trigger('change');
    },

    registerCKEditor: function (form) {
        let ckeditorInstance = new Vtiger_CkEditor_Js();

        form.find('.ckeditor').each(function (index, element) {
            ckeditorInstance.loadCkEditor($(element), {height: '25em'})
        });
    },
    proceedRegisterEvents: function () {
        if (jQuery('.recordEditView').length > 0) {
            return true;
        } else {
            return false;
        }
    },

    registerPageLeaveEvents: function () {
        app.helper.registerLeavePageWithoutSubmit(this.getForm());
        app.helper.registerModalDismissWithoutSubmit(this.getForm());
    },

    registerEvents: function (callParent) {
        //donot call parent if registering Events from overlay.
        if (callParent != false) {
            this._super();
        }
        var editViewContainer = this.getEditViewContainer();
        this.registerPreventingEnterSubmitEvent(editViewContainer);
        this.registerBasicEvents(this.getForm());
        this.registerEventForImageDelete();
        this.registerImageChangeEvent();
        this.registerValidation();
        app.event.trigger('post.editView.load', editViewContainer);
        this.registerPageLeaveEvents();
    },
    /**
     * Function to get popup params
     */
    getPopUpParams: function (container) {
        let self = this,
            params = self._super(container),
            referenceModule = jQuery('input[name=popupReferenceModule]', container).val(),
            form = container.parents('form');

        if ('Contacts' === referenceModule) {
            let parentIdElement = self.getAccountField(form);

            if (parentIdElement.length > 0 && parentIdElement.val() > 0) {
                const closestContainer = parentIdElement.closest('.fieldValue');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            }
        }

        return params;
    },
    getAccountField(form) {
        let moduleName = form.find('[name=module]').val();

        switch (moduleName) {
            case 'Potentials':
                return form.find('[name=related_to]');
            case 'Assets':
                return form.find('[name=account]');
            case 'HelpDesk':
                return form.find('[name=parent_id]');
        }

        return form.find('[name=account_id]');
    },
});
