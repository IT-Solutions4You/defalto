/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

Vtiger.Class('Migration_Index_Js', {
	startMigrationEvent: function () {
		let params = {
			module: 'Migration',
			view: 'Index',
			mode: 'applyDBChanges',
		};

		app.request.post({data: params}).then(function (err, data) {
			jQuery('#running').addClass('hide').removeClass('show');
			jQuery('#success').addClass('show').removeClass('hide');
			jQuery('#nextButton').addClass('show').removeClass('hide');
			jQuery('#showDetails').addClass('show').removeClass('hide').html(data);
		});
	},

	registerEvents: function () {
		this.startMigrationEvent();
	}
});
