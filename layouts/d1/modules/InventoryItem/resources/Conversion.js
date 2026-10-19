/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var InventoryItem_Conversion_Js */
Vtiger.Class("InventoryItem_Conversion_Js", {
    /**
     * Opens the item review modal. The edit view of the target record opens after the reviewed items are stored on the server.
     *
     * @param {string} sourceModule
     * @param {string} targetModule
     * @param {number} sourceRecord
     * @param {string} editUrl url of the create view of the target record
     */
    showModal: function (sourceModule, targetModule, sourceRecord, editUrl) {
        const params = {
            module: "InventoryItem",
            view: "ConversionModal",
            sourceModule: sourceModule,
            targetModule: targetModule,
            sourceRecord: sourceRecord
        };

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: error.message});

                return;
            }

            app.helper.showModal(data, {
                cb: function (modal) {
                    vtUtils.applyFieldElementsView(modal);
                    InventoryItem_Conversion_Js.registerModalEvents(modal, editUrl);
                }
            });
        });
    },

    registerModalEvents: function (modal, editUrl) {
        const form = jQuery("#inventoryItemConversionForm", modal);

        form.on("change", ".conversionRemove", function (e) {
            const row = jQuery(e.currentTarget).closest("tr");

            row.toggleClass("text-decoration-line-through opacity-50", e.currentTarget.checked);
            jQuery(".conversionValue", row).prop("disabled", e.currentTarget.checked);
        });

        form.on("submit", function (e) {
            e.preventDefault();

            const data = form.serializeFormData(),
                headerParams = jQuery.param(jQuery(".conversionHeader :input", form).serializeArray());

            data.module = "InventoryItem";
            data.action = "ConversionStore";

            app.helper.showProgress();
            app.request.post({data: data}).then(function (error) {
                if (error) {
                    app.helper.hideProgress();
                    app.helper.showErrorNotification({message: error.message});

                    return;
                }

                // Full page navigation is required: the target record is created in its own edit view.
                window.location.href = editUrl + (editUrl.indexOf("?") === -1 ? "?" : "&") + "conversionModal=1" + (headerParams ? "&" + headerParams : "");
            });
        });
    }
}, {});
