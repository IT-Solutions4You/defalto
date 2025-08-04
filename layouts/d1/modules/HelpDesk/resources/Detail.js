/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Detail_Js("HelpDesk_Detail_Js", {}, {

    /**
     * This function is used to transform href(GET) request of CovertFAQ
     * function to POST request because we are hitting action URL, So it should
     * be post request with valid token
     * */
    regiterEventForConvertFAQ: function () {
        var eleName = '#' + app.getModuleName() + '_detailView_moreAction_LBL_CONVERT_FAQ';
        var ele = jQuery(eleName).find('a');
        ele.on('click', function (e) {
            var url = ele.attr('href');
            e.preventDefault();
            var form = jQuery("<form/>", {method: "post", action: url});
            form.append(jQuery("<input/>", {type: "hidden", name: csrfMagicName, value: csrfMagicToken}));
            form.appendTo('body').submit();
        });
    },

    registerEvents: function () {
        this._super();
        this.regiterEventForConvertFAQ();
    }
});