/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
            var url = 'index.php?module=' + app.getModuleName() + '&view=Detail&record=' + selectedBookmark;
            window.location.href = url;
        });
    },
    registerEvents: function () {
        this.registerAddBookmark();
        this.registerDetailViewChangeEvent();
    }
});