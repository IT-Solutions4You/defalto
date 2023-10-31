/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Detail_Js('Portal_Detail_Js', {}, {
	registerAddBookmark: function () {
		jQuery('.addBookmark').click(function () {
			var params = {
				'module': app.getModuleName(),
				'parent': app.getParentModuleName(),
				'view': 'EditAjax'
			};
			Portal_List_Js.editBookmark(params);
		});
	},
	registerDetailViewChangeEvent: function () {
		jQuery('#bookmarksDropdown').change(function () {
			var selectedBookmark = jQuery('#bookmarksDropdown').val();
			app.helper.showProgress();
			var url = 'index.php?module='+app.getModuleName()+'&view=Detail&record='+selectedBookmark;
			window.location.href = url;
		});
	},
	registerEvents: function () {
		this.registerAddBookmark();
		this.registerDetailViewChangeEvent();
	}
});