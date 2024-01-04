/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

if (typeof (EMAILMaker_RelatedBlockJs) == 'undefined') {
    /*
     * Namespaced javascript class for Import
     */
    EMAILMaker_RelatedBlockJs = {
        formElement: false,
        container: false,
        relatedblockColumnsList: false,
        stepContainer: false,
        advanceFilterInstance: false,
        selectedFields: false,

        changeSteps: function () {
            var actual_step = document.getElementById('step').value * 1;
            var next_step = actual_step + 1;

            if (next_step == "2") {
                EMAILMaker_RelatedBlockJs.changeSecOptions();
            } else if (next_step == "6") {
                var editViewForm = this.getForm();

                var blocknameElement = jQuery('input[name="blockname"]');
                var control = blocknameElement.val();

                if (control == "") {
                    vtUtils.showValidationMessage(blocknameElement, app.vtranslate('JS_REQUIRED_FIELD'));
                    return false;
                } else {
                    vtUtils.hideValidationMessage(blocknameElement);
                }
                editViewForm.submit();
            } else {
                if (next_step == "3") {
                    if (!this.isFormValidate()) return false;

                    var selectedFields = this.getSelectedColumns();
                    this.getSelectedFields().val(JSON.stringify(selectedFields));
                    this.createRelatedBlockTable();
                }

                jQuery("#steplabel" + actual_step).removeClass('active');
                jQuery("#steplabel" + next_step).addClass('active');
                jQuery("#step" + actual_step).addClass('hide');
                jQuery("#step" + next_step).removeClass('hide');
            }

            document.getElementById('back_rep').disabled = false;
            document.getElementById('step').value = next_step;
        },
        changeStepsback: function (mode) {
            actual_step = document.getElementById('step').value * 1;
            last_step = actual_step - 1;

            jQuery("#steplabel" + actual_step).removeClass('active');
            jQuery("#steplabel" + last_step).addClass('active');

            jQuery("#step" + actual_step).addClass('hide');
            jQuery("#step" + last_step).removeClass('hide');

            if ((last_step == 1 && mode == "create") || (last_step == 3 && mode == "edit"))
                document.getElementById('back_rep').disabled = true;

            document.getElementById('step').value = last_step;
        },
        changeSecOptions: function () {

            var primodule = document.NewBlock.primarymodule.value;
            var secmodule = EMAILMaker_RelatedBlockJs.getCheckedValue(document.NewBlock.secondarymodule);

            var saved_secmodule = jQuery("#saved_secmodule").val();

            if (saved_secmodule != secmodule) {
                jQuery("#saved_secmodule").val(secmodule);

                var thisElement = this;

                var params = {
                    'module': 'EMAILMaker',
                    'action': 'IndexAjax',
                    'mode': 'GetRelatedBlockColumns',
                    'type': 'columns',
                    'secmodule': secmodule,
                    'primodule': primodule
                };

                app.helper.showProgress();

                var ModuleFieldsElements = jQuery('.relatedblockColumns');

                app.request.post({'data': params}).then(
                    function (err, response) {
                        app.helper.hideProgress();

                        ModuleFieldsElements.each(function (index, domElement) {
                            ModuleFieldsElement = jQuery(domElement);
                            jQuery.each(response['fields'], function (i, fields) {

                                var optgroup = jQuery('<optgroup/>');
                                optgroup.attr('label', i);

                                jQuery.each(fields, function (key, field) {

                                    optgroup.append(jQuery('<option>', {
                                        value: key,
                                        text: field
                                    }));
                                });

                                ModuleFieldsElement.append(optgroup);
                            });
                        });

                        var relatedblockColumnsListElement = jQuery('#relatedblockColumnsList');
                        relatedblockColumnsListElement.select2("destroy");
                        relatedblockColumnsListElement.select2();

                        thisElement.initialize();

                        jQuery("#steplabel1").removeClass('active');
                        jQuery("#steplabel2").addClass('active');

                        jQuery("#step1").addClass('hide');
                        jQuery("#step2").removeClass('hide');
                    }
                );

                var params2 = {
                    'module': 'EMAILMaker',
                    'view': 'GetRelatedBlockFilters',
                    'secmodule': secmodule,
                    'primodule': primodule
                };

                app.request.post({'data': params2}).then(
                    function (err, response) {
                        jQuery('#step3').html(response);
                        EMAILMaker_RelatedBlockJs.registerEvents2();
                    }
                );
            } else {
                this.initialize();
                jQuery("#steplabel1").removeClass('active');
                jQuery("#steplabel2").addClass('active');

                jQuery("#step1").addClass('hide');
                jQuery("#step2").removeClass('hide');
            }
        },
        getCheckedValue: function (radioObj) {
            if (!radioObj)
                return "";
            var radioLength = radioObj.length;
            if (radioLength == undefined)
                if (radioObj.checked)
                    return radioObj.value;
                else
                    return "";
            for (var i = 0; i < radioLength; i++) {
                if (radioObj[i].checked) {
                    return radioObj[i].value;
                }
            }
            return "";
        },
        registerSelect2ElementForRelatedBlockColumns: function () {
            var selectElement = this.getRelatedBlockColumnsList();
            selectElement.select2();
        },
        getRelatedBlockColumnsList: function () {
            if (this.relatedblockColumnsList == false) {
                this.relatedblockColumnsList = jQuery('#relatedblockColumnsList');
            }
            return this.relatedblockColumnsList;
        },
        getSelectedFields: function () {
            if (this.selectedFields == false) {
                this.selectedFields = jQuery('#seleted_fields');
            }
            return this.selectedFields;
        },
        arrangeSelectChoicesInOrder: function () {
            var selectElement = this.getRelatedBlockColumnsList();
            var chosenElement = app.getSelect2ElementFromSelect(selectElement);
            var choicesContainer = chosenElement.find('ul.select2-choices');
            var choicesList = choicesContainer.find('li.select2-search-choice');

            var selectedOptions = selectElement.find('option:selected');
            var selectedOrder = JSON.parse(this.getSelectedFields().val());
            var selectedOrderKeys = [];
            for (var key in selectedOrder) {
                if (selectedOrder.hasOwnProperty(key)) {
                    selectedOrderKeys.push(key);
                }
            }
            for (var index = selectedOrderKeys.length; index > 0; index--) {
                var selectedValue = selectedOrder[selectedOrderKeys[index - 1]];
                var option = selectedOptions.filter('[value="' + selectedValue + '"]');
                choicesList.each(function (choiceListIndex, element) {
                    var liElement = jQuery(element);
                    if (liElement.find('div').html() == option.html()) {
                        choicesContainer.prepend(liElement);
                        return false;
                    }
                });
            }
        },
        makeColumnListSortable: function () {
            var thisInstance = this;
            var selectElement = thisInstance.getRelatedBlockColumnsList();
            var select2Element = app.getSelect2ElementFromSelect(selectElement);
            //TODO : peform the selection operation in context this might break if you have multi select element in advance filter
            //The sorting is only available when Select2 is attached to a hidden input field.
            var chozenChoiceElement = select2Element.find('ul.select2-choices');
            chozenChoiceElement.sortable({
                containment: 'parent',
                start: function () {
                    thisInstance.getSelectedFields().select2("onSortStart");
                },
                update: function () {
                    thisInstance.getSelectedFields().select2("onSortEnd");
                }
            });
        },
        isFormValidate: function () {
            var fieldElement = this.getRelatedBlockColumnsList();
            var fieldElementValue = fieldElement.find('option:selected').length;
            var select2Element = fieldElement.parent().find('.select2-container');
            if (fieldElementValue == 0) {
                vtUtils.showValidationMessage(select2Element, app.vtranslate('JS_REQUIRED_FIELD'));
                return false;
            } else {
                vtUtils.hideValidationMessage(select2Element);
                return true;
            }
        },
        initialize: function () {
            this.relatedblockColumnsList = false;
            this.selectedFields = false;

            var sort_selectbox1 = jQuery('#selectScolrow_1');
            var sort_selectbox2 = jQuery('#selectScolrow_2');
            var sort_selectbox3 = jQuery('#selectScolrow_3');

            sort_selectbox1.select2();
            sort_selectbox2.select2();
            sort_selectbox3.select2();
        },
        getForm: function () {
            if (this.formElement == false) {
                this.setForm(jQuery('#NewBlock'));
            }
            return this.formElement;
        },
        setForm: function (element) {
            this.formElement = element;
            return this;
        },
        calculateValues: function () {
            //handled advanced filters saved values.
            var advfilterlist = this.advanceFilterInstance.getValues();
            jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));

            var selectedSortOrderFields = [];
            var selectedSortFieldsRows = jQuery('.sortFieldRow');
            jQuery.each(selectedSortFieldsRows, function (index, element) {
                var currentElement = jQuery(element);
                var field = currentElement.find('select.selectedSortFields').val();
                var order = currentElement.find('.sortOrder').filter(':checked').val();
                //TODO: need to handle sort type for Reports
                var type = currentElement.find('.sortType').val();
                selectedSortOrderFields.push([field, order, type]);
            });
            jQuery('#selected_sort_fields').val(JSON.stringify(selectedSortOrderFields));
        },
        registerSubmitEvent: function () {
            var editViewForm = this.getForm();
            var thisInstance = this;
            editViewForm.submit(function () {
                thisInstance.calculateValues();
            });
        },
        registerEvents: function () {
            this.relatedblockColumnsList = false;
            this.selectedFields = false;
            this.arrangeSelectChoicesInOrder();
            this.makeColumnListSortable();
            this.registerSubmitEvent();
        },
        registerEvents2: function () {
            this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer'));
            CKEDITOR.replace('relatedblock', {height: '280px'});
        },
        registerEditEvents: function () {
            this.initialize();
            this.registerEvents2();
            this.registerSubmitEvent();
        },
        createRelatedBlockTable: function () {

            var selectedColumns = JSON.parse(this.getSelectedFields().val());
            var oEditor = CKEDITOR.instances.relatedblock;

            var table = "<table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse;'>";
            //header
            table += "<tr>";

            for (var key in selectedColumns) {
                tmpArr = selectedColumns[key].split(":");
                tmpLbl = tmpArr[2];
                var idx = tmpLbl.indexOf("_");
                var module = tmpLbl.slice(0, idx).toUpperCase();
                var label = tmpLbl.slice(idx + 1).replace(/_/g, " ");
                label = label.replace(/@~@/g, "_");          //because of PriceBook listprice field header that contains '_'
                label = "%R_" + module + "_" + label + "%";
                table += "<td>";
                table += label;
                table += "</td>";
            }

            table += "</tr>";

            //separator Start
            table += "<tr>";
            table += "<td colspan='50'>#RELBLOCK_START#</td>";
            table += "</tr>";

            table += "<tr>";
            for (var key in selectedColumns) {
                var coldata = selectedColumns[key].split(":");
                table += "<td>";
                table += "$" + coldata[3] + "$";
                table += "</td>";
            }

            table += "</tr>";

            //separator End
            table += "<tr>";
            table += "<td colspan='50'>#RELBLOCK_END#</td>";
            table += "</tr>";

            table += "</table>";

            oEditor.setData(table);
        },
        getSelectedColumns: function () {
            var columnListSelectElement = this.getRelatedBlockColumnsList();
            var select2Element = app.getSelect2ElementFromSelect(columnListSelectElement);

            var selectedValuesByOrder = [];
            var selectedOptions = columnListSelectElement.find('option:selected');

            var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
            orderedSelect2Options.each(function (index, element) {
                var chosenOption = jQuery(element);
                var choiceElement = chosenOption.closest('.select2-search-choice');
                var choiceValue = choiceElement.data('select2Data').id;
                selectedOptions.each(function (optionIndex, domOption) {
                    var option = jQuery(domOption);
                    if (option.val() == choiceValue) {
                        selectedValuesByOrder.push(option.val());
                        return false;
                    }
                });
            });
            return selectedValuesByOrder;
        }
    }
}


