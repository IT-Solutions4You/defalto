/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Vtiger_Edit_Js("Vtiger_EditSharingRecord_Js", {}, {
	memberSelectElement : false,

	/**
	 *
	 * @param element
	 * @returns {boolean}
	 */
    getMemberSelectElement : function ( element) {
		if(this.memberSelectElement == false) {
			this.memberSelectElement = jQuery(element);
		}
		return this.memberSelectElement;
	},
    
    memberSelectEditElement : false,
	/**
	 *
	 * @returns {boolean}
	 */
    getMemberSelectEditElement : function () {
		if(this.memberSelectEditElement == false) {
			this.memberSelectEditElement = jQuery('#memberEditList');
		}

		return this.memberSelectEditElement;
	},
    
	/**
	 * Function to register event for select2 element
	 */
	registerEventForSelect2Element : function(){
		let editViewForm = this.getForm();//jQuery('#EditSharingRecord');
		let selectElement = this.getMemberSelectElement('#memberViewList');//jQuery("#memberViewList");
		let params = {};
		params.formatSelection = function(object,container){
			let selectedId = object.id;
			let container2 = editViewForm.find('#ViewList');
			let selectedOptionTag = container2.find('option[value="'+selectedId+'"]');
			let selectedMemberType = selectedOptionTag.data('memberType');
			container.prevObject.addClass(selectedMemberType);
			let element = '<div>'+selectedOptionTag.text()+'</div>';

			return element;
		}
		this.changeSelectElementView(selectElement, 'select2',params);

		let selectElement2 = this.getMemberSelectEditElement();//jQuery("#memberEditList");
		let params2 = {};
		params2.formatSelection = function(object,container){
			let selectedId = object.id;
			let container2 = editViewForm.find('#EditList');
			let selectedOptionTag = container2.find('option[value="'+selectedId+'"]');
			let selectedMemberType = selectedOptionTag.data('memberType');
			container.prevObject.addClass(selectedMemberType);
			let element = '<div>'+selectedOptionTag.text()+'</div>';

			return element;
		}
		this.changeSelectElementView(selectElement2, 'select2',params2);
	},


	changeSelectElementView : function(parent, view, viewParams){
		if(typeof parent == 'undefined') {
			parent = jQuery('body');
		}

		//If view is select2, This will convert the ui of select boxes to select2 elements.
		if('select2' == view ) {
			vtUtils.showSelect2ElementView(parent, viewParams);
			return;
		}
	},
	
	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		let editViewForm = jQuery('#EditSharingRecord');
		editViewForm.validationEngine(app.getvalidationEngineOptions(true));
	},
	
	/**
	 * Function to register the submit event of form
	 */
	registerSubmitEvent : function() {
		let thisInstance = this;
		let form = jQuery('#EditSharingRecord');
		form.on('submit',function(e) {
			if(form.data('submit') == 'true' && form.data('performCheck') == 'true') {
				return true;
			} else {
				form.data('submit', 'true');
				form.data('performCheck', 'true');
				form.submit();
				e.preventDefault();
			}
		});
	},
	
	/**
	 * Function which will handle the registrations for the elements 
	 */
	registerEvents : function() {
		this._super();
		this.registerEventForSelect2Element();
		this.registerSubmitEvent();
	}
});