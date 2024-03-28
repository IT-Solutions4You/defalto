/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
            let actualStep = document.getElementById('step').value * 1;
            let nextStep = actualStep + 1;

            if (2 === nextStep) {
                EMAILMaker_RelatedBlockJs.changeSecOptions();
            } else if (6 === nextStep) {
                let editViewForm = this.getForm(),
                    blockNameElement = jQuery('input[name="blockname"]'),
                    control = blockNameElement.val();

                if (!control) {
                    vtUtils.showValidationMessage(blockNameElement, app.vtranslate('JS_REQUIRED_FIELD'));
                    return false;
                } else {
                    vtUtils.hideValidationMessage(blockNameElement);
                }
                
                editViewForm.submit();
            } else {
                if (3 === nextStep) {
                    if (!this.isFormValidate()) return false;

                    let selectedFields = this.getSelectedColumns();
                    this.getSelectedFields().val(JSON.stringify(selectedFields));
                    this.createRelatedBlockTable();
                }

                jQuery("#crumb" + actualStep).removeClass('active');
                jQuery("#crumb" + nextStep).addClass('active');
                jQuery("#step" + actualStep).addClass('hide');
                jQuery("#step" + nextStep).removeClass('hide');
            }

            document.getElementById('back_rep').disabled = false;
            document.getElementById('step').value = nextStep;
        },
        changeStepsback: function (mode) {
            let actualStep = document.getElementById('step').value * 1,
                lastStep = actualStep - 1;

            jQuery("#crumb" + actualStep).removeClass('active');
            jQuery("#crumb" + lastStep).addClass('active');

            jQuery("#step" + actualStep).addClass('hide');
            jQuery("#step" + lastStep).removeClass('hide');

            if ((1 === lastStep && 'create' === mode) || (3 === lastStep && 'edit' === mode)) {
                document.getElementById('back_rep').disabled = true;
            }

            document.getElementById('step').value = lastStep;
        },
        changeSecOptions: function () {
            let self = this,
                primodule = document.NewBlock.primarymodule.value,
                secmodule = EMAILMaker_RelatedBlockJs.getCheckedValue(document.NewBlock.secondarymodule),
                saved_secmodule_element = jQuery("#saved_secmodule"),
                saved_secmodule = saved_secmodule_element.val();

            if (saved_secmodule !== secmodule) {
                saved_secmodule_element.val(secmodule);

                let params = {
                    'module': 'EMAILMaker',
                    'action': 'IndexAjax',
                    'mode': 'GetRelatedBlockColumns',
                    'type': 'columns',
                    'secmodule': secmodule,
                    'primodule': primodule
                };

                app.helper.showProgress();

                let ModuleFieldsElements = jQuery('.relatedblockColumns');

                app.request.post({'data': params}).then(
                    function (err, response) {
                        app.helper.hideProgress();

                        ModuleFieldsElements.html('');
                        ModuleFieldsElements.each(function (index, domElement) {
                            let ModuleFieldsElement = jQuery(domElement);

                            jQuery.each(response['fields'], function (i, fields) {

                                let optgroup = jQuery('<optgroup/>');
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

                        let relatedblockColumnsListElement = jQuery('#relatedblockColumnsList');
                        relatedblockColumnsListElement.select2("destroy");

                        vtUtils.showSelect2ElementView(relatedblockColumnsListElement);

                        self.initialize();
                        self.makeColumnListSortable();

                        jQuery("#crumb1").removeClass('active');
                        jQuery("#crumb2").addClass('active');

                        jQuery("#step1").addClass('hide');
                        jQuery("#step2").removeClass('hide');
                    }
                );

                let params2 = {
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
                jQuery("#crumb1").removeClass('active');
                jQuery("#crumb2").addClass('active');

                jQuery("#step1").addClass('hide');
                jQuery("#step2").removeClass('hide');
            }
        },
        getCheckedValue: function (radioObj) {
            if (!radioObj)
                return "";
            let radioLength = radioObj.length;
            if (radioLength == undefined)
                if (radioObj.checked)
                    return radioObj.value;
                else
                    return "";
            for (let i = 0; i < radioLength; i++) {
                if (radioObj[i].checked) {
                    return radioObj[i].value;
                }
            }
            return "";
        },
        registerSelect2ElementForRelatedBlockColumns: function () {
            let selectElement = this.getRelatedBlockColumnsList();

            vtUtils.showSelect2ElementView(selectElement);
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
        makeColumnListSortable: function () {
            let selectParent = this.getRelatedBlockColumnsList().parent(),
                selectSelection = selectParent.find('.select2-selection ul');

            selectSelection.sortable();
        },
        isFormValidate: function () {
            let fieldElement = this.getRelatedBlockColumnsList();
            let fieldElementValue = fieldElement.find('option:selected').length;
            let select2Element = fieldElement.parent().find('.select2-container');
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

            let sort_selectbox1 = jQuery('#selectScolrow_1'),
                sort_selectbox2 = jQuery('#selectScolrow_2'),
                sort_selectbox3 = jQuery('#selectScolrow_3');

            vtUtils.showSelect2ElementView(sort_selectbox1);
            vtUtils.showSelect2ElementView(sort_selectbox2);
            vtUtils.showSelect2ElementView(sort_selectbox3);
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
            let advfilterlist = this.advanceFilterInstance.getValues();
            jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));

            let selectedSortOrderFields = [];
            let selectedSortFieldsRows = jQuery('.sortFieldRow');
            jQuery.each(selectedSortFieldsRows, function (index, element) {
                let currentElement = jQuery(element);
                let field = currentElement.find('select.selectedSortFields').val();
                let order = currentElement.find('.sortOrder').filter(':checked').val();
                let type = currentElement.find('.sortType').val();
                selectedSortOrderFields.push([field, order, type]);
            });
            jQuery('#selected_sort_fields').val(JSON.stringify(selectedSortOrderFields));
        },
        registerSubmitEvent: function () {
            let editViewForm = this.getForm();
            let thisInstance = this;
            editViewForm.submit(function () {
                thisInstance.calculateValues();
            });
        },
        registerEvents: function () {
            this.relatedblockColumnsList = false;
            this.selectedFields = false;
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

            let selectedColumns = JSON.parse(this.getSelectedFields().val());
            let oEditor = CKEDITOR.instances['relatedblock'];

            let table = "<table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse;'>";
            //header
            table += "<tr>";

            for (let key in selectedColumns) {
                let tmpArr = selectedColumns[key].split(":");
                let tmpLbl = tmpArr[2];
                let idx = tmpLbl.indexOf("_");
                let module = tmpLbl.slice(0, idx).toUpperCase();
                let label = tmpLbl.slice(idx + 1).replace(/_/g, " ");
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
            for (let key in selectedColumns) {
                let coldata = selectedColumns[key].split(":");
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
            let element = this.getRelatedBlockColumnsList(),
                parent = element.parent(),
                data = element.select2('data'),
                values = [];

            if (data) {
                parent.find('.select2-selection__choice').each(function () {
                    let choice = $(this);

                    $.each(data, function (index, value) {
                        if (value.text === choice.attr('title')) {
                            values.push(value.id);
                        }
                    });
                });
            }

            return values;
        }
    }
}


