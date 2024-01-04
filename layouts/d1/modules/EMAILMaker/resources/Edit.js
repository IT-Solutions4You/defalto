/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Vtiger_Edit_Js("EMAILMaker_Edit_Js", {

    duplicateCheckCache: {},
    advanceFilterInstance: false,
    formElement: false,

    getForm: function () {
        if (this.formElement === false) {
            this.setForm(jQuery('#EditView'));
        }
        return this.formElement;
    },
    setForm: function (element) {
        this.formElement = element;
        return this;
    },
    registerRecordPreSaveEvent: function (form) {
        if (typeof form == 'undefined') {
            form = this.getForm();
        }
        form.on(Vtiger_Edit_Js.recordPreSave, function (e, data) {
            let error = 0;

            return error === 0;
        })
    },
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerButtons();
    },
    registerButtons: function () {
        let thisInstance = this,
            selectElement1 = jQuery('.InsertIntoTemplate');

        selectElement1.on('click', function () {
            let selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType, false);
        });

        let selectElement2 = jQuery('.InsertLIntoTemplate');

        selectElement2.on('click', function () {
            let selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType, true);
        });
    },
    inArray: function (needle, haystack) {
        let length = haystack.length;

        for (let i = 0; i < length; i++) {
            if (typeof haystack[i] == 'object') {
                if (arrayCompare(haystack[i], needle)) return true;
            } else {
                if (haystack[i] == needle) return true;
            }
        }

        return false;
    },
    InsertIntoTemplate: function (element, islabel) {

        let invarray = ['SUBTOTAL', 'TOTALWITHOUTVAT', 'TOTALDISCOUNT', 'TOTALDISCOUNTPERCENT', 'TOTALAFTERDISCOUNT',
                'VAT', 'VATPERCENT', 'VATBLOCK', 'TOTALWITHVAT', 'ADJUSTMENT', 'TOTAL', 'SHTAXTOTAL', 'SHTAXAMOUNT',
                'CURRENCYNAME', 'CURRENCYSYMBOL', 'CURRENCYCODE'],
            selectedTab2 = jQuery('#ContentEditorTabs').find('.active').data('type'),
            selectField = document.getElementById(element).value,
            oEditor = CKEDITOR.instances.body;

        if (islabel) {
            if (element == "modulefields") {
                selectField = 's-' + selectField;
            }

            oEditor.insertHtml('%' + selectField + '%');
        } else {
            let insert_value;

            if (element != 'hmodulefields' && element != 'fmodulefields' && element != 'dateval') {
                if (selectField != '') {
                    if (selectField == 'COMPANY_LOGO')
                        insert_value = jQuery('#div_company_logo').html();
                    else if (selectField == 'ORGANIZATION_STAMP_SIGNATURE')
                        insert_value = jQuery('#div_company_stamp_signature').html();
                    else if (selectField == 'ORGANIZATION_HEADER_SIGNATURE')
                        insert_value = jQuery('#div_company_header_signature').html();
                    else if (selectField == 'vatblock')
                        insert_value = jQuery('#div_vat_block_table').html();
                    else if (selectField == 'chargesblock')
                        insert_value = jQuery('#div_charges_block_table').html();
                    else if (element == "articelvar" || selectField == "LISTVIEWBLOCK_START" || selectField == "LISTVIEWBLOCK_END")
                        insert_value = '#' + selectField + '#';
                    else if (element == "relatedmodulefields")
                        insert_value = '$r-' + selectField + '$';
                    else if (element == "productbloctpl" || element == "productbloctpl2")
                        insert_value = selectField;
                    else if (element == "global_lang")
                        insert_value = '%G_' + selectField + '%';
                    else if (element == "module_lang")
                        insert_value = '%M_' + selectField + '%';
                    else if (element == "custom_lang")
                        insert_value = '%' + selectField + '%';
                    else if (element == "customfunction") {
                        let cft = jQuery("#custom_function_type").val();
                        if (cft == "after")
                            insert_value = '[CUSTOMFUNCTION_AFTER|' + selectField + '|CUSTOMFUNCTION_AFTER]';
                        else
                            insert_value = '[CUSTOMFUNCTION|' + selectField + '|CUSTOMFUNCTION]';

                    } else if (element == "modulefields") {
                        if (this.inArray(selectField, invarray)) {
                            insert_value = '$' + selectField + '$';
                        } else {
                            insert_value = '$s-' + selectField + '$';
                        }
                    } else {
                        insert_value = '$' + selectField + '$';
                    }

                    oEditor.insertHtml(insert_value);
                }
            } else {
                if (selectField != '') {
                    if (element == 'hmodulefields' || element == 'fmodulefields') {
                        oEditor.insertHtml('$' + selectField + '$');
                    } else {
                        oEditor.insertHtml(selectField);
                    }
                }
            }
        }
    },
    registerSelectRecipientModuleOption: function () {
        let thisInstance = this;
        let selectElement = jQuery('[name="r_modulename"]');
        selectElement.on('change', function () {

            let selectedOption = selectElement.find('option:selected');
            let moduleName = selectedOption.val();

            thisInstance.getFields(moduleName, "recipientmodulefields", "");
        });
    },
    registerSelectModuleOption: function () {
        let thisInstance = this;
        let selectElement = jQuery('[name="modulename"]');
        selectElement.on('change', function () {
            if (selected_module != '') {
                question = confirm(app.vtranslate("LBL_CHANGE_MODULE_QUESTION"));
                if (question) {
                    let oEditor = CKEDITOR.instances.body;
                    oEditor.setData("");
                } else {
                    selectElement.val(selected_module);
                    return;
                }
            }

            let selectedOption = selectElement.find('option:selected');
            let moduleName = selectedOption.val();

            thisInstance.getFields(moduleName, "modulefields", "");

            EMAILMaker_EditJs.fill_module_lang_array(moduleName);
            EMAILMaker_EditJs.fill_related_blocks_array(moduleName);
            EMAILMaker_EditJs.fill_module_product_fields_array(moduleName);
        });
    },
    registerSelectRelatedModuleOption: function () {
        const self = this,
            selectElement = jQuery('[name="relatedmodulesorce"]');

        selectElement.on('change', function () {
            let fieldInfo = selectElement.find('option:selected').val().split('|'),
                moduleName = fieldInfo[0],
                fieldName = fieldInfo[1];

            self.getFields(moduleName, 'relatedmodulefields', fieldName);
        });
    },

    getFields: function (moduleName, selectname, fieldName) {
        let thisInstance = this;

        let urlParams = {
            "module": "EMAILMaker",
            "formodule": moduleName,
            "forfieldname": fieldName,
            "action": "IndexAjax",
            "mode": "getModuleFields"
        }

        app.request.post({'data': urlParams}).then(
            function (err, response) {
                thisInstance.updateFields(response, selectname);
            }
        );
    },
    getOptionsGroup: function (label, fields) {
        let optgroup = jQuery('<optgroup/>');
        optgroup.attr('label', label);

        jQuery.each(fields, function (key, field) {
            optgroup.append(jQuery('<option>', {
                value: key,
                text: field
            }));
        });

        return optgroup;
    },
    updateFields: function (response, selectName) {
        let self = this;

        if (response['success']) {
            let ModuleFieldsElement = jQuery('#' + selectName);

            ModuleFieldsElement.empty();

            if ('subject_fields' === selectName) {
                jQuery.each(response['subject_fields'], function (label, fields) {
                    ModuleFieldsElement.append(self.getOptionsGroup(label, fields));
                });
            }

            jQuery.each(response['fields'], function (label, fields) {
                ModuleFieldsElement.append(self.getOptionsGroup(label, fields));
            });

            ModuleFieldsElement.select2('destroy');

            vtUtils.showSelect2ElementView(ModuleFieldsElement);

            if ('modulefields' === selectName) {
                const RelatedModuleSourceElement = jQuery('#relatedmodulesorce');

                RelatedModuleSourceElement.empty();

                jQuery.each(response['related_modules'], function (i, item) {
                    RelatedModuleSourceElement.append(jQuery('<option>', {
                        value: item[3] + '|' + item[0],
                        text: item[2] + ' (' + item[1] + ')',
                    }).data('module', item[3]));
                });

                RelatedModuleSourceElement.select2('destroy');

                vtUtils.showSelect2ElementView(RelatedModuleSourceElement);

                RelatedModuleSourceElement.trigger('change');

                self.updateFields(response, 'subject_fields');
            }
        }
    },
    registerToggleShareList: function () {
        const self = this;

        $('[data-toogle-members]').on('change', function () {
            self.updateShareListVisibility($(this).val());
        });
    },
    updateShareListVisibility: function (value) {
        if ('share' === value) {
            $('.memberListContainer').removeClass('hide').data('rule-required', true);
        } else {
            $('.memberListContainer').addClass('hide').data('rule-required', false);
        }
    },
    registerCSSStyles: function () {
        jQuery('.CodeMirrorContent').each(function (index, Element) {
            let StyleElementId = jQuery(Element).attr('id');
            CodeMirror.runMode(document.getElementById(StyleElementId).value, "css",
                document.getElementById(StyleElementId + "Output"));
        });
    },

    /**
     * Function to Validate and Save Event
     * @returns {undefined}
     */
    registerValidation: function () {
        let editViewForm = this.getForm();
        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {

                let e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                app.event.trigger(e);
                if (e.isDefaultPrevented()) {
                    return false;
                }
                let error = 0;

                if (error > 0) {
                    return false;
                }

                window.onbeforeunload = null;
                editViewForm.find('.saveButton').attr('disabled', true);
                return true;
            }
        });
    },

    registerEvents: function () {
        const editViewForm = this.getForm(),
            statusToProceed = this.proceedRegisterEvents();

        if (!statusToProceed) {
            return;
        }

        this.registerBasicEvents(editViewForm);
        this.registerSelectRecipientModuleOption();
        this.registerSelectModuleOption();
        this.registerSelectRelatedModuleOption();
        this.registerValidation();
        this.registerToggleShareList();
        this.registerCSSStyles();

        if (typeof this.registerLeavePageWithoutSubmit == 'function') {
            this.registerLeavePageWithoutSubmit(editViewForm);
        }
    }
});
if (typeof (EMAILMaker_EditJs) == 'undefined') {
    /*
     * Namespaced javascript class for Import
     */
    EMAILMaker_EditJs = {
        reportsColumnsList: false,
        advanceFilterInstance: false,
        availListObj: false,
        selectedColumnsObj: false,

        clearRelatedModuleFields: function () {
            second = document.getElementById("relatedmodulefields");
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            let box2 = second;
            let optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--) {
                box2.removeChild(optgroups[i]);
            }

            objOption = document.createElement("option");
            objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
            objOption.value = "";
            box2.appendChild(objOption);
        },
        change_relatedmodulesorce: function (first, second_name) {
            second = document.getElementById(second_name);
            optionTest = true;
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            let box = first;
            let number = box.options[box.selectedIndex].value;
            if (!number)
                return;

            let params = {
                module: app.getModuleName(),
                view: 'IndexAjax',
                source_module: number,
                mode: 'getModuleConditions'
            }
            let actionParams = {
                "type": "POST",
                "url": 'index.php',
                "dataType": "html",
                "data": params
            };

            let box2 = second;
            let optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--) {
                box2.removeChild(optgroups[i]);
            }

            let list = all_related_modules[number];
            for (i = 0; i < list.length; i += 2) {
                objOption = document.createElement("option");
                objOption.innerHTML = list[i];
                objOption.value = list[i + 1];
                box2.appendChild(objOption);
            }

            EMAILMaker_EditJs.clearRelatedModuleFields();
        },
        change_relatedmodule: function (first, second_name) {
            second = document.getElementById(second_name);
            optionTest = true;
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            let box = first;
            let number = box.options[box.selectedIndex].value;
            if (!number)
                return;
            let box2 = second;
            let optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--) {
                box2.removeChild(optgroups[i]);
            }

            if (number == "none") {
                objOption = document.createElement("option");
                objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
                objOption.value = "";
                box2.appendChild(objOption);
            } else {
                let tmpArr = number.split('|', 2);
                let moduleName = tmpArr[0];
                number = tmpArr[1];
                let blocks = module_blocks[moduleName];
                for (b = 0; b < blocks.length; b += 2) {
                    let list = related_module_fields[moduleName + '|' + blocks[b + 1]];
                    if (list.length > 0) {
                        optGroup = document.createElement('optgroup');
                        optGroup.label = blocks[b];
                        box2.appendChild(optGroup);
                        for (i = 0; i < list.length; i += 2) {
                            objOption = document.createElement("option");
                            objOption.innerHTML = list[i];
                            let objVal = list[i + 1];
                            let newObjVal = objVal.replace(moduleName.toUpperCase() + '_', number.toUpperCase() + '_');
                            objOption.value = newObjVal;
                            optGroup.appendChild(objOption);
                        }
                    }
                }
            }
        },
        change_acc_info: function (element) {
            jQuery('.au_info_div').css('display', 'none');

            let div_name;

            switch (element.value) {
                case "Assigned":
                    div_name = 'user_info_div';
                    break;
                case "Logged":
                    div_name = 'logged_user_info_div';
                    break;
                case "Modifiedby":
                    div_name = 'modifiedby_user_info_div';
                    break;
                case "Creator":
                    div_name = 'smcreator_user_info_div';
                    break;
                default:
                    div_name = 'user_info_div';
                    break;
            }
            jQuery('#' + div_name).css('display', 'inline');
        },
        ControlNumber: function (elid, final) {
            let control_number = document.getElementById(elid).value;
            let re = [];
            re[1] = new RegExp("^([0-9])");
            re[2] = new RegExp("^[0-9]{1}[.]$");
            re[3] = new RegExp("^[0-9]{1}[.][0-9]{1}$");
            if (control_number.length > 3 || !re[control_number.length].test(control_number) || (final == true && control_number.length == 2)) {
                alert(app.vtranslate("LBL_MARGIN_ERROR"));
                document.getElementById(elid).focus();
                return false;
            } else {
                return true;
            }
        },
        showHideTab3: function (tabname) {
            document.getElementById(tabname + '_tab2').className = 'active';
            if (tabname == 'body') {
                document.getElementById('body_variables').style.display = '';
                document.getElementById('related_block_tpl_row').style.display = '';
                document.getElementById('listview_block_tpl_row').style.display = '';
            } else {
                document.getElementById('body_variables').style.display = 'none';
                document.getElementById('related_block_tpl_row').style.display = 'none';
                document.getElementById('listview_block_tpl_row').style.display = 'none';
            }


            document.getElementById(tabname + '_div2').style.display = 'block';
            box = document.getElementById('modulename')
            let module = box.options[box.selectedIndex].value;

        },
        fill_module_lang_array: function (module, selected) {

            let urlParams = {
                "module": "EMAILMaker",
                "handler": "fill_lang",
                "action": "AjaxRequestHandle",
                "langmod": module
            }

            app.request.post({'data': urlParams}).then(
                function (err, response) {
                    let result = response['success'];

                    if (result == true) {
                        let moduleLangElement = jQuery('#module_lang');

                        moduleLangElement.empty();

                        jQuery.each(response['labels'], function (key, langlabel) {

                            moduleLangElement.append(jQuery('<option>', {
                                value: key,
                                text: langlabel
                            }));
                        })
                    }
                })
        },
        fill_related_blocks_array: function (module, selected) {
            let urlParams = {
                module: 'EMAILMaker',
                handler: 'fill_relblocks',
                action: 'AjaxRequestHandle',
                selmod: module
            }

            app.request.post({'data': urlParams}).then(function (error, data) {
                if (!error && data['success']) {
                    let relatedBlockElement = jQuery('#related_block'),
                        newKey,
                        newOption;

                    relatedBlockElement.empty();

                    jQuery.each(data['relblocks'], function (blockKey, blockName) {
                        newOption = new Option(blockName, blockKey, false, false);
                        newKey = blockKey

                        relatedBlockElement.append(newOption);
                    })

                    if(!relatedBlockElement.val()) {
                        relatedBlockElement.val(newKey);
                    }

                    relatedBlockElement.trigger('change')
                }
            })
        },
        fill_module_product_fields_array: function (module) {
            let ajax_url = 'index.php?module=EMAILMaker&action=AjaxRequestHandle&handler=fill_module_product_fields&productmod=' + module;
            jQuery.ajax(ajax_url).success(function (response) {

                let product_fields = document.getElementById('psfields');
                product_fields.length = 0;
                let map = response.split('|@|');
                let keys = map[0].split('||');
                let values = map[1].split('||');
                for (i = 0; i < values.length; i++) {
                    let item = document.createElement('option');
                    item.text = values[i];
                    item.value = keys[i];
                    try {
                        product_fields.add(item, null);
                    } catch (ex) {
                        product_fields.add(item);
                    }
                }
            }).error(function () {
                alert('fill_module_product_fields_array error');
            });
        },
        refresh_related_blocks_array: function (selected) {
            let module = document.getElementById('modulename').value;
            EMAILMaker_EditJs.fill_related_blocks_array(module, selected);
        },
        InsertRelatedBlock: function () {
            let relblockid = document.getElementById('related_block').value;
            if (relblockid == '')
                return false;
            let oEditor = CKEDITOR.instances.body;
            let ajax_url = 'index.php?module=EMAILMaker&action=AjaxRequestHandle&handler=get_relblock&relblockid=' + relblockid;
            jQuery.ajax(ajax_url).success(function (response) {
                oEditor.insertHtml(response);
            }).error(function () {
            });
        },
        EditRelatedBlock: function () {
            let relblockid = document.getElementById('related_block').value;
            if (relblockid == '') {
                alert(app.vtranslate('LBL_SELECT_RELBLOCK'));
                return false;
            }

            let popup_url = 'index.php?module=EMAILMaker&view=EditRelatedBlock&record=' + relblockid;
            window.open(popup_url, "Editblock", "width=1230,height=700,scrollbars=yes");
        },
        CreateRelatedBlock: function () {
            let email_module = document.getElementById("modulename").value;
            if (email_module == '') {
                alert(app.vtranslate("LBL_MODULE_ERROR"));
                return false;
            }
            let popup_url = 'index.php?module=EMAILMaker&view=EditRelatedBlock&emailmodule=' + email_module;
            window.open(popup_url, "Editblock", "width=1230,height=700,scrollbars=yes");
        },
        DeleteRelatedBlock: function () {
            let relblockid = document.getElementById('related_block').value;
            let result = false;
            if (relblockid == '') {
                alert(app.vtranslate('LBL_SELECT_RELBLOCK'));
                return false;
            } else {
                let message = app.vtranslate('LBL_DELETE_RELBLOCK_CONFIRM') + " " + jQuery("#related_block option:selected").text();

                app.helper.showConfirmationBox({'message': message}).then(function (e) {
                    let params = {
                        "module": "EMAILMaker",
                        "action": "AjaxRequestHandle",
                        "handler": "delete_relblock",
                        "relblockid": relblockid
                    };
                    app.helper.showProgress();

                    app.request.post({'data': params}).then(
                        function (err, response) {
                            app.helper.hideProgress();
                            if (err === null) {
                                EMAILMaker_EditJs.refresh_related_blocks_array();
                            }
                        }
                    );
                });
            }
        },
        insertFieldIntoSubject: function (val) {
            if (val != '') {
                if (val == '##DD.MM.YYYY##' || val == '##DD-MM-YYYY##' || val == '##DD/MM/YYYY##' || val == '##MM-DD-YYYY##' || val == '##MM/DD/YYYY##' || val == '##YYYY-MM-DD##')
                    document.getElementById('subject').value += val;
                else
                    document.getElementById('subject').value += '$s-' + val + '$';
            }
        },
        CustomFormat: function () {
            let selObj;
            selObj = document.getElementById('pdf_format');

            if (selObj.value == 'Custom') {
                document.getElementById('custom_format_table').style.display = 'table';
            } else {
                document.getElementById('custom_format_table').style.display = 'none';
            }
        },
        isLvTmplClicked: function (source) {
            let oTrigger = document.getElementById('isListViewTmpl');
            let oButt = jQuery("#listviewblocktpl_butt");
            let oDlvChbx = document.getElementById('is_default_dv');

            let listViewblockTPLElement = jQuery("#listviewblocktpl");

            listViewblockTPLElement.attr("disabled", !(oTrigger.checked));
            oButt.attr("disabled", !(oTrigger.checked));

            if (source != 'init') {
                oDlvChbx.checked = false;
            }

            oDlvChbx.disabled = oTrigger.checked;
        },
        templateActiveChanged: function (activeElm) {
            let is_defaultElm1 = document.getElementById('is_default_dv');
            let is_defaultElm2 = document.getElementById('is_default_lv');

            if (activeElm.value == '1') {
                is_defaultElm1.disabled = false;
                is_defaultElm2.disabled = false;
            } else {
                is_defaultElm1.checked = false;
                is_defaultElm1.disabled = true;
                is_defaultElm2.checked = false;
                is_defaultElm2.disabled = true;
            }
        },
    }
}
