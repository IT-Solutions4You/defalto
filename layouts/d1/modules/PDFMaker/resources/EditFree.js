/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Vtiger_Edit_Js("PDFMaker_EditFree_Js",{

    duplicateCheckCache : {},
    advanceFilterInstance : false,
    formElement : false,

    getForm : function(){
        if(this.formElement == false){
                this.setForm(jQuery('#EditView'));
        }
        return this.formElement;
    },
    setForm : function(element){
        this.formElement = element;
        return this;
    },    
    registerRecordPreSaveEvent : function(form){
        if(typeof form == 'undefined'){
                form = this.getForm();
        }

        form.on(Vtiger_Edit_Js.recordPreSave, function(e, data){

            if (!PDFMaker_EditFreeJs.ControlNumber('margin_top', true) || !PDFMaker_EditFreeJs.ControlNumber('margin_bottom', true) || !PDFMaker_EditFreeJs.ControlNumber('margin_left', true) || !PDFMaker_EditFreeJs.ControlNumber('margin_right', true)){
                error++;
            }
            if (!PDFMaker_EditFreeJs.CheckCustomFormat()){
                error++;
            }
            if (error == 0){
                moduleName = app.getModuleName();
                form.submit();

            }
            e.preventDefault();
        })
    },
    registerBasicEvents: function(container){
        this._super(container);
        this.registerButtons();
        this.registerRecordPreSaveEvent();

    },    
    registerSubmitEvent: function(){
        var thisInstance = this;
        var editViewForm = this.getForm();
        editViewForm.submit(function(e){
            //Form should submit only once for multiple clicks also
            if(typeof editViewForm.data('submit') != "undefined"){
                    return false;
            } else {
                thisInstance.calculateValues();
                editViewForm.data('submit', 'true');
                //on submit form trigger the recordPreSave event
                var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
                editViewForm.trigger(recordPreSaveEvent, {'value' : 'edit'});
                if(recordPreSaveEvent.isDefaultPrevented()) {
                        //If duplicate record validation fails, form should submit again
                        editViewForm.removeData('submit');
                        e.preventDefault();
                }
            }
        });
    },
    registerButtons: function() {
        var thisInstance = this;
        var selectElement1 = jQuery('.InsertIntoTemplate');
        selectElement1.on('click', function() {
            var selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType,false);
        });
        var selectElement2 = jQuery('.InsertLIntoTemplate');
        selectElement2.on('click', function() {
            var selectedType = jQuery(this).data('type');
            thisInstance.InsertIntoTemplate(selectedType,true);
        });
    },
    InsertIntoTemplate: function(element,islabel){

        let selectedTab2 = jQuery('#ContentEditorTabs').find('.active').data('type'),
            selectField = document.getElementById(element).value,
            oEditor,
            CKEditorInstance = new Vtiger_CkEditor_Js(),
            insert_value = '';

        switch (selectedTab2) {
            case 'header':
                selectedTab2 = 'header_body';
                break;
            case 'footer':
                selectedTab2 = 'footer_body';
        }

        oEditor = CKEditorInstance.load(selectedTab2);

        if (islabel){
            let insert_value = selectField;

            if ('relatedmodulefields' === element) {
                insert_value = 'R_' + insert_value;
            }

            oEditor.insertHtml('%' + insert_value + '%');
        } else {

            if (element != 'header_var' && element != 'footer_var' && element != 'hmodulefields' && element != 'fmodulefields' && element != 'dateval'){
                if (selectField != ''){
                    if (selectField == 'ORGANIZATION_STAMP_SIGNATURE')
                        insert_value = jQuery('#company_stamp_signature_content').html();
                    else if (selectField == 'COMPANY_LOGO')
                        insert_value = jQuery('#companylogo_content').html();
                    else if (selectField == 'ORGANIZATION_HEADER_SIGNATURE')
                        insert_value = jQuery('#company_header_signature_content').html();
                    else if (selectField == 'VATBLOCK')
                        insert_value = jQuery('#vatblock_table_content').html();
                    else {
                        if (element == "articelvar")
                            insert_value = '#' + selectField + '#';
                        else if (element == "relatedmodulefields")
                            insert_value = '$R_' + selectField + '$';
                        else if (element == "productbloctpl" || element == "productbloctpl2")
                            insert_value = selectField;
                        else if (element == "global_lang")
                            insert_value = '%G_' + selectField + '%';
                        else if (element == "module_lang")
                            insert_value = '%M_' + selectField + '%';
                        else
                            insert_value = '$' + selectField + '$';
                    }
                    oEditor.insertHtml(insert_value);
                }

            } else {
                if (selectField != ''){
                    if (element == 'hmodulefields' || element == 'fmodulefields'){
                        oEditor.insertHtml('$' + selectField + '$');
                    } else {
                        oEditor.insertHtml(selectField);
                    }
                }
            }
        }
    },

    registerSelectRelatedModuleOption : function() {
        var thisInstance = this;
        var selectElement = jQuery('[name="relatedmodulesorce"]');
        selectElement.on('change', function() {
            var selectedOption = selectElement.find('option:selected');
            var moduleName = selectedOption.data('module');
            var fieldName = selectedOption.val();
            
            thisInstance.getFields(moduleName,"relatedmodulefields",fieldName);
        });		
    },
    
    getFields : function(moduleName,selectname,fieldName) {
        var thisInstance = this;

        var urlParams = {
            "module": "PDFMaker",
            "formodule" : moduleName,
            "forfieldname" : fieldName,
            "action" : "IndexAjax",
            "mode" : "getModuleFields"            
        };

        app.request.post({'data' : urlParams}).then(
            function(err,response) {
                thisInstance.updateFields(response,selectname);
            }      
        );
    },
    
    updateFields: function(response,selectname){
        var thisInstance = this;

        var result = response['success'];
        var formElement = this.getForm();

        if(result == true) {
            var ModuleFieldsElement = jQuery('#'+selectname);
            //ModuleFieldsElement.find('option:not([value=""]').remove();
            ModuleFieldsElement.empty();

            if (selectname == "filename_fields") {
                jQuery.each(response['filename_fields'], function (i, fields) {

                    var optgroup = jQuery('<optgroup/>');
                    optgroup.attr('label',i);

                    jQuery.each(fields, function (key, field) {

                        optgroup.append(jQuery('<option>', { 
                            value: key,
                            text : field 
                        }));
                    })

                    ModuleFieldsElement.append(optgroup);
                });                   
            }

            jQuery.each(response['fields'], function (i, fields) {

                var optgroup = jQuery('<optgroup/>');
                optgroup.attr('label',i);

                jQuery.each(fields, function (key, field) {

                    optgroup.append(jQuery('<option>', { 
                        value: key,
                        text : field 
                    }));
                })

                ModuleFieldsElement.append(optgroup);
            });

            ModuleFieldsElement.select2("destroy");
            ModuleFieldsElement.select2();

            if (selectname == "modulefields") {                        

                var RelatedModuleSourceElement = jQuery('#relatedmodulesorce');
                //RelatedModuleSourceElement.find('option:not([value=""]').remove();
                RelatedModuleSourceElement.empty();
                jQuery.each(response['related_modules'], function (i, item) {

                    RelatedModuleSourceElement.append(jQuery('<option>', { 
                        value: item[0],
                        text : item[2] + " (" + item[1] + ")",
                    }).data("module",item[3]));
                });

                RelatedModuleSourceElement.select2("destroy");
                RelatedModuleSourceElement.select2();
                RelatedModuleSourceElement.trigger('change');
                thisInstance.updateFields(response,"filename_fields");
            } 
        }
    },
    registerSelectAccInfoOption : function() {
        var selectElement = jQuery('[name="acc_info_type"]');
        selectElement.on('change', function() {
            var selectedOption = selectElement.find('option:selected');
            jQuery('.au_info_div').css('display','none');
            switch (selectedOption.val()){
                case "Assigned":
                    var div_name = 'user_info_div';
                    break;
                case "Logged":
                    var div_name = 'logged_user_info_div';
                    break;
                case "Modifiedby":
                    var div_name = 'modifiedby_user_info_div';
                    break;
                case "Creator":
                    var div_name = 'smcreator_user_info_div';
                    break;
                default:
                    var div_name = 'acc_info_div';
                    break;
            }
            jQuery('#'+div_name).css('display','inline');
        });
    },
    registerSelectModuleOption : function() {
        var thisInstance = this;
        var selectElement = jQuery('[name="modulename"]');
        var moduleName = selectElement.val();

        thisInstance.getFields(moduleName,"modulefields","");
        PDFMaker_EditFreeJs.fill_module_lang_array(moduleName);
        PDFMaker_EditFreeJs.fill_module_product_fields_array(moduleName);
    },
    registerCKEditor: function() {
        const ckeditorInstance = new Vtiger_CkEditor_Js();

        ckeditorInstance.loadCkEditor($('#body'), {height: '70vh'});
        ckeditorInstance.loadCkEditor($('#header_body'), {height: '70vh'});
        ckeditorInstance.loadCkEditor($('#footer_body'), {height: '70vh'});
    },
    registerEvents: function(){
        this.registerAppTriggerEvent();
        this.registerCKEditor();

        var editViewForm = this.getForm();
        var statusToProceed = this.proceedRegisterEvents();
        if(!statusToProceed){
                return;
        }
        this.registerSelectModuleOption();

        this.registerBasicEvents(this.getForm());
        this.registerSelectRelatedModuleOption();
        this.registerSubmitEvent();
        this.registerSelectAccInfoOption();


        if (typeof this.registerLeavePageWithoutSubmit == 'function'){
            this.registerLeavePageWithoutSubmit(editViewForm);
        }             
    },
    registerAutoCompleteFields: function() {

    },
});
if (typeof(PDFMaker_EditFreeJs) == 'undefined'){
    /*
     * Namespaced javascript class for Import
     */
    PDFMaker_EditFreeJs = {
        reportsColumnsList : false,
        advanceFilterInstance : false,
        availListObj : false,
        selectedColumnsObj : false,
    
        clearRelatedModuleFields: function(){
            second = document.getElementById("relatedmodulefields");
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            var box2 = second;
            var optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            objOption = document.createElement("option");
            objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
            objOption.value = "";
            box2.appendChild(objOption);
        },
        change_relatedmodulesorce: function(first, second_name){
            second = document.getElementById(second_name);
            optionTest = true;
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            var box = first;
            var number = box.options[box.selectedIndex].value;
            if (!number)
                return;
            
            var params = {
                            module : app.getModuleName(),
                            view : 'IndexAjax',
                            source_module : number,
                            mode : 'getModuleConditions'
            }
            var actionParams = {
                "type": "POST",
                "url": 'index.php',
                "dataType": "html",
                "data": params
            };

            var box2 = second;
            var optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            var list = all_related_modules[number];
            for (i = 0; i < list.length; i += 2){
                objOption = document.createElement("option");
                objOption.innerHTML = list[i];
                objOption.value = list[i + 1];
                box2.appendChild(objOption);
            }

            PDFMaker_EditFreeJs.clearRelatedModuleFields();
        },
        change_relatedmodule: function(first, second_name){
            second = document.getElementById(second_name);
            optionTest = true;
            lgth = second.options.length - 1;
            second.options[lgth] = null;
            if (second.options[lgth])
                optionTest = false;
            if (!optionTest)
                return;
            var box = first;
            var number = box.options[box.selectedIndex].value;
            if (!number)
                return;
            var box2 = second;
            var optgroups = box2.childNodes;
            for (i = optgroups.length - 1; i >= 0; i--){
                box2.removeChild(optgroups[i]);
            }

            if (number == "none"){
                objOption = document.createElement("option");
                objOption.innerHTML = app.vtranslate("LBL_SELECT_MODULE_FIELD");
                objOption.value = "";
                box2.appendChild(objOption);
            } else {
                var tmpArr = number.split('|', 2);
                var moduleName = tmpArr[0];
                number = tmpArr[1];
                var blocks = module_blocks[moduleName];
                for (b = 0; b < blocks.length; b += 2){
                    var list = related_module_fields[moduleName + '|' + blocks[b + 1]];
                    if (list.length > 0){
                        optGroup = document.createElement('optgroup');
                        optGroup.label = blocks[b];
                        box2.appendChild(optGroup);
                        for (i = 0; i < list.length; i += 2){
                            objOption = document.createElement("option");
                            objOption.innerHTML = list[i];
                            var objVal = list[i + 1];
                            var newObjVal = objVal.replace(moduleName.toUpperCase() + '_', number.toUpperCase() + '_');
                            objOption.value = newObjVal;
                            optGroup.appendChild(objOption);
                        }
                    }
                }
            }
        },
        ControlNumber: function(elid, final){
            var control_number = document.getElementById(elid).value;
            var re = new Array();
            re[1] = new RegExp("^([0-9])");
            re[2] = new RegExp("^[0-9]{1}[.]$");
            re[3] = new RegExp("^[0-9]{1}[.][0-9]{1}$");
            if (control_number.length > 3 || !re[control_number.length].test(control_number) || (final == true && control_number.length == 2)){
                alert(app.vtranslate("LBL_MARGIN_ERROR"));
                document.getElementById(elid).focus();
                return false;
            } else {
                return true;
            }
        },
        showHideTab3: function(tabname){
            document.getElementById(tabname + '_tab2').className = 'active';
            if (tabname == 'body'){
                document.getElementById('body_variables').style.display = '';
                document.getElementById('related_block_tpl_row').style.display = '';
                document.getElementById('listview_block_tpl_row').style.display = '';
            } else {
                document.getElementById('header_variables').style.display = '';
                document.getElementById('body_variables').style.display = 'none';
                document.getElementById('related_block_tpl_row').style.display = 'none';
                document.getElementById('listview_block_tpl_row').style.display = 'none';
            }


            document.getElementById(tabname + '_div2').style.display = 'block';
            box = document.getElementById('modulename');
            var module = box.options[box.selectedIndex].value;

        },
        fill_module_lang_array: function(module, selected){
            
            var urlParams = {
                "module" : "PDFMaker",
                "handler" : "fill_lang",
                "action" : "AjaxRequestHandle",
                "langmod" : module            
            };

            app.request.post({'data' : urlParams}).then(
                function(err,response) {
                    var result = response['success'];

                    if(result == true) {
                        var moduleLangElement = jQuery('#module_lang');

                        moduleLangElement.empty();

                        jQuery.each(response['labels'], function (key, langlabel) {

                             moduleLangElement.append(jQuery('<option>', {
                                        value: key,
                                        text : langlabel
                            }));
                        });
                    }
            })
        },
        fill_module_product_fields_array: function(module){
            var ajax_url = 'index.php?module=PDFMaker&action=AjaxRequestHandle&handler=fill_module_product_fields&productmod=' + module;
            jQuery.ajax(ajax_url).success(function(response){

                var product_fields = document.getElementById('psfields');
                product_fields.length = 0;
                var map = response.split('|@|');
                var keys = map[0].split('||');
                var values = map[1].split('||');
                for (i = 0; i < values.length; i++){
                    var item = document.createElement('option');
                    item.text = values[i];
                    item.value = keys[i];
                    try {
                        product_fields.add(item, null);
                    } catch (ex){
                        product_fields.add(item);
                    }
                }
            }).error(function(){
            });
        },
        insertFieldIntoFilename: function(val){
            if (val != '')
                document.getElementById('nameOfFile').value += '$' + val + '$';
        },
        CustomFormat: function(){
            var selObj;
            selObj = document.getElementById('pdf_format');

            if (selObj.value == 'Custom'){
                document.getElementById('custom_format_table').style.display = 'table';
            } else {
                document.getElementById('custom_format_table').style.display = 'none';
            }
        },
        hf_checkboxes_changed: function(oChck, oType){
            var prefix;
            var optionsArr;
            if (oType == 'header'){
                prefix = 'dh_';
                optionsArr = new Array('allid', 'firstid', 'otherid');
            } else {
                prefix = 'df_';
                optionsArr = new Array('allid', 'firstid', 'otherid', 'lastid');
            }

            var tmpArr = oChck.id.split("_");
            var sufix = tmpArr[1];
            var i;
            if (sufix == 'allid'){
                for (i = 0; i < optionsArr.length; i++){
                    document.getElementById(prefix + optionsArr[i]).checked = oChck.checked;
                }
            } else {
                var allChck = document.getElementById(prefix + 'allid');
                var allChecked = true;
                for (i = 1; i < optionsArr.length; i++){
                    if (document.getElementById(prefix + optionsArr[i]).checked == false){
                        allChecked = false;
                        break;
                    }
                }
                allChck.checked = allChecked;
            }
        },
        templateActiveChanged: function(activeElm){
            var is_defaultElm1 = document.getElementById('is_default_dv');
            var is_defaultElm2 = document.getElementById('is_default_lv');

            if (activeElm.value == '1'){
                is_defaultElm1.disabled = false;
                is_defaultElm2.disabled = false;
            } else {
                is_defaultElm1.checked = false;
                is_defaultElm1.disabled = true;
                is_defaultElm2.checked = false;
                is_defaultElm2.disabled = true;
            }
        },
        CheckCustomFormat: function(){
            if (document.getElementById('pdf_format').value == 'Custom'){
                var pdfWidth = document.getElementById('pdf_format_width').value;
                var pdfHeight = document.getElementById('pdf_format_height').value;
                if (pdfWidth > 2000 || pdfHeight > 2000 || pdfWidth < 1 || pdfHeight < 1 || isNaN(pdfWidth) || isNaN(pdfHeight)){
                    alert(app.vtranslate('LBL_CUSTOM_FORMAT_ERROR'));
                    document.getElementById('pdf_format_width').focus();
                    return false;
                }
            }
            return true;
        }

    }    
}
