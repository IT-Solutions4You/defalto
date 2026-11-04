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
     * Opens the item review modal. The target record is created directly from the modal.
     *
     * @param {string} sourceModule
     * @param {string} targetModule
     * @param {number} sourceRecord
     */
    showModal: function (sourceModule, targetModule, sourceRecord) {
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
                backdrop: "static",
                cb: function (modal) {
                    const form = jQuery("#inventoryItemConversionForm", modal);

                    if (typeof Vtiger_Edit_Js !== "undefined") {
                        new Vtiger_Edit_Js().registerBasicEvents(form);
                    }

                    vtUtils.applyFieldElementsView(form);
                    InventoryItem_Conversion_Js.registerModalEvents(form);
                }
            });
        });
    },

    /**
     * Builds the metadata which the form validator uses to find the mandatory fields.
     *
     * @param {Object} fieldsInfo
     *
     * @return {Object}
     */
    getValidationMeta: function (fieldsInfo) {
        return {
            field: {
                get: function (name, property) {
                    if (name && property === undefined) {
                        return fieldsInfo[name];
                    }

                    if (name && property) {
                        return fieldsInfo[name][property];
                    }
                },
                isMandatory: function (name) {
                    return fieldsInfo[name] ? fieldsInfo[name].mandatory : false;
                },
                getType: function (name) {
                    return fieldsInfo[name] ? fieldsInfo[name].type : false;
                }
            }
        };
    },

    registerModalEvents: function (form) {
        const saveButton = jQuery("button[name='saveButton']", form);

        form.on("change", ".conversionRemove", function (e) {
            const row = jQuery(e.currentTarget).closest("tr");

            row.toggleClass("text-decoration-line-through opacity-50", e.currentTarget.checked);
            jQuery(".conversionValue", row).prop("disabled", e.currentTarget.checked);
        });

        form.vtValidate({
            submitHandler: function (formElement) {
                if (this.numberOfInvalids() > 0) {
                    return false;
                }

                const data = jQuery(formElement).serializeFormData();

                saveButton.attr("disabled", "disabled");
                app.helper.showProgress();
                app.request.post({data: data}).then(function (error, result) {
                    if (error) {
                        app.helper.hideProgress();
                        saveButton.removeAttr("disabled");
                        app.helper.showErrorNotification({message: error.message});

                        return;
                    }

                    // The detail view of the new record is a different page, so a full navigation is required.
                    window.location.href = result.url;
                });
            },
            validationMeta: InventoryItem_Conversion_Js.getValidationMeta(JSON.parse(form.attr("data-fields-info") || "{}"))
        });
    }
}, {});
