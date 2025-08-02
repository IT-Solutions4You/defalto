/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Popup_Js("PriceBooks_Popup_Js", {}, {

    /**
     * Function to pass params for request
     */
    getCompleteParams: function () {
        var params = this._super();
        params['currency_id'] = jQuery('#currencyId').val();
        return params;
    },

    registerEvents: function () {
        this._super();
    }
});

