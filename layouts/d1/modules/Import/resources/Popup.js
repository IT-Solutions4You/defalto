/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Popup_Js("Import_Popup_Js", {}, {
    getCompleteParams: function () {
        var params = {};
        params['view'] = 'List';
        params['module'] = 'Import';
        params['search_key'] = this.getSearchKey();
        params['search_value'] = this.getSearchValue();
        params['orderby'] = this.getOrderBy();
        params['sortorder'] = this.getSortOrder();
        params['page'] = this.getPageNumber();
        params['for_module'] = app.getModuleName();
        params.search_params = JSON.stringify(this.getPopupListSearchParams());
        return params;
    },

    /**
     * Function to get Page Jump Params
     */
    getPageJumpParams: function () {
        var params = this.getCompleteParams();
        params['view'] = "List";
        params['mode'] = "getPageCount";

        return params;
    },

    registerEvents: function () {
        this._super();
    }
});

