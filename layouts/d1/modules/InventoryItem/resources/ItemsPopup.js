/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var InventoryItem_Popup_Js */
Vtiger_Popup_Js("InventoryItem_Popup_Js", {}, {

    searchHandler: function () {
        const self = this;
        const aDeferred = jQuery.Deferred();
        let completeParams = self.getCompleteParams();
        completeParams.page = 1;

        this.getPageRecords(completeParams).then(
            function (data) {
                self.registerEventForBackToProductsButtonClick();
                aDeferred.resolve(data);
            });

        return aDeferred.promise();
    },

    registerSubproductsClick: function () {
        const self = this;
        const popupPageContainer = this.getPopupPageContainer();
        this.parentProductEle = popupPageContainer.clone(true, true);
        popupPageContainer.on('click', '.subproducts', function (e) {
            e.stopPropagation();
            let rowElement = jQuery(e.currentTarget).closest('tr');

            let params = {};
            params.view = 'SubProductsPopup';
            params.module = app.getModuleName();
            params.multi_select = true;
            params.subProductsPopup = true;
            params.productid = rowElement.data('id');
            jQuery('#recordsCount').val('');
            jQuery('#pageNumber').val("1");
            jQuery('#pageToJump').val('1');
            jQuery('#orderBy').val('');
            jQuery("#sortOrder").val('');
            app.request.get({'data': params}).then(function (error, data) {
                jQuery('#popupContentsDiv').html(data);
                jQuery('#totalPageCount').text('');
                vtUtils.applyFieldElementsView(jQuery('#popupContentsDiv'));
                self.registerEventForBackToProductsButtonClick();
                self.registerPostPopupLoadEvents();
                jQuery('#pageNumber', popupPageContainer).val(1);
                jQuery('#pageToJump', popupPageContainer).val(1);
                self.updatePagination();
            });
        });
    },

    getCompleteParams: function () {
        let params = this._super();
        params.item_module = jQuery('#item_module').val();
        const subProductsPopup = jQuery('#subProductsPopup').val();
        const parentProductId = jQuery('#parentProductId').val();

        if (typeof subProductsPopup != "undefined" && typeof parentProductId != "undefined") {
            params.subProductsPopup = subProductsPopup;
            params.productid = parentProductId;
            params.view = 'SubProductsPopupAjax';
        }

        const module = jQuery('#EditView').find('[name="module"]').val();

        if (typeof module != "undefined") {
            params.module = module;
        }

        return params;
    },

    /**
     * Function to get Page Jump Params
     */
    getPageJumpParams: function () {
        let params = this.getCompleteParams();
        params.view = 'ItemsPopupAjax';
        params.mode = 'getPageCount';
        params.module = this.getModuleName();

        return params;
    },

    /**
     * Function to register event for back to products button click
     */
    registerEventForBackToProductsButtonClick: function () {
        const self = this;
        jQuery('#backToProducts').on('click', function () {
            self.getPopupPageContainer().html(self.parentProductEle.html());
            self.registerPostPopupLoadEvents();
        });
    },

    registerEvents: function () {
        this._super();
        this.registerSubproductsClick();
    },
});