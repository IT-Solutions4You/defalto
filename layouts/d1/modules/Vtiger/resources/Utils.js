/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

var vtUtils = {

        weekDaysArray : {Sunday : 0,Monday : 1, Tuesday : 2, Wednesday : 3,Thursday : 4, Friday : 5, Saturday : 6},


    registerReplaceCommaWithDot: function (container) {
        container.off('keyup', '.replaceCommaWithDot').on('keyup', '.replaceCommaWithDot', function (e) {
            if($(this).is('textarea')) {
                $(this).text($(this).text().toString().replace(',', '.'))
            } else {
                $(this).val($(this).val().toString().replace(',', '.'))
            }
        });
    },
    /**
	 * Function which will show the select2 element for select boxes . This will use select2 library
	 */
    showSelect2ElementView: function (selectElement, params) {
        if (selectElement.length > 1) {
            selectElement.each(function () {
                vtUtils.showSelect2ElementView($(this), params);
            });

            return selectElement;
        }

        if (typeof params == 'undefined') {
            params = {};
        }

        let data = selectElement.data();

        if (data != null) {
            params = jQuery.extend(data, params);
        }

        if (jQuery('#minilistWizardContainer').length) {
            params.maximumSelectionLength = 4
        }

        if (params['maximumSelectionSize']) {
            params.maximumSelectionLength = params['maximumSelectionSize']
        }

        // Fix to eliminate flicker happening on list view load
        let ele = jQuery(selectElement);

        if (ele.hasClass("listSearchContributor")) {
            ele.closest(".select2_search_div").find(".select2_input_element").remove();
            ele.show();
        }

        // Sort DOM nodes alphabetically in select box.
        if (typeof params['customSortOptGroup'] !== 'undefined' && params['customSortOptGroup']) {
            jQuery('optgroup', selectElement).each(function () {
                let optgroup = jQuery(this),
                    options = optgroup.children().toArray().sort(function (a, b) {
                        let aText = jQuery(a).text(),
                            bText = jQuery(b).text();

                        return aText < bText ? 1 : -1;
                    });
                jQuery.each(options, function (i, v) {
                    optgroup.prepend(v);
                });
            });

            delete params['customSortOptGroup'];
        }

        //formatSelectionTooBig param is not defined even it has the maximumSelectionSize,
        //then we should send our custom function for formatSelectionTooBig
        if (typeof params.maximumSelectionSize != "undefined" && typeof params.formatSelectionTooBig == "undefined") {
            let limit = params.maximumSelectionSize;
            //custom function which will return the maximum selection size exceeds message.
            params.formatSelectionTooBig = function (limit) {
                return app.vtranslate('JS_YOU_CAN_SELECT_ONLY') + ' ' + limit + ' ' + app.vtranslate('JS_ITEMS');
            };
        }

        if(typeof params.closeOnSelect == 'undefined') {
            params.closeOnSelect = !selectElement.attr('multiple');
        }

        if (selectElement.attr('multiple') !== 'undefined' && typeof params.selectOnClose == 'undefined') {
            params.selectOnClose = false;
        }

        if(selectElement.parents('.modal').length) {
            params.dropdownParent = selectElement.parents('.modal');
        }

        params.width = params.width ?? 'auto';
        params.theme = params.theme ?? 'bootstrap-5';

        if (!params.id && selectElement.attr('id')) {
            params.id = 's2id_' + selectElement.attr('id');
        }

        selectElement.select2(params).on('select2:opening', function (e) {
            if (selectElement.data('unselect')) {
                e.preventDefault();

                selectElement.data('unselect', false);
            }
        }).on('select2:unselect', function (e) {
            selectElement.data('unselect', true);
        }).on('open', function (e) {
            let element = jQuery(e.currentTarget),
                instance = element.data('select2');

            instance.dropdown.css('z-index', 1000002);
        }).on('select2-open', function (e) {
            let element = jQuery(e.currentTarget),
                instance = element.data('select2');

            instance.dropdown.css('z-index', 1000002);
        });

        //validator should not validate select2 text inputs
        $('.select2-search input').addClass('ignore-validation');

        if (typeof params.maximumSelectionSize != "undefined") {
            vtUtils.registerChangeEventForMultiSelect(selectElement, params);
        }

        selectElement.trigger('select2-loaded');

        return selectElement;
    },

    /**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */
    registerChangeEventForMultiSelect: function (selectElement, params) {
        if (typeof selectElement == 'undefined') {
            return;
        }

        selectElement.on("select2:select", function (e) {
            if ($(this).select2("data").length >= params.maximumSelectionSize) {
                $(this).select2("close");
            }
        });
    },
    getFirstDayId: function() {
        const defaultFirstDay = jQuery('#start_day').val();

        return this.weekDaysArray[defaultFirstDay];
    },
    getMonthNames: function() {
        return [
            app.vtranslate('JS_JANUARY'),
            app.vtranslate('JS_FEBRUARY'),
            app.vtranslate('JS_MARCH'),
            app.vtranslate('JS_APRIL'),
            app.vtranslate('JS_MAY'),
            app.vtranslate('JS_JUNE'),
            app.vtranslate('JS_JULY'),
            app.vtranslate('JS_AUGUST'),
            app.vtranslate('JS_SEPTEMBER'),
            app.vtranslate('JS_OCTOBER'),
            app.vtranslate('JS_NOVEMBER'),
            app.vtranslate('JS_DECEMBER')
        ];
    },
    getMonthNamesShort: function() {
        return [
            app.vtranslate('JS_JAN'),
            app.vtranslate('JS_FEB'),
            app.vtranslate('JS_MAR'),
            app.vtranslate('JS_APR'),
            app.vtranslate('JS_MAY'),
            app.vtranslate('JS_JUN'),
            app.vtranslate('JS_JUL'),
            app.vtranslate('JS_AUG'),
            app.vtranslate('JS_SEP'),
            app.vtranslate('JS_OCT'),
            app.vtranslate('JS_NOV'),
            app.vtranslate('JS_DEC')
        ];
    },
    getDayNames: function() {
        return [
            app.vtranslate('JS_SUNDAY'),
            app.vtranslate('JS_MONDAY'),
            app.vtranslate('JS_TUESDAY'),
            app.vtranslate('JS_WEDNESDAY'),
            app.vtranslate('JS_THURSDAY'),
            app.vtranslate('JS_FRIDAY'),
            app.vtranslate('JS_SATURDAY')
        ];
    },
    getDayNamesShort: function() {
        return [
            app.vtranslate('JS_SUN'),
            app.vtranslate('JS_MON'),
            app.vtranslate('JS_TUE'),
            app.vtranslate('JS_WED'),
            app.vtranslate('JS_THU'),
            app.vtranslate('JS_FRI'),
            app.vtranslate('JS_SAT')
        ];
    },
    getCurrentDay: function () {
        const date = new Date(),
            currentDay = String(date.getDate()).padStart(2, '0'),
            currentMonth = String(date.getMonth() + 1).padStart(2, "0"),
            currentYear = date.getFullYear();

        return currentYear + '/' + currentMonth + '/' + currentDay;
    },
    getDatePickerShortcutData: function (shortcut) {
        const self = this;

        function nextMonth(month) {
            return moment(month).startOf('month').add(1, 'month').toDate();
        }

        function prevMonth(month) {
            return moment(month).startOf('month').subtract(1, 'month').toDate();
        }

        let shortcutData = shortcut.split(','),
            shortcutType = shortcutData[0],
            dayTime = 86400000,
            currentDate = new Date(self.getCurrentDay()),
            currentTime = currentDate.getTime(),
            start = false,
            end = false;

        if ('day' === shortcutType) {
            let days = shortcutData[1];

            start = new Date(currentTime + dayTime);
            end = new Date(currentTime + (dayTime * days));
        } else if ('week' === shortcutType) {
            let firstDay = vtUtils.getFirstDayId();

            start = new Date(currentTime + dayTime);

            while (start.getDay() !== firstDay) {
                start = new Date(start.getTime() + dayTime);
            }

            end = new Date(start.getTime() + dayTime);

            while (end.getDay() !== firstDay) {
                end = new Date(end.getTime() + dayTime);
            }

            end = new Date(end.getTime() - dayTime);
        } else if ('month' === shortcutType) {
            start = nextMonth(currentDate);
            start.setDate(1);
            end = nextMonth(start);
            end.setDate(1);
            end = new Date(end.getTime() - dayTime);
        } else if ('year' === shortcutType) {
            start = new Date();
            start.setFullYear(currentDate.getFullYear() + 1);
            start.setMonth(0);
            start.setDate(1);
            end = currentDate;
            end.setFullYear(currentDate.getFullYear() + 1);
            end.setMonth(11);
            end.setDate(31);
        }

        if (start && end) {
            return {start: start, end: end};
        }

        return false;
    },
    getDatepickerRangeDefaultParams: function (element) {
        function updateButtons(input) {
            let container = input.datepicker('widget'),
                customButtons = '<div class="ui-datepicker-shortcut text-secondary"><b class="me-2">' + app.vtranslate('JS_SHORTCUTS') + '</b>' +
                    '<div class="me-2 next-days d-inline-block">' + app.vtranslate('JS_FOLLOWING') +
                    '<b class="ms-2 text-primary" data-shortcut="day,3">3 ' + app.vtranslate('JS_DAYS') + '</b>' +
                    '<b class="ms-2 text-primary" data-shortcut="day,5">5 ' + app.vtranslate('JS_DAYS') + '</b>' +
                    '<b class="ms-2 text-primary" data-shortcut="day,7">7 ' + app.vtranslate('JS_DAYS') + '</b>' +
                    '</div>' +
                    '<div class="me-2 next-buttons d-inline-block" >' + app.vtranslate('JS_NEXT') +
                    '<b class="ms-2 text-primary" data-shortcut="week,next">' + app.vtranslate('JS_WEEK') + '</b>' +
                    '<b class="ms-2 text-primary" data-shortcut="month,next">' + app.vtranslate('JS_MONTH') + '</b>' +
                    '<b class="ms-2 text-primary" data-shortcut="year,next">' + app.vtranslate('JS_YEAR') + '</b>' +
                    '</div></div>';

            setTimeout(function () {
                if (container.find('.ui-datepicker-shortcut').length) {
                    return;
                }

                container.find('.ui-datepicker-buttonpane').append(customButtons);
                container.off('click', '[data-shortcut]').on('click', '[data-shortcut]', function () {
                    let shortcut = $(this).data('shortcut'),
                        shortcutData = self.getDatePickerShortcutData(shortcut);

                    if (shortcutData) {
                        let inputElement = $(input),
                            startDate = app.getDateInVtigerFormat(format, shortcutData['start']),
                            endDate = app.getDateInVtigerFormat(format, shortcutData['end']);

                        if (startDate && endDate) {
                            inputElement.val(startDate + ',' + endDate)
                            inputElement.attr('data-times', (shortcutData['start'].getTime() - 1) + ',' + shortcutData['end'].getTime());
                            inputElement.datepicker('hide');
                        }
                    }
                });
            }, 1)
        }

        const self = this,
            language = app.getUserLanguage().substring(0, 2),
            format = self.getDatepickerFormat(element);

        return {
            numberOfMonths: 2,
            showButtonPanel: true,
            dateFormat: format.replace('yyyy', 'yy'),
            todayHighlight: true,
            language: language,
            firstDay: self.getFirstDayId(),
            weekStart: self.getFirstDayId(),
            monthNames: self.getMonthNames(),
            monthNamesShort: self.getMonthNamesShort(),
            dayNames: self.getDayNames(),
            dayNamesShort: self.getDayNamesShort(),
            width: 'auto',
            currentText: app.vtranslate('JS_TODAY'),
            beforeShow: function() {
                updateButtons(element);
            },
            onChangeMonthYear: function() {
                updateButtons(element);
            },
            beforeShowDay: function (date) {
                let inputElement = $(this),
                    inputValue = inputElement.attr('data-times'),
                    backgroundClass = 'day-inactive';

                if (inputValue) {
                    let times = inputValue.split(','),
                        startTime = parseInt(times[0]),
                        endTime = parseInt(times[1]),
                        time = date.getTime();

                    if (startTime <= time && time <= endTime) {
                        backgroundClass = 'day-active';
                    }
                }

                return [true, backgroundClass];
            },
            onClose: function (dateText, event) {
                event.inline = false;
            },
            onSelect: function (dateText, event) {
                updateButtons(element);

                let elementTimes = '',
                    elementValue = '',
                    dateTime = new Date(event['selectedYear'] + '/' + (parseInt(event['selectedMonth']) + 1) + '/' + event['selectedDay']).getTime();

                event.inline = true;

                if (!event.selectType) {
                    event.selectType = 'first';
                }

                if ('first' === event.selectType) {
                    event.selectType = 'second';
                    event.firstValue = dateText;
                    event.firstTime = dateTime;
                } else {
                    event.inline = true;
                    event.selectType = 'first';
                    event.secondValue = dateText;
                    event.secondTime = dateTime;

                    if (event.firstTime < event.secondTime) {
                        elementValue = event.firstValue + ',' + event.secondValue;
                        elementTimes = event.firstTime + ',' + event.secondTime;
                    } else {
                        elementValue = event.secondValue + ',' + event.firstValue;
                        elementTimes = event.secondTime + ',' + event.firstTime;
                    }
                }

                let inputElement = $(event.input);
                inputElement.val(elementValue);
                inputElement.attr('data-times', elementTimes);
            }
        };
    },
    getDatepickerFormat: function (element) {
        let userDateFormat = app.getDateFormat(),
            elementDateFormat = element.data('dateFormat');

        if (typeof elementDateFormat !== 'undefined') {
            userDateFormat = elementDateFormat;
        }

        return userDateFormat
    },
    getDatepickerDefaultParams: function(element) {
        const self = this,
            language = app.getUserLanguage().substring(0, 2),
            format = self.getDatepickerFormat(element);

        return {
            autoclose: true,
            todayBtn: "linked",
            dateFormat: format.replace('yyyy', 'yy'),
            todayHighlight: true,
            clearBtn: true,
            language: language,
            firstDay: self.getFirstDayId(),
            weekStart: self.getFirstDayId(),
            width: 'auto',
            monthNames: self.getMonthNames(),
            monthNamesShort: self.getMonthNamesShort(),
            dayNames: self.getDayNames(),
            dayNamesShort: self.getDayNamesShort(),
        };
    },
    /**
     * Function register datepicker for dateField elements
     */
    registerEventForDateFields: function (parent, params) {
        let element;

        if (parent.hasClass('dateField') && !parent.hasClass('ignore-ui-registration')) {
            element = parent;
        } else {
            element = jQuery('.dateField:not(ignore-ui-registration)', parent);
        }

        if (typeof params == 'undefined') {
            params = {};
        }

        let parentDateElement = element.parent();

        jQuery('.input-group-addon, .input-group-text', parentDateElement).on('click', function (e) {
            let elem = jQuery(e.currentTarget);
            elem.parent().find('.dateField').focus();
        });

        let defaultPickerParams;

        if (element.length > 0) {
            jQuery(element).each(function (index, Elem) {
                element = jQuery(Elem);

                if ('range' === element.data('calendarType')) {
                    defaultPickerParams = jQuery.extend({}, vtUtils.getDatepickerRangeDefaultParams(element), params);

                    element.datepicker(defaultPickerParams);
                } else {
                    defaultPickerParams = jQuery.extend({}, vtUtils.getDatepickerDefaultParams(element), params);

                    element.datepicker(defaultPickerParams);

                    if (element.hasClass('input-daterange')) {
                        element = element.find('input');
                    }
                }
            });
        }
    },

    /**
	 * Function which will register time fields
	 * @params : container - jquery object which contains time fields with class timepicker-default or itself can be time field
	 *			 registerForAddon - boolean value to register the event for Addon or not
	 *			 params  - params for the  plugin
	 * @return : container to support chaining
	 */
	registerEventForTimeFields : function(container, registerForAddon, params) {
		if(typeof container === 'undefined') {
			container = jQuery('body');
		}
		if(typeof registerForAddon === 'undefined'){
			registerForAddon = true;
		}

		container = jQuery(container);

		if (container.hasClass('timepicker-default')) {
            var element = container;
        } else {
            var element = container.find('.timepicker-default');
        }

		if(registerForAddon === true){
			var parentTimeElem = element.closest('.time');
			jQuery('.input-group-addon',parentTimeElem).on('click',function(e){
				var elem = jQuery(e.currentTarget);
				elem.closest('.time').find('.timepicker-default').focus();
			});
		}

		if(typeof params === 'undefined') {
			params = {};
		}

		var timeFormat = element.data('format');
		if(timeFormat == '24') {
			timeFormat = 'H:i';
		} else {
			timeFormat = 'h:i A';
		}
		var defaultsTimePickerParams = {
			'timeFormat' : timeFormat,
			'className'  : 'timePicker'
		};
		var params = jQuery.extend(defaultsTimePickerParams, params);

        if(element.length) {
            element.timepicker(params);
        }

		return container;
	},

    /**
     * Function to change view of edited elements related to selected Plugin
     * @param {type} elementsContainer
     * @returns {undefined}
     */
    applyFieldElementsView : function(container){
        this.showSelect2ElementView(container.find('select.select2'));
        this.registerEventForDateFields(container.find('.dateField').not('.ignore-ui-registration'));
        this.registerEventForTimeFields(container.find('.timepicker-default'));
    },

    showQtip : function(element,message,customParams) {
        if(typeof customParams === 'undefined') {
            customParams = {};
        }
        var qtipParams =  {
            content: {
                text: message
            },
            show: {
                event: 'Vtiger.Qtip.ShowMesssage'
            },
            hide: {
                event: 'Vtiger.Qtip.HideMesssage'
            }
        };
        jQuery.extend(qtipParams,customParams);

        element.qtip(qtipParams);
        element.trigger('Vtiger.Qtip.ShowMesssage');
    },

    hideQtip : function(element) {
        element.trigger('Vtiger.Qtip.HideMesssage');
    },

	linkifyStr : function(str) {
		var options = {'TLDs':267};
		return anchorme.js(str,options);
	},

	htmlSubstring : function(content, maxlength) {
		var m, r = /<([^>\s]*)[^>]*>/g,
			stack = [],
			lasti = 0,
			result = '';

		//for each tag, while we don't have enough characters
		while ((m = r.exec(content)) && maxlength) {
			//get the text substring between the last tag and this one
			var temp = content.substring(lasti, m.index).substr(0, maxlength);
			//append to the result and count the number of characters added
			result += temp;
			maxlength -= temp.length;
			lasti = r.lastIndex;

			if (content) {
				result += m[0];
				if (m[1].indexOf('/') === 0) {
					//if this is a closing tag, then pop the stack (does not account for bad html)
					stack.pop();
				} else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
					//if this is not a self closing tag then push it in the stack
					stack.push(m[1]);
				}
			}
		}

		//add the remainder of the string, if needed (there are no more tags in here)
		result += content.substr(lasti, maxlength);

		//fix the unclosed tags
		while (stack.length) {
			var unclosedtag = stack.pop();
			if(jQuery.inArray(unclosedtag,['br']) == -1){
				result += '</' + unclosedtag + '>';
			}
		}
		return result;
	},

    showValidationMessage : function(element,message,params) {
        if(element.hasClass('select2')) {
            element = app.helper.getSelect2FromSelect(element);
        }

        if(typeof params === 'undefined') {
            params = {};
        }

        var validationTooltipParams = {
            position: {
                my: 'bottom left',
                at: 'top left'
            },
            style: {
                classes: 'qtip-red qtip-shadow'
            }
        };

        jQuery.extend(validationTooltipParams,params);
        this.showQtip(element,message,validationTooltipParams);
        element.addClass('input-error');
    },

    hideValidationMessage : function(element) {
        if(element.hasClass('select2')) {
            element = app.helper.getSelect2FromSelect(element);
        }
        //should hide even message displyed by vtValidate
        element.trigger('Vtiger.Validation.Hide.Messsage');
        this.hideQtip(element);
        element.removeClass('input-error');
    },

    getMomentDateFormat : function() {
        var dateFormat = app.getDateFormat();
        return dateFormat.toUpperCase();
    },

    getMomentTimeFormat : function() {
        var hourFormat = app.getHourFormat();
        var timeFormat = 'HH:mm';
        if(hourFormat === 12) {
            timeFormat = 'hh:mm A';
        }
        return timeFormat;
    },

    getMomentCompatibleDateTimeFormat : function() {
        return this.getMomentDateFormat() + ' ' + this.getMomentTimeFormat();
    },

    convertFileSizeInToDisplayFormat : function(fileSizeInBytes) {
		 var i = -1;
		var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
		do {
			fileSizeInBytes = fileSizeInBytes / 1024;
			i++;
		} while (fileSizeInBytes > 1024);

		return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];

	},

    enableTooltips: function (querySelector) {
        jQuery(function () {
            if(!querySelector) {
                querySelector = '[data-bs-toggle="tooltip"]';
            }

            const tooltipTriggerList = document.querySelectorAll(querySelector)
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        });
    },
    
    stripTags : function(string,allowed) {
        //https://stackoverflow.com/questions/5601903/jquery-almost-equivalent-of-phps-strip-tags#answer-46483672
        allowed = (((allowed || '') + '').toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
        var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
        return string.replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
    },
	
    addMask: function (container) {
            if (container.length && jQuery('#vt-mask').length == 0) {
                    var mask = '<div id="vt-mask" class="vt-page-mask" ></div>'
                    container.before(mask);
            }
    },

    removeMask: function () {
            if (jQuery('#vt-mask').length) {
                    jQuery('#vt-mask').remove();
            }
    },
        
    isPasswordStrong : function(password) {
            /*
            * ^					The password string will start this way
            * (?=.*[a-z])			The string must contain at least 1 lowercase alphabetical character
            * (?=.*[A-Z])			The string must contain at least 1 uppercase alphabetical character
            * (?=.*[0-9])			The string must contain at least 1 numeric character
            * (?=.*[!@#\$%\^&\*])	The string must contain at least one special character, but we are escaping reserved RegEx characters to avoid conflict
            * (?=.{8,})			The string must be eight characters or longer
            */
           var password_regex = jQuery('[name="pwd_regex"]').val();
           if((typeof password_regex != 'undefined') && (password_regex != '')){
                var strongPasswordRegex = new RegExp(password_regex);
                var isStrong = strongPasswordRegex.test(password)? true : false; 
                return isStrong;
           }
	   // If password regex is not set - consider it as strong.
           return true;
    },
    makeSelect2ElementSortable: function (selectElement, valueElement, getValueFunction, setValueFunction) {

        vtUtils.showSelect2ElementView(selectElement, {
            results: function() {
                return [
                    {
                        id: 0,
                        text: 'enhancement'
                    },
                    {
                        id: 1,
                        text: 'bug'
                    },
                    {
                        id: 2,
                        text: 'duplicate'
                    },
                    {
                        id: 3,
                        text: 'invalid'
                    },
                    {
                        id: 4,
                        text: 'wontfix'
                    }
                ];
            },
        })

        let selectParent = selectElement.parent(),
            select2Element = selectElement.next('.select2'),
            select2ChoiceElement = select2Element.find('ul.select2-selection__rendered'),
            selectedFieldNames;

        orderSortedValues = function () {
            selectParent.find("ul.select2-selection__rendered").children("li[title]").each(function (i, obj) {
                console.log(obj, selectElement.select2('data'));

                let element = selectElement.children('option').filter(function () {
                    return $(this).html() == obj.title
                });

                moveElementToEndOfParent(element)
            });
        };

        moveElementToEndOfParent = function (element) {
            element.parents('select').append(element.detach());
        };

        updateOptionTitles = function(selectElement) {
            selectElement.find('option').each(function() {
                $(this).attr('title', $(this).html());
            });
        }

        getOptionById = function (selectElement, id) {
            let optionElement;

            selectElement.find('option').each(function () {
                if ($(this).val() === id) {
                    optionElement = $(this);
                }
            });

            return optionElement;
        }

        updateOptionTitles(selectElement);

        if ('function' === typeof getValueFunction) {
            selectedFieldNames = getValueFunction(valueElement);
        } else {
            selectedFieldNames = valueElement.val().split(';');
        }

        if (selectedFieldNames) {
            $.each(selectedFieldNames, function (index, id) {
                let element = getOptionById(selectElement, id);

                moveElementToEndOfParent(element);
            });

            selectElement.trigger('change');
        }

        selectElement.on("select2:select", function (evt) {
            let id = evt.params.data.id,
                element = getOptionById($(this), id);

            moveElementToEndOfParent(element);

            selectElement.trigger('change');
        });

        select2ChoiceElement.sortable({
            update: function () {
                //If arrangements of fields is completed save field order button should be enabled
                if (selectElement.val().length > 1) {
                    orderSortedValues();
                }
            },
            stop: function () {
                selectElement.trigger('change');
            }
        });

        selectElement.on('change', function () {
            let selectIds = [];

            select2Element.find('.select2-selection__choice').each(function (i, selectedElement) {
                selectElement.find('option').each(function (i, optionElement) {
                    if ($(optionElement).attr('title') === $(selectedElement).attr('title')) {
                        selectIds.push($(optionElement).val())
                    }
                })
            })

            if ('function' === typeof setValueFunction) {
                setValueFunction(valueElement, selectIds);
            } else {
                valueElement.val(selectIds.join(';'));
            }
        });
    },
}
