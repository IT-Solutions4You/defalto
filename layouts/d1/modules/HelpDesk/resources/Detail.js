/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Detail_Js("HelpDesk_Detail_Js", {}, {
    
    /**
     * This function is used to transform href(GET) request of CovertFAQ
     * function to POST request because we are hitting action URL, So it should
     * be post request with valid token
     * */
    regiterEventForConvertFAQ: function () {
        var eleName = '#'+app.getModuleName()+'_detailView_moreAction_LBL_CONVERT_FAQ';
        var ele = jQuery(eleName).find('a');
        ele.on('click',function(e){
            var url = ele.attr('href');
            e.preventDefault();
            var form = jQuery("<form/>",{method:"post",action:url});
            form.append(jQuery("<input/>",{type:"hidden",name:csrfMagicName,value:csrfMagicToken}));
            form.appendTo('body').submit();
        });
    },
    
    registerEvents : function() {
        this._super();
        this.regiterEventForConvertFAQ();
    }
});