/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

PriceBooks_Detail_Js("Products_Detail_Js", {
    triggerEditQuantity: function (url) {
        app.request.get({url: url}).then(
            function (err, data) {
                app.helper.showModal(data, {
                    cb: function () {
                        var productDetailjs = new Products_Detail_Js();
                        productDetailjs.registerEventForUpdateQuantity();
                    }
                });
            });
    },
}, {
    /**
     * function to register event for showing multiple images using bxslider
     */
    registerEventForImageGraphics: function () {
        if (jQuery('#imageContainer').find("img").length > 0) {
            jQuery('#imageContainer').bxSlider({
                slideWidth: 400,
                infiniteLoop: false,
                minSlides: 1,
                maxSlides: 1,
                slideMargin: 10,
                nextText: "",
                prevText: "",
                hideControlOnEnd: true
            });
            jQuery('.bx-next').css({"font-size": "20px", "color": "gray"});
            jQuery('.bx-next').addClass("fa fa-arrow-right");
            jQuery('.bx-prev').css({"font-size": "20px", "color": "gray"});
            jQuery('.bx-prev').addClass("fa fa-arrow-left");
        }
    },

    /**
     * function to register event for editing the list price in related list
     */


    registerEventForUpdateQuantity: function () {
        var self = this;
        var updateQuantityForm = jQuery('#quantityUpdate');
        updateQuantityForm.vtValidate({
            submitHandler: function (form) {
                var selectedRecordDetails = {};
                var quantityEle = updateQuantityForm.find('input[name="quantity"]');
                var quantity = quantityEle.val();
                var relid = updateQuantityForm.find('input[name="relid"]').val();
                selectedRecordDetails[relid] = quantity;
                var relatedListInstance = self.getRelatedController();
                var relation = relatedListInstance.updateRelations(selectedRecordDetails);
                app.helper.hideModal();
                relation.done(self.loadRelatedList());
            }
        });
    },

    registerEventForChangeTotalCost: function () {
        var detailContentsHolder = this.getContentHolder();
        var thisInstance = this;
        detailContentsHolder.on('click', '#updatePrice', function (e) {
            var element = jQuery(e.currentTarget);
            if (element.attr('disabled')) {
                return;
            }
            app.helper.showProgress();
            var unitPrice = jQuery('.subProductsTotalCost').text();
            var params = {
                'module': app.getModuleName(),
                'relatedModule': thisInstance.getRelatedModuleName(),
                'record': thisInstance.getRecordId(),
                'tabLabel': jQuery('#tab_label').val(),
                'action': 'RelationAjax',
                'mode': 'changeBundleCost',
                'unit_price': unitPrice
            }
            app.request.post({data: params}).then(
                function (err, data) {
                    app.helper.hideProgress();

                    if (data) {
                        element.attr('disabled', true);
                        var message = 'JS_SUCCESSFULLY_CHANGED_BUNDLE_COST';
                        app.helper.showSuccessNotification({message: app.vtranslate(message)});
                    }
                });
        });
        thisInstance.registerPopover();
    },
    /**
     * Function to register event for select button click on pricebooks in Products related list
     */
    registerEventForSelectOptionToShowBundleInInventory: function () {
        var thisInstance = this;
        var detailContentsHolder = this.getContentHolder();

        detailContentsHolder.on('click', '.showBundlesInInventory', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var prevSelectedValue = jQuery('.isShowBundles').val();
            var isChecked = currentTarget.find('input[type="checkbox"]').is(':checked');
            var value = 0;
            if (isChecked) {
                value = 1;
            }

            if (value != prevSelectedValue) {
                var params = {
                    'module': app.getModuleName(),
                    'relatedModule': thisInstance.getRelatedModuleName(),
                    'record': thisInstance.getRecordId(),
                    'tabLabel': jQuery('#tab_label').val(),
                    'action': 'RelationAjax',
                    'mode': 'updateShowBundles',
                    'value': value
                }

                app.request.post({data: params}).then(
                    function (err, response) {
                        jQuery('.isShowBundles').val(value);
                        if (value) {
                            currentTarget.attr('checked', 'true');
                        } else {
                            currentTarget.removeAttr('checked');
                        }
                        var message = 'JS_SUB_PRODUCTS_WILL_NOT_BE_SHOWN_IN_INVENTORY';
                        if (value) {
                            message = 'JS_SUB_PRODUCTS_WILL_BE_SHOWN_IN_INVENTORY';
                        }
                        app.helper.showSuccessNotification({message: app.vtranslate(message)});
                    }
                );
            }
        });
    },
    loadRelatedList: function () {
        var relatedController = this.getRelatedController();
        var self = this;
        relatedController.loadRelatedList().then(function () {
            relatedController.triggerRelationAdditionalActions();
        });
    },
    registerPopover: function () {
        let element = jQuery('.totalCostCalculationInfo');

        if (element.length) {
            let popover = new bootstrap.Popover(element, {html: true, container: 'body', placement: 'top', customClass: 'productBundlePopover'});

            element.one('shown.bs.popover', function () {
                app.helper.showVerticalScroll(jQuery('.productBundlePopover .popover-content'));
            });
        }
    },
    registerBasicEvents: function () {
        this._super();
        this.registerEventForImageGraphics();
    },
    /**
     * Function to register events
     */
    registerEvents: function () {
        let self = this;

        app.event.on("post.RecordList.click", function (event, data) {
            let responseData = JSON.parse(data);
            app.helper.hideModal();
            let relatedController = self.getRelatedController();
            relatedController.addRelations(responseData).then(self.loadRelatedList());
        });

        this._super();
        this.registerEventForSelectOptionToShowBundleInInventory();
        this.registerEventForChangeTotalCost();

        app.event.on("post.relatedListLoad.click", function () {
            self.registerPopover();
            self.registerEventForImageGraphics();
        });

        app.event.on("popover.click.event", function (e) {
            self.registerPopover();
        });
    }
});
